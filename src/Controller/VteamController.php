<?php

namespace App\Controller;

use App\Entity\Vteam;
use App\Form\VteamType;
use App\Repository\VteamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
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
                $fileName = $this->saveCroppedImageAsWebp($croppedData);
                $vteam->setImage($fileName);
            } else {
                $file = $form->get('image')->getData();
                if ($file) {
                    $fileName = $this->convertAndSaveAsWebp($file->getPathname());
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
                $this->deleteExistingImage($filename);
                $fileName = $this->saveCroppedImageAsWebp($croppedData);
                $vteam->setImage($fileName);
            } else {
                $file = $form->get('image')->getData();
                if ($file) {
                    $this->deleteExistingImage($filename);
                    $fileName = $this->convertAndSaveAsWebp($file->getPathname());
                    $vteam->setImage($fileName);
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
        if ($this->isCsrfTokenValid('delete'.$vteam->getId(), $request->request->get('_token'))) {
            $this->deleteExistingImage($vteam->getImage());
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

    // ── Private helpers ──────────────────────────────────────────────────────

    private function generateUniqueFileName(): string
    {
        return md5(uniqid());
    }

    /**
     * Convert any uploaded file to WebP and save it.
     * Returns the new filename (*.webp).
     */
    private function convertAndSaveAsWebp(string $sourcePath): string
    {
        $manager  = new ImageManager(new Driver());
        $image    = $manager->read($sourcePath);
        $fileName = $this->generateUniqueFileName() . '.webp';
        $destPath = $this->getParameter('image_directory') . '/' . $fileName;
        $image->toWebp(85)->save($destPath);

        return $fileName;
    }

    /**
     * Decode a base64 cropped-image data-URI, convert to WebP and save.
     * Returns the new filename (*.webp).
     */
    private function saveCroppedImageAsWebp(string $croppedData): string
    {
        $data    = explode(',', $croppedData);
        $decoded = base64_decode($data[1]);

        // Write raw bytes to a temp file so Intervention can read them
        $tmp = tempnam(sys_get_temp_dir(), 'crop_') . '.png';
        file_put_contents($tmp, $decoded);

        $fileName = $this->convertAndSaveAsWebp($tmp);

        @unlink($tmp);

        return $fileName;
    }

    /**
     * Delete an existing image file from the uploads directory if it exists.
     */
    private function deleteExistingImage(?string $filename): void
    {
        if ($filename) {
            $filepath = $this->getParameter('image_directory') . '/' . $filename;
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
    }
}
