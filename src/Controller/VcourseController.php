<?php

namespace App\Controller;

use App\Entity\Vcourse;
use App\Form\VcourseFormType;
use App\Repository\VcourseRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/vcourse')]
class VcourseController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
        private ImageUploader $imageUploader
    ) {}

    #[Route('/', name: 'app_vcourse_index', methods: ['GET'])]
    public function index(VcourseRepository $vcourseRepository): Response
    {
        return $this->render('vcourse/index.html.twig', [
            'vcourses' => $vcourseRepository->findAllOrderedByCreatedAt(),
        ]);
    }

    #[Route('/new', name: 'app_vcourse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, VcourseRepository $vcourseRepository): Response
    {
        $vcourse = new Vcourse();
        $form = $this->createForm(VcourseFormType::class, $vcourse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $this->slugger->slug($vcourse->getTitle())->lower();
            $vcourse->setSlug($slug);

            $this->entityManager->persist($vcourse);
            $this->entityManager->flush();

            $this->addFlash('success', 'Course created successfully!');
            return $this->redirectToRoute('app_vcourse_edit', ['id' => $vcourse->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vcourse/new.html.twig', [
            'vcourse' => $vcourse,
            'form' => $form,
            'max_file_size' => $this->imageUploader->getMaxFileSizeFormatted(),
            'max_file_size_mb' => $this->imageUploader->getMaxFileSize() / 1024 / 1024,
        ]);
    }

    #[Route('/{id}', name: 'app_vcourse_show', methods: ['GET'])]
    public function show(Vcourse $vcourse): Response
    {
        return $this->render('vcourse/show.html.twig', [
            'vcourse' => $vcourse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vcourse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vcourse $vcourse): Response
    {
        $form = $this->createForm(VcourseFormType::class, $vcourse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $this->slugger->slug($vcourse->getTitle())->lower();
            $vcourse->setSlug($slug);

            $this->entityManager->flush();

            $this->addFlash('success', 'Course updated successfully!');
            return $this->redirectToRoute('app_vcourse_show', ['id' => $vcourse->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vcourse/edit.html.twig', [
            'vcourse' => $vcourse,
            'form' => $form,
            'max_file_size' => $this->imageUploader->getMaxFileSizeFormatted(),
            'max_file_size_mb' => $this->imageUploader->getMaxFileSize() / 1024 / 1024,
        ]);
    }

    #[Route('/{id}', name: 'app_vcourse_delete', methods: ['POST'])]
    public function delete(Request $request, Vcourse $vcourse): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vcourse->getId(), $request->request->get('_token'))) {
            $this->imageUploader->deleteMultiple($vcourse->getImages());
            $this->entityManager->remove($vcourse);
            $this->entityManager->flush();
            $this->addFlash('success', 'Course deleted successfully!');
        }

        return $this->redirectToRoute('app_vcourse_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/upload-images', name: 'app_vcourse_upload_images', methods: ['POST'])]
    public function uploadImages(Request $request, Vcourse $vcourse): JsonResponse
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
            $uploadedFiles = $this->imageUploader->uploadMultiple($files, 'course');
            foreach ($uploadedFiles as $fileName) {
                $vcourse->addImage($fileName);
            }
            $this->entityManager->flush();

            $responseFiles = array_map(fn($fileName) => [
                'name' => $fileName,
                'url' => $this->imageUploader->getWebPath($fileName)
            ], $uploadedFiles);

            return new JsonResponse([
                'success' => true,
                'files' => $responseFiles,
                'message' => count($uploadedFiles) . ' file(s) uploaded successfully'
            ]);
        } catch (FileException $e) {
            return new JsonResponse(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/set-cover-image', name: 'app_vcourse_set_cover', methods: ['POST'])]
    public function setCoverImage(Request $request, Vcourse $vcourse): JsonResponse
    {
        if (!$this->isCsrfTokenValid('set_cover', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $imageName = $request->request->get('image');
        if (!$vcourse->hasImage($imageName)) {
            return new JsonResponse(['error' => 'Image not found'], 404);
        }

        $vcourse->setCoverImage($imageName);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Cover image set successfully']);
    }

    #[Route('/{id}/delete-image', name: 'app_vcourse_delete_image', methods: ['DELETE'])]
    public function deleteImage(Request $request, Vcourse $vcourse): JsonResponse
    {
        if (!$this->isCsrfTokenValid('delete_image', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $imageName = $request->request->get('image');
        if (!$vcourse->hasImage($imageName)) {
            return new JsonResponse(['error' => 'Image not found'], 404);
        }

        $vcourse->removeImage($imageName);
        if ($vcourse->getCoverImage() === $imageName) {
            $vcourse->setCoverImage(null);
        }
        $this->imageUploader->delete($imageName);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Image deleted successfully']);
    }
}
