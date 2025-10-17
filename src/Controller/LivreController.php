<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/livres')]
class LivreController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(LivreRepository $repo): JsonResponse
    {
        $livres = $repo->findAll();
        $data = array_map(fn($l) => [
            'id' => $l->getId(),
            'titre' => $l->getTitre(),
            'datePublication' => $l->getDatePublication()?->format('Y-m-d'),
            'disponible' => $l->isDisponible()
        ], $livres);

        return new JsonResponse($data, 200);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Livre $livre): JsonResponse
    {
        return new JsonResponse([
            'id' => $livre->getId(),
            'titre' => $livre->getTitre(),
            'datePublication' => $livre->getDatePublication()?->format('Y-m-d'),
            'disponible' => $livre->isDisponible()
        ], 200);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['titre'], $data['datePublication'], $data['disponible'])) {
            return new JsonResponse(['error' => 'Données manquantes'], 400);
        }

        $livre = new Livre();
        $livre->setTitre($data['titre']);
        $livre->setDatePublication(new \DateTime($data['datePublication']));
        $livre->setDisponible($data['disponible']);

        $em->persist($livre);
        $em->flush();

        return new JsonResponse(['message' => 'Livre créé avec succès'], 201);
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Livre $livre, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) return new JsonResponse(['error' => 'Données manquantes'], 400);

        if (isset($data['titre'])) $livre->setTitre($data['titre']);
        if (isset($data['datePublication'])) $livre->setDatePublication(new \DateTime($data['datePublication']));
        if (isset($data['disponible'])) $livre->setDisponible($data['disponible']);

        $em->flush();
        return new JsonResponse(['message' => 'Livre mis à jour'], 200);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Livre $livre, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($livre);
        $em->flush();
        return new JsonResponse(['message' => 'Livre supprimé'], 200);
    }
}
