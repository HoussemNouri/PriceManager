<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use App\Manager\ArticleManager;

final class ArticleController extends CustomAbstractController
{
    private ArticleManager $articleManager;

    public function __construct(ArticleManager $articleManager)
    {
        $this->articleManager = $articleManager;
    }

    #[Route('/article', name: 'add_article', methods: ['POST'])]
    public function addArticle(Request $request): JsonResponse
    {
        // On récupère les données JSON du corps de la requête
        $data = json_decode($request->getContent(), true);

        // Simple validation : s'assurer que les clés minimales sont présentes
        if (
            !isset($data['name']) || !isset($data['origin']) || !isset($data['destination']) ||
            !isset($data['delay']) || !isset($data['price']) || !is_numeric($data['price'])
        ) {
            return $this->error('Données manquantes ou format incorrect pour l\'article.', [], JsonResponse::HTTP_BAD_REQUEST); // Code HTTP 400
        }

        try {
            $this->articleManager->addArticle(
                $data['name'],
                $data['origin'],
                $data['destination'],
                $data['delay'],
                (float) $data['price']
            );

            return $this->success('Article ajouté avec succès.',[] , JsonResponse::HTTP_CREATED); // Code HTTP 201

        } catch (ConflictHttpException $e) {
            // Capture l'exception levée par ArticleManager si l'article existe déjà (409)
            return $this->error($e->getMessage(), [], JsonResponse::HTTP_CONFLICT); // Code HTTP 409

        } catch (\Exception $e) {dd($e);
            // Capture toute autre erreur inattendue
            return $this->error('Erreur interne lors de l\'ajout de l\'article.', [], JsonResponse::HTTP_INTERNAL_SERVER_ERROR); // Code HTTP 500
        }
    }

    #[Route('/article/names', name: 'get_article_names', methods: ['GET'])]
    public function getArticleNames(): JsonResponse
    {
        try {
            // Appeler le Manager pour obtenir la liste des noms uniques
            $names = $this->articleManager->getArticleNames();
            
            // Retourner la liste des noms dans une réponse de succès (Code HTTP 200)
            return $this->success('Noms d\'articles récupérés avec succès.', $names, JsonResponse::HTTP_OK);
            
        } catch (\Exception $e) {
            // Gérer les erreurs (ex: problème de base de données)
            return $this->error('Erreur lors de la récupération des noms d\'articles.', [], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    
    #[Route('/article/price', name: 'get_article_price', methods: ['GET'])]
    public function getArticlePrice(Request $request): JsonResponse
    {

        $name = $request->query->get('name');
        $orig = $request->query->get('origin');
        $dest = $request->query->get('destination');
        $delay = $request->query->get('delay');
        $dateStr = $request->query->get('date');

        if (empty($name) || empty($orig) || empty($dest) || empty($delay) || empty($dateStr)) {
            return $this->error('Données manquantes ou format incorrect pour l\'article.', [], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            
            $date = new \DateTime($dateStr . ' 00:00:00');
        } catch (\Exception $e) {
            return $this->error('Données manquantes ou format incorrect pour l\'article.', [], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        
        try {
            $finalPrice = $this->articleManager->getPrice($name, $orig, $dest, $delay, $date);
        } catch (NotFoundHttpException $e) {
            
            return $this->error($e->getMessage(), [], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->success(
            'Article ajouté avec succès.',
            [
                'name' => $name,
                'final_price' => $finalPrice,
                'calculated_on_date' => $date->format('Y-m-d'),
            ] , 
            JsonResponse::HTTP_OK
        );
    }
}
