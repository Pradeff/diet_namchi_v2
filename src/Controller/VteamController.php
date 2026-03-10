<?php

namespace App\Controller;

use App\Entity\Vteam;
use App\Form\VteamType;
use App\Repository\VteamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/vteam')]
class VteamController extends AbstractController
{
    #[Route('/', name: 'app_vteam_index', methods: ['GET'])]
    public function index(VteamRepository $vteamRepository): Response
    {
        return $this->render('vteam/index.html.twig', [
            'vteams' => $vteamRepository->findAllOrdered(),
        ]);
    }

    #[Route('/new', name: 'app_vteam_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, VteamRepository $vteamRepository): Response
    {
        $vteam = new Vteam();
        $form = $this->createForm(VteamType::class, $vteam);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $croppedData = $request->request->get('croppedImageData');

            if ($croppedData) {
                $fileName = $this->saveCroppedImage($croppedData);
                $vteam->setImage($fileName);
            } else {
                $file = $form->get('image')->getData();
                if ($file) {
                    $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();
                    $file->move($this->getParameter('image_directory'), $fileName);
                    $vteam->setImage($fileName);
                }
            }

            // Set position to max + 1
            $maxPosition = $vteamRepository->getMaxPosition();
            $vteam->setPosition($maxPosition + 1);

            $entityManager->persist($vteam);
            $entityManager->flush();

            $this->addFlash('success', 'Item Added successfully.');

            return $this->redirectToRoute('app_vteam_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vteam/new.html.twig', [
            'vteam' => $vteam,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vteam_show', methods: ['GET'])]
    public function show(Vteam $vteam): Response
    {
        return $this->render('vteam/show.html.twig', [
            'vteam' => $vteam,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vteam_edit', methods: ['GET', 'POST'])]
    public function edit($id, Request $request, Vteam $vteam, EntityManagerInterface $entityManager): Response
    {
        $entity = $entityManager->getRepository(Vteam::class)->find($id);
        $filename = $vteam->getImage();
        $form = $this->createForm(VteamType::class, $vteam);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $croppedData = $request->request->get('croppedImageData');

            if ($croppedData) {
                if ($filename) {
                    $filepath = $this->getParameter('image_directory') . '/' . $filename;
                    if (file_exists($filepath)) {
                        unlink($filepath);
                    }
                }
                $fileName = $this->saveCroppedImage($croppedData);
                $vteam->setImage($fileName);
            } else {
                $file = $form->get('image')->getData();
                if ($file) {
                    if ($filename) {
                        $filepath = $this->getParameter('image_directory') . '/' . $filename;
                        if (file_exists($filepath)) {
                            unlink($filepath);
                        }
                    }
                    $fileName1 = $this->generateUniqueFileName() . '.' . $file->guessExtension();
                    $file->move($this->getParameter('image_directory'), $fileName1);
                    $vteam->setImage($fileName1);
                } else {
                    $vteam->setImage($filename);
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Item updated successfully.');

            return $this->redirectToRoute('app_vteam_index', [], Response::HTTP_SEE_OTHER);
        }

        $imageUrl = $entity->getImage();
        return $this->render('vteam/edit.html.twig', [
            'vteam' => $vteam,
            'form' => $form,
            'image_url' => $imageUrl,
        ]);
    }

    #[Route('/{id}', name: 'app_vteam_delete', methods: ['POST'])]
    public function delete(Request $request, Vteam $vteam,
                           VteamRepository $vteamRepository, EntityManagerInterface $entityManager): Response
    {
        $image = $vteam->getImage();
        if ($this->isCsrfTokenValid('delete'.$vteam->getId(), $request->request->get('_token'))) {
            if ($image) {
                $filepath = $this->getParameter('image_directory') . '/' . $image;
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
            $entityManager->remove($vteam);
            $entityManager->flush();
            $vteamRepository->reorderPositions();
        }

        return $this->redirectToRoute('app_vteam_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/vteam/sort', name: 'app_vteam_sort', methods: ['POST'])]
    public function sort(Request $request, VteamRepository $vteamRepository, EntityManagerInterface $em): JsonResponse
    {
        $order = $request->request->all('order');

        foreach ($order as $item) {
            $vteam = $vteamRepository->find($item['id']);
            if ($vteam) {
                $vteam->setPosition((int)$item['position']);
            }
        }

        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    private function generateUniqueFileName(): string
    {
        return md5(uniqid());
    }

    private function saveCroppedImage(string $croppedData): string
    {
        $data = explode(',', $croppedData);
        $decoded = base64_decode($data[1]);
        $fileName = $this->generateUniqueFileName() . '.png';
        $path = $this->getParameter('image_directory') . '/' . $fileName;
        file_put_contents($path, $decoded);

        return $fileName;
    }
}
