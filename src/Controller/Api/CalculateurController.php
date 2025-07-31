<?php

namespace App\Controller\Api;

use App\Entity\RendementCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/api/calculateur', name: 'api_calculateur_')]
final class CalculateurController extends AbstractController
{

    #[Route('/simulate', methods: ['POST'], name: 'simulate')]
    public function simulate(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
    ): JsonResponse
    {
        // on recupere les données
        $data = json_decode($request->getContent(), true);

        // vérifier les données
        if (!$data) {
            return $this->json([
                'status' => 'error',
                'message' => 'Données JSON invalides'
            ], 400);
        }

        // sinon :

        $calculateur = new RendementCalculator();
        $calculateur->setMontantInvesti((float)($data['montantInvesti'] ?? 0));

        $tauxPourcentage = (float)($data['tauxRendement'] ?? 0);
        $calculateur->setTauxRendement($tauxPourcentage / 100);

        $calculateur->setDureeAnnees((int)($data['dureeAnnees'] ?? 0));

        $errors = $validator->validate($calculateur);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessage[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json([
                'status' => 'error',
                'errors' => $errorMessage
            ], 422);
        }

        try {
            $em->persist($calculateur);
            $em->flush();
        } catch(\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Erreur lors de la sauvegarde'
            ], 500);
        }
        $resultats = [
            'id' => $calculateur->getId(),
            'parametres' => [
                'montantInvesti' => $calculateur->getMontantInvesti(),
                'tauxRendement' => $tauxPourcentage, // Retour en %
                'dureeAnnees' => $calculateur->getDureeAnnees(),
                'dateCalcul' => $calculateur->getDateCalcul()->format('Y-m-d H:i:s')
            ],
            'resultats' => [
                'rendementAnnuel' => round($calculateur->calculerRendementAnnuel(), 2),
                'rendementTotal' => round($calculateur->calculerRendementTotal(), 2),
                'revenusMensuels' => round($calculateur->calculerRevenusMensuels(), 2),
                'projectionAnnuelle' => $calculateur->getProjectionAnnuelle(),
                'gainTotal' => round($calculateur->calculerRendementTotal() - $calculateur->getMontantInvesti(), 2)
            ],
            'resume' => $calculateur->getResume()
        ];

        return $this->json([
            'status' => 'success',
            'message' => 'Simulation réalisée avec succès',
            'data' => $resultats,
            '_links' => [
                'self' => $this->generateUrl('api_calculateur_simulate'),
                'historique' => $this->generateUrl('api_calculateur_historique')
            ]
        ], 201);


    }

    #[Route('/historique', methods: ['GET'], name: 'historique')]
    public function historique(
        EntityManagerInterface $em,
        Request $request
    ): JsonResponse {
        // Paramètres de pagination optionnels
        $limit = min((int)$request->query->get('limit', 10), 50); // Max 50
        $offset = max((int)$request->query->get('offset', 0), 0);

        // Récupération des calculs triés par date (plus récent en premier)
        $repository = $em->getRepository(RendementCalculator::class);

        $calculs = $repository->findBy(
            [], // Critères (aucun = tous)
            ['dateCalcul' => 'DESC'], // Tri
            $limit, // Limite
            $offset  // Décalage
        );

        // Comptage total pour pagination
        $total = $repository->count([]);

        // Transformation des données
        $data = array_map(function(RendementCalculator $calc) {
            return [
                'id' => $calc->getId(),
                'montantInvesti' => $calc->getMontantInvesti(),
                'tauxRendement' => $calc->getTauxRendement() * 100, // Back to %
                'dureeAnnees' => $calc->getDureeAnnees(),
                'rendementAnnuel' => round($calc->calculerRendementAnnuel(), 2),
                'rendementTotal' => round($calc->calculerRendementTotal(), 2),
                'dateCalcul' => $calc->getDateCalcul()->format('Y-m-d H:i:s'),
                'resume' => $calc->getResume()
            ];
        }, $calculs);

        return $this->json([
            'status' => 'success',
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'count' => count($data),
                'limit' => $limit,
                'offset' => $offset,
                'hasMore' => ($offset + $limit) < $total
            ],
            '_links' => [
                'self' => $this->generateUrl('api_calculateur_historique'),
                'simulate' => $this->generateUrl('api_calculateur_simulate')
            ]
        ]);
    }

    /**
     * Récupère un calcul spécifique par ID
     */
    #[Route('/{id}', methods: ['GET'], name: 'show')]
    public function show(
        int $id,
        EntityManagerInterface $em
    ): JsonResponse {
        $calculateur = $em->getRepository(RendementCalculator::class)->find($id);

        if (!$calculateur) {
            return $this->json([
                'status' => 'error',
                'message' => 'Calcul non trouvé'
            ], 404);
        }

        return $this->json([
            'status' => 'success',
            'data' => [
                'id' => $calculateur->getId(),
                'parametres' => [
                    'montantInvesti' => $calculateur->getMontantInvesti(),
                    'tauxRendement' => $calculateur->getTauxRendement() * 100,
                    'dureeAnnees' => $calculateur->getDureeAnnees(),
                    'dateCalcul' => $calculateur->getDateCalcul()->format('Y-m-d H:i:s')
                ],
                'resultats' => [
                    'rendementAnnuel' => round($calculateur->calculerRendementAnnuel(), 2),
                    'rendementTotal' => round($calculateur->calculerRendementTotal(), 2),
                    'revenusMensuels' => round($calculateur->calculerRevenusMensuels(), 2),
                    'projectionAnnuelle' => $calculateur->getProjectionAnnuelle()
                ]
            ],
            '_links' => [
                'self' => $this->generateUrl('api_calculateur_show', ['id' => $id]),
                'historique' => $this->generateUrl('api_calculateur_historique')
            ]
        ]);
    }



}
