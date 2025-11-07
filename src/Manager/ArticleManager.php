<?php

namespace App\Manager;

use App\Entity\Article;
use App\Entity\Promotion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException; // Utiliser une exception HTTP

class ArticleManager
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Ajoute un nouvel article. Lève une exception si l'article existe déjà.
     */
    public function addArticle(string $name, string $orig, string $dest, string $delay, float $price): void
    {
        $repository = $this->entityManager->getRepository(Article::class);

        // 1. Vérifier si l'article existe déjà (selon les 4 critères)
        $existingArticle = $repository->findOneBy([
            'name' => $name,
            'origin' => $orig,
            'destination' => $dest,
            'delay' => $delay,
        ]);

        if ($existingArticle) {
            // Conformément au requis, on lève une exception
            throw new ConflictHttpException("L'article (Nom, Origine, Destination, Délai) existe déjà. Impossible d'ajouter.");
        }

        // 2. Créer et persister le nouvel article
        $article = new Article();
        $article->setName($name)
            ->setOrigin($orig)
            ->setDestination($dest)
            ->setDelay($delay)
            ->setPrice($price);

        $this->entityManager->persist($article);
        $this->entityManager->flush();
    }

    /**
     * Retourne la liste des noms d'articles uniques
     * @return string[]
     */
    public function getArticleNames(): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        
        $names = $queryBuilder
            ->select('a.name') 
            ->from(Article::class, 'a')
            ->getQuery()
            ->getSingleColumnResult(); // Retourne un simple tableau de chaînes
            
        return $names;
    }

    /**
     * Calcule le prix final de l'article en appliquant les règles de promotion.
     */
    public function getPrice(string $name, string $orig, string $dest, string $delay, \DateTime $date): float
    {
        $articleRepository = $this->entityManager->getRepository(Article::class);
        $promotionRepository = $this->entityManager->getRepository(Promotion::class);

        // 1. Trouver le prix de base de l'article
        $article = $articleRepository->findOneBy([
            'name' => $name, 'origin' => $orig, 'destination' => $dest, 'delay' => $delay,
        ]);

        if (!$article) {
            // Si l'article de base n'existe pas, on lève une exception (HTTP 404)
            throw new NotFoundHttpException('L\'article de base n\'existe pas pour ces critères.');
        }

        $finalPrice = $article->getPrice();
        $bestDiscount = 0.0; 
        $hasGlobalIncrease = false;

       // Créer la requête pour trouver toutes les promotions actives pour cet article à cette date
        $activePromotions = $promotionRepository->createQueryBuilder('p')
            ->where('p.article = :article')
            ->andWhere('p.startDate <= :currentDate AND DATE_ADD(p.startDate, p.durationDays, \'DAY\') > :currentDate') 
            ->setParameter('article', $article)
            ->setParameter('currentDate', $date)
            ->getQuery()
            ->getResult();
            
        // Parcourir les promotions valides et sélectionner le meilleur taux de réduction
        foreach ($activePromotions as $promotion) {
            $bestDiscount = max($bestDiscount, $promotion->getDiscount());
        }
        
        if ($bestDiscount > 0) {
            $finalPrice *= (1 - $bestDiscount);
        }

        // Créer la requête pour trouver toutes les promotions Broadcast actives à cette date
        $broadcastPromotions = $promotionRepository->createQueryBuilder('p')
            ->where('p.startDate <= :currentDate AND DATE_ADD(p.startDate, p.durationDays, \'DAY\') > :currentDate') 
            ->andWhere('p.isBroadcastPromotion = true') 
            ->setParameter('currentDate', $date)
            ->getQuery()
            ->getResult();

        // Parcourir les promotions valides 
        foreach ($broadcastPromotions as $broadcastPromotion) {
            // augmentation de prix
            if ($broadcastPromotion->getDiscount() > 1 ) {
                $finalPrice *= $broadcastPromotion->getDiscount();
            } else {
                $finalPrice *= (1 - $broadcastPromotion->getDiscount());
            }
        }

        return round($finalPrice, 2);
    }
}