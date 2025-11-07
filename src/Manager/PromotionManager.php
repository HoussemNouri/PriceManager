<?php

namespace App\Manager;

use App\Entity\Promotion;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException; // Utiliser une exception HTTP

class PromotionManager
{
    private const INCEARSE_CHRISMES = 1.2;

    private const DISCOUNT_CLEARANCE = 0.5;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Ajoute un nouvel promotion a un article deja existe. Lève une exception si la promotion existe déjà.
     */
    public function addPromotion(Article $article, float $discount, \DateTime $startDate, int $durationDays): void
    {
        $repository = $this->entityManager->getRepository(Promotion::class);

        // 1. Vérifier si l'article existe déjà (selon les 4 critères)
        $existingPromotion = $repository->findOneBy([
            'article' => $article,
            'discount' => $discount,
            'startDate' => $startDate,
            'durationDays' => $durationDays,
        ]);
        
        if ($existingPromotion) {
            // Conformément au requis, on lève une exception
            throw new ConflictHttpException("La promotion (article, discount, startDate, durationDays) existe déjà. Impossible d'ajouter.");
        }

        // 2. Créer et persister le nouvel promotion
        $promotion = new Promotion();
        $promotion->setArticle($article)
            ->setDiscount($discount)
            ->setStartDate($startDate)
            ->setDurationDays($durationDays);

        $this->entityManager->persist($promotion);
        $this->entityManager->flush();
    }

    /**
     * Ajoute un nouvel promotion broadcast. Lève une exception si la promotion existe déjà.
     */
    public function setChristmasPeriod( \DateTime $startDate, \DateTime $endDate): void
    {
        $repository = $this->entityManager->getRepository(Promotion::class);

        $interval = $startDate->diff($endDate);

        // 1. Vérifier si l'article existe déjà (selon les 4 critères)
        $existingPromotion = $repository->findOneBy([
            'article' => null,
            'discount' => self::INCEARSE_CHRISMES,
            'startDate' => $startDate,
            'durationDays' => $interval->d,
        ]);

        if ($existingPromotion instanceof Promotion) {
            // Conformément au requis, on lève une exception
            throw new ConflictHttpException("La promotion (discount, startDate, endDate) existe déjà. Impossible d'ajouter.");
        }

        // 2. Créer et persister le nouvel promotion
        $promotion = new Promotion();
        $promotion->setArticle(null)
            ->setDiscount(self::INCEARSE_CHRISMES)
            ->setStartDate($startDate)
            ->setDurationDays($interval->d)
            ->setIsBroadcastPromotion(true);

        $this->entityManager->persist($promotion);
        $this->entityManager->flush();
    }

    /**
     * Ajoute un nouvel promotion broadcast. Lève une exception si la promotion existe déjà.
     */
    public function setClearancePeriod( \DateTime $startDate, int $durationDays): void
    {
        $repository = $this->entityManager->getRepository(Promotion::class);

        // 1. Vérifier si l'article existe déjà (selon les 4 critères)
        $existingPromotion = $repository->findOneBy([
            'article' => null,
            'discount' => self::DISCOUNT_CLEARANCE,
            'startDate' => $startDate,
            'durationDays' => $durationDays,
        ]);

        if ($existingPromotion instanceof Promotion) {
            // Conformément au requis, on lève une exception
            throw new ConflictHttpException("La promotion (discount, startDate, durationDays) existe déjà. Impossible d'ajouter.");
        }

        // 2. Créer et persister le nouvel promotion
        $promotion = new Promotion();
        $promotion->setArticle(null)
            ->setDiscount(self::DISCOUNT_CLEARANCE)
            ->setStartDate($startDate)
            ->setDurationDays($durationDays)
            ->setIsBroadcastPromotion(true);

        $this->entityManager->persist($promotion);
        $this->entityManager->flush();
    }
}