<?php
/*src/controller/VaboutController.php*/
namespace App\Controller;

use App\Entity\Vabout;
use App\Form\VaboutFormType;
use App\Repository\VaboutRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/vabout')]
class VaboutController extends AbstractController
{
    private ImageManager $imageManager;
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
        private ImageUploader $imageUploader,
        private Filesystem $filesystem
    ) {
        $this->imageManager = new ImageManager(new Driver());
    }

    #[Route('/', name: 'app_vabout_index', methods: ['GET'])]
    public function index(VaboutRepository $vaboutRepository): Response
    {
        $vabout = $vaboutRepository->findFirst();

        if (!$vabout) {
            return $this->redirectToRoute('app_vabout_new');
        }

        return $this->redirectToRoute('app_vabout_edit', ['id' => $vabout->getId()]);
    }

    #[Route('/new', name: 'app_vabout_new', methods: ['GET', 'POST'])]
    public function new(Request $request, VaboutRepository $vaboutRepository): Response
    {
        // Check if Vabout already exists (singleton pattern)
        $existingVabout = $vaboutRepository->findFirst();
        if ($existingVabout) {
            $this->addFlash('warning', 'About page already exists. You can only edit the existing one.');
            return $this->redirectToRoute('app_vabout_edit', ['id' => $existingVabout->getId()]);
        }

        $vabout = new Vabout();
        $form = $this->createForm(VaboutFormType::class, $vabout);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $this->slugger->slug($vabout->getTitle())->lower();
            $vabout->setSlug($slug);

            $vabout->setCreatedAt(new \DateTimeImmutable());
            $vabout->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($vabout);
            $this->entityManager->flush();

            $this->addFlash('success', 'About page created successfully!');
            return $this->redirectToRoute('app_vabout_edit', ['id' => $vabout->getId()]);
        }

        return $this->render('vabout/new.html.twig', [
            'vabout' => $vabout,
            'form' => $form,
            'max_file_size' => $this->imageUploader->getMaxFileSizeFormatted(),
            'max_file_size_mb' => $this->imageUploader->getMaxFileSize() / 1024 / 1024,
        ]);
    }

    #[Route('/{id}', name: 'app_vabout_show', methods: ['GET'])]
    public function show(Vabout $vabout): Response
    {
        return $this->render('vabout/show.html.twig', [
            'vabout' => $vabout,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vabout_edit', methods: ['GET', 'POST'])]
    public function edit($id, Request $request, Vabout $vabout, EntityManagerInterface $entityManager): Response
    {
        $entity = $entityManager->getRepository(Vabout::class)->find($id);
        $filename = $vabout->getCoverImage();
        $form = $this->createForm(VaboutFormType::class, $vabout);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $this->slugger->slug($vabout->getTitle())->lower();
            $vabout->setSlug($slug);

            $file = $form->get('cover_image')->getData();
            if ($file) {
                if ($filename) {
                    $filepath = $this->getParameter('image_directory') . '/' . $filename;
                    if ($this->filesystem->exists($filepath)) {
                        $this->filesystem->remove($filepath);
                    }
                }
                $fileName1 = $this->generateUniqueFileName() . '.jpg';
                $directory = $this->getParameter('image_directory');
                $filePath = $directory . '/' . $fileName1;

                // Ensure the directory exists
                if (!$this->filesystem->exists($directory)) {
                    $this->filesystem->mkdir($directory);
                }

                // Compress and save the image
                $image = $this->imageManager->read($file->getPathname());
                //$image->cover(800, 600);
                $image->toWebp(80)->save($filePath);

                $vabout->setCoverImage($fileName1);
            } else {
                $vabout->setCoverImage($filename);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'About page updated successfully!');
            return $this->redirectToRoute('app_vabout_show', ['id' => $vabout->getId()]);
        }
        $imageUrl = $entity->getCoverImage();
        return $this->render('vabout/edit.html.twig', [
            'vabout' => $vabout,
            'form' => $form,
            'max_file_size' => $this->imageUploader->getMaxFileSizeFormatted(),
            'max_file_size_mb' => $this->imageUploader->getMaxFileSize() / 1024 / 1024,
            'image_url' => $imageUrl,
        ]);
    }

    private function generateUniqueFileName(): string
    {
        return md5(uniqid());
    }

    #[Route('/{id}/upload-images', name: 'app_vabout_upload_images', methods: ['POST'])]
    public function uploadImages(Request $request, Vabout $vabout): JsonResponse
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
            $uploadedFiles = $this->imageUploader->uploadMultiple($files, 'about');

            foreach ($uploadedFiles as $fileName) {
                $vabout->addImage($fileName, '');
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

    /*#[Route('/{id}/set-cover-image', name: 'app_vabout_set_cover', methods: ['POST'])]
    public function setCoverImage(Request $request, Vabout $vabout): JsonResponse
    {
        if (!$this->isCsrfTokenValid('set_cover', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $imageName = $request->request->get('image');
        if (!$vabout->hasImage($imageName)) {
            return new JsonResponse(['error' => 'Image not found'], 404);
        }

        $vabout->setCoverImage($imageName);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Cover image set successfully'
        ]);
    }*/

    #[Route('/{id}/delete-image', name: 'app_vabout_delete_image', methods: ['POST'])]
    public function deleteImage(Request $request, Vabout $vabout): JsonResponse
    {
        if (!$this->isCsrfTokenValid('delete_image', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $imageName = $request->request->get('image');
        if (!$vabout->hasImage($imageName)) {
            return new JsonResponse(['error' => 'Image not found'], 404);
        }

        $vabout->removeImage($imageName);

        if ($vabout->getCoverImage() === $imageName) {
            $vabout->setCoverImage(null);
        }

        $this->imageUploader->delete($imageName);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Image deleted successfully'
        ]);
    }

    #[Route('/{id}/update-image-title', name: 'app_vabout_update_image_title', methods: ['POST'])]
    public function updateImageTitle(Request $request, Vabout $vabout): JsonResponse
    {
        if (!$this->isCsrfTokenValid('update_title', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }

        $imageName = $request->request->get('image');
        $title = $request->request->get('title', '');

        if (!$vabout->hasImage($imageName)) {
            return new JsonResponse(['error' => 'Image not found'], 404);
        }

        $vabout->updateImageTitle($imageName, $title);
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
