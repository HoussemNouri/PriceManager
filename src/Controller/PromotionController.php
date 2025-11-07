<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use App\Entity\Article;
use App\Manager\PromotionManager;

final class PromotionController extends CustomAbstractController
{
    private PromotionManager $promotionManager;

    public function __construct(PromotionManager $promotionManager)
    {
        $this->promotionManager = $promotionManager;
    }

    #[Route('/promotion/{id}', name: 'add_promotion', methods: ['POST'])]
    public function AddPromotion(Request $request, Article $article): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Validation : s'assurer que les données de promotion sont présentes
        if (
            !isset($data['discount']) || !isset($data['startDate']) ||
            !isset($data['durationDays']) || !is_numeric($data['discount']) || !is_int($data['durationDays'])
        ) {
            return $this->error('Données manquantes ou format incorrect pour la promotion.', [], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        try {
            // Convertir la chaîne de date en DateTime
            $startDate = new \DateTime($data['startDate']);

            $this->promotionManager->addPromotion(
                $article,
                (float) $data['discount'],
                $startDate,
                (int) $data['durationDays']
            );
            
            return $this->success('Promotion ajoutée avec succès à l\'article ' . $article->getId(), [], JsonResponse::HTTP_CREATED);

        } catch (\Exception $e) {

            if ($e instanceof ConflictHttpException) {
                return $this->error( $e->getMessage(), [], JsonResponse::HTTP_CONFLICT);
            }
            return $this->error( 'Erreur interne lors de l\'ajout de la promotion.', [], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('christmas/period', name: 'add_christmas_period', methods: ['POST'])]
    public function setChristmasPeriod(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Validation : s'assurer que les données de promotion sont présentes
        if (
            !isset($data['endDate']) || !isset($data['startDate'])
        ) {
            return $this->error('Données manquantes ou format incorrect pour la promotion.', [], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        try {
            // Convertir la chaîne de date en DateTime
            $startDate = new \DateTime($data['startDate']);
            $endDate = new \DateTime($data['endDate']);

            $this->promotionManager->setChristmasPeriod(
                $startDate,
                $endDate,
            );
            
            return $this->success('Promotion ajoutée avec succès', [], JsonResponse::HTTP_CREATED);

        } catch (\Exception $e) {dd($e);

            if ($e instanceof ConflictHttpException) {
                return $this->error( $e->getMessage(), [], JsonResponse::HTTP_CONFLICT);
            }
            return $this->error( 'Erreur interne lors de l\'ajout de la promotion.', [], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('clearance/period', name: 'add_clearance_period', methods: ['POST'])]
    public function setClearancePeriod(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Validation : s'assurer que les données de promotion sont présentes
        if (
            !isset($data['durationDays']) || !isset($data['startDate'])
        ) {
            return $this->error('Données manquantes ou format incorrect pour la promotion.', [], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        try {
            // Convertir la chaîne de date en DateTime
            $startDate = new \DateTime($data['startDate']);

            $this->promotionManager->setClearancePeriod(
                $startDate,
                $data['durationDays'],
            );
            
            return $this->success('Promotion ajoutée avec succès', [], JsonResponse::HTTP_CREATED);

        } catch (\Exception $e) {dd($e);

            if ($e instanceof ConflictHttpException) {
                return $this->error( $e->getMessage(), [], JsonResponse::HTTP_CONFLICT);
            }
            return $this->error( 'Erreur interne lors de l\'ajout de la promotion.', [], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
