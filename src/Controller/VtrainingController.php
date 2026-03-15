<?php

namespace App\Controller;

use App\Entity\Vtraining;
use App\Form\VtrainingFormType;
use App\Repository\VtrainingRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/vtraining')]
class VtrainingController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface       $slugger,
        private ImageUploader          $imageUploader
    ) {}

    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────

    #[Route('/', name: 'app_vtraining_index', methods: ['GET'])]
    public function index(VtrainingRepository $vtrainingRepository): Response
    {
        return $this->render('vtraining/index.html.twig', [
            'vtrainings' => $vtrainingRepository->findAllOrderedByCreatedAt(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // NEW
    // ──────────────────────────────────────────────────────────────

    #[Route('/new', name: 'app_vtraining_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $vtraining = new Vtraining();
        $form = $this->createForm(VtrainingFormType::class, $vtraining);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $this->slugger->slug($vtraining->getTitle())->lower();
            $vtraining->setSlug($slug);

            $this->entityManager->persist($vtraining);
            $this->entityManager->flush();

            $this->addFlash('success', 'Training created successfully!');
            return $this->redirectToRoute(
                'app_vtraining_edit',
                ['id' => $vtraining->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('vtraining/new.html.twig', [
            'vtraining'       => $vtraining,
            'form'            => $form,
            'max_file_size'   => $this->imageUploader->getMaxFileSizeFormatted(),
            'max_file_size_mb'=> $this->imageUploader->getMaxFileSize() / 1024 / 1024,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────

    #[Route('/{id}', name: 'app_vtraining_show', methods: ['GET'])]
    public function show(Vtraining $vtraining): Response
    {
        return $this->render('vtraining/show.html.twig', [
            'vtraining' => $vtraining,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────

    #[Route('/{id}/edit', name: 'app_vtraining_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vtraining $vtraining): Response
    {
        $form = $this->createForm(VtrainingFormType::class, $vtraining);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $this->slugger->slug($vtraining->getTitle())->lower();
            $vtraining->setSlug($slug);

            $this->entityManager->flush();

            $this->addFlash('success', 'Training updated successfully!');
            return $this->redirectToRoute(
                'app_vtraining_show',
                ['id' => $vtraining->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('vtraining/edit.html.twig', [
            'vtraining'       => $vtraining,
            'form'            => $form,
            'max_file_size'   => $this->imageUploader->getMaxFileSizeFormatted(),
            'max_file_size_mb'=> $this->imageUploader->getMaxFileSize() / 1024 / 1024,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // DELETE
    // ──────────────────────────────────────────────────────────────

    #[Route('/{id}', name: 'app_vtraining_delete', methods: ['POST'])]
    public function delete(Request $request, Vtraining $vtraining): Response
    {
        if ($this->isCsrfTokenValid('delete' . $vtraining->getId(), $request->request->get('_token'))) {
            $this->imageUploader->deleteMultiple($vtraining->getImages());
            $this->entityManager->remove($vtraining);
            $this->entityManager->flush();
            $this->addFlash('success', 'Training deleted successfully!');
        }

        return $this->redirectToRoute('app_vtraining_index', [], Response::HTTP_SEE_OTHER);
    }

    // ──────────────────────────────────────────────────────────────
    // UPLOAD IMAGES
    // ──────────────────────────────────────────────────────────────

    #[Route('/{id}/upload-images', name: 'app_vtraining_upload_images', methods: ['POST'])]
    public function uploadImages(Request $request, Vtraining $vtraining): JsonResponse
    {
        if (!$this->isCsrfTokenValid('upload_images', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $files = $request->files->get('file');
        if (!$files) {
            return new JsonResponse(['error' => 'No files uploaded'], 400);
        }

        if (!is_array($files)) {
            $files = [$files];
        }

        try {
            $uploadedFiles = $this->imageUploader->uploadMultiple($files, 'training');
            foreach ($uploadedFiles as $fileName) {
                $vtraining->addImage($fileName);
            }
            $this->entityManager->flush();

            $responseFiles = array_map(fn($fileName) => [
                'name' => $fileName,
                'url'  => $this->imageUploader->getWebPath($fileName),
            ], $uploadedFiles);

            return new JsonResponse([
                'success' => true,
                'files'   => $responseFiles,
                'message' => count($uploadedFiles) . ' file(s) uploaded successfully',
            ]);
        } catch (FileException $e) {
            return new JsonResponse(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // SET COVER IMAGE
    // ──────────────────────────────────────────────────────────────

    #[Route('/{id}/set-cover-image', name: 'app_vtraining_set_cover', methods: ['POST'])]
    public function setCoverImage(Request $request, Vtraining $vtraining): JsonResponse
    {
        if (!$this->isCsrfTokenValid('set_cover', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $imageName = $request->request->get('image');
        if (!$vtraining->hasImage($imageName)) {
            return new JsonResponse(['error' => 'Image not found'], 404);
        }

        $vtraining->setCoverImage($imageName);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Cover image set successfully']);
    }

    // ──────────────────────────────────────────────────────────────
    // DELETE IMAGE
    // ──────────────────────────────────────────────────────────────

    #[Route('/{id}/delete-image', name: 'app_vtraining_delete_image', methods: ['DELETE'])]
    public function deleteImage(Request $request, Vtraining $vtraining): JsonResponse
    {
        if (!$this->isCsrfTokenValid('delete_image', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $imageName = $request->request->get('image');
        if (!$vtraining->hasImage($imageName)) {
            return new JsonResponse(['error' => 'Image not found'], 404);
        }

        $vtraining->removeImage($imageName);
        if ($vtraining->getCoverImage() === $imageName) {
            $vtraining->setCoverImage(null);
        }
        $this->imageUploader->delete($imageName);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Image deleted successfully']);
    }
}
