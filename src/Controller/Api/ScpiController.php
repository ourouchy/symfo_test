<?php

namespace App\Controller\Api;

use App\Entity\SCPI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;



#[Route('/api')]
class ScpiController extends AbstractController {

    

  #[Route('/scpi', methods: ['GET'], name: 'api_scpi_list')]
  public function list(EntityManagerInterface $em): JsonResponse
  {
    $scpis = $em->getRepository(SCPI::class)->findAll();

    $data = array_map(function($scpi) {
      return [
        'id' => $scpi->getId(),
        'nom' => $scpi->getNom(),
        'rendement' => $scpi->getRendement()
      ];
    }, $scpis);

    return $this->json([
      'status' => 'succes',
      'count' => count($scpis),
      'data' => $data,
      '_links' => [
        'self' => $this->generateUrl('api_scpi_list'),
        'create' => $this->generateUrl('api_scpi_create')
        ]
      ]);
  }

  #[Route('/scpi', methods: ['POST'], name: 'api_scpi_create')]
  public function create(
    Request $request,
    EntityManagerInterface $em,
    ValidatorInterface $validator
  ): JsonResponse
  {
    $data = json_decode($request->getContent(), true);

    $scpi = new SCPI();
    $scpi->setNom($data['nom'] ?? '');
    $scpi->setRendement((float)($data['rendement'] ?? 0));

    $errors = $validator->validate($scpi);
    if (count($errors) > 0) {
      $errorMessages= [];
      foreach ($errors as $error) {
        $errorMessage[$error->getPropertyPath()] = $error->getMessage();
      }
      return $this->json(['errors' => $errorMessages], 422);
    }

    $em->persist($scpi);
    $em->flush();

    return $this->json([
      'status' => 'created',
      'id' => $scpi->getId(),
      '_links' => [
        'self' => $this->generateUrl('api_scpi_create'),
        'list' => $this->generateUrl('api_scpi_list')
      ]
      ], 201);




  }










}
