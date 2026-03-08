<?php
/*src/controller/VgalleryController.php*/
namespace App\Controller;

use App\Entity\Vgallery;
use App\Form\VgalleryFormType;
use App\Repository\VgalleryRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/vgallery')]
class VgalleryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
        private ImageUploader $imageUploader
    ) {}

    #[Route('/', name: 'app_vgallery_index', methods: ['GET'])]
    public function index(VgalleryRepository $vgalleryRepository): Response
    {
        return $this->render('vgallery/index.html.twig', [
            'vgalleries' => $vgalleryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_vgallery_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $vgallery = new Vgallery();
        $form = $this->createForm(VgalleryFormType::class, $vgallery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $this->slugger->slug($vgallery->getTitle())->lower();
            $vgallery->setSlug($slug);

            $this->entityManager->persist($vgallery);
            $this->entityManager->flush();

            $this->addFlash('success', 'Gallery created successfully!');
            return $this->redirectToRoute('app_vgallery_edit', ['id' => $vgallery->getId()]);
        }

        return $this->render('vgallery/new.html.twig', [
            'vgallery' => $vgallery,
            'form' => $form,
            'max_file_size' => $this->imageUploader->getMaxFileSizeFormatted(),
            'max_file_size_mb' => $this->imageUploader->getMaxFileSize() / 1024 / 1024,
        ]);
    }

    #[Route('/{id}', name: 'app_vgallery_show', methods: ['GET'])]
    public function show(Vgallery $vgallery): Response
    {
        return $this->render('vgallery/show.html.twig', [
            'vgallery' => $vgallery,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vgallery_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vgallery $vgallery): Response
    {
        $form = $this->createForm(VgalleryFormType::class, $vgallery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $this->slugger->slug($vgallery->getTitle())->lower();
            $vgallery->setSlug($slug);

            $this->entityManager->flush();

            $this->addFlash('success', 'Gallery updated successfully!');
            return $this->redirectToRoute('app_vgallery_show', ['id' => $vgallery->getId()]);
        }

        return $this->render('vgallery/edit.html.twig', [
            'vgallery' => $vgallery,
            'form' => $form,
            'max_file_size' => $this->imageUploader->getMaxFileSizeFormatted(),
            'max_file_size_mb' => $this->imageUploader->getMaxFileSize() / 1024 / 1024,
        ]);
    }

    #[Route('/{id}', name: 'app_vgallery_delete', methods: ['POST'])]
    public function delete(Request $request, Vgallery $vgallery): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vgallery->getId(), $request->request->get('_token'))) {
            $this->imageUploader->deleteMultiple($vgallery->getImageFilenames());
            $this->entityManager->remove($vgallery);
            $this->entityManager->flush();
            $this->addFlash('success', 'Gallery deleted successfully!');
        }

        return $this->redirectToRoute('app_vgallery_index');
    }

    #[Route('/{id}/upload-images', name: 'app_vgallery_upload_images', methods: ['POST'])]
    public function uploadImages(Request $request, Vgallery $vgallery): JsonResponse
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

        $maxSize = $this->imageUploader->getMaxFileSize();
        $errors = [];

        foreach ($files as $file) {
            if ($file->getSize() > $maxSize) {
                $errors[] = $file->getClientOriginalName() . ' (' . $this->formatFileSize($file->getSize()) . ') exceeds maximum size of ' . $this->imageUploader->getMaxFileSizeFormatted();
            }
        }

        if (!empty($errors)) {
            return new JsonResponse([
                'error' => 'File size error',
                'details' => $errors
            ], 400);
        }

        try {
            $uploadedFiles = $this->imageUploader->uploadMultiple($files, 'gallery');

            foreach ($uploadedFiles as $fileName) {
                $vgallery->addImage($fileName, '');
            }

            $this->entityManager->flush();

            $responseFiles = array_map(function($fileName) {
                return [
                    'name' => $fileName,
                    'url' => $this->imageUploader->getWebPath($fileName)
                ];
            }, $uploadedFiles);

            return new JsonResponse([
                'success' => true,
                'files' => $responseFiles,
                'message' => count($uploadedFiles) . ' file(s) uploaded successfully'
            ]);

        } catch (FileException $e) {
            return new JsonResponse(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/set-cover-image', name: 'app_vgallery_set_cover', methods: ['POST'])]
    public function setCoverImage(Request $request, Vgallery $vgallery): JsonResponse
    {
        if (!$this->isCsrfTokenValid('set_cover', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $imageName = $request->request->get('image');
        if (!$vgallery->hasImage($imageName)) {
            return new JsonResponse(['error' => 'Image not found'], 404);
        }

        $vgallery->setCoverImage($imageName);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Cover image set successfully'
        ]);
    }

    // FIXED: Changed from DELETE to POST method
    #[Route('/{id}/delete-image', name: 'app_vgallery_delete_image', methods: ['POST'])]
    public function deleteImage(Request $request, Vgallery $vgallery): JsonResponse
    {
        if (!$this->isCsrfTokenValid('delete_image', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $imageName = $request->request->get('image');
        if (!$vgallery->hasImage($imageName)) {
            return new JsonResponse(['error' => 'Image not found'], 404);
        }

        $vgallery->removeImage($imageName);

        if ($vgallery->getCoverImage() === $imageName) {
            $vgallery->setCoverImage(null);
        }

        $this->imageUploader->delete($imageName);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Image deleted successfully'
        ]);
    }

    #[Route('/{id}/update-image-title', name: 'app_vgallery_update_image_title', methods: ['POST'])]
    public function updateImageTitle(Request $request, Vgallery $vgallery): JsonResponse
    {
        if (!$this->isCsrfTokenValid('update_title', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $imageName = $request->request->get('image');
        $title = $request->request->get('title', '');

        if (!$vgallery->hasImage($imageName)) {
            return new JsonResponse(['error' => 'Image not found'], 404);
        }

        $vgallery->updateImageTitle($imageName, $title);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Image title updated successfully'
        ]);
    }

    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / (1024 ** $pow), 2) . ' ' . $units[$pow];
    }
}
