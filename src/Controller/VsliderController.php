<?php

namespace App\Controller;

use App\Entity\Vslider;
use App\Form\VsliderType;
use App\Repository\VsliderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

#[Route('/vslider')]
class VsliderController extends AbstractController
{
    #[Route('/', name: 'app_vslider_index', methods: ['GET'])]
    public function index(VsliderRepository $vsliderRepository): Response
    {
        return $this->render('vslider/index.html.twig', [
            'vsliders' => $vsliderRepository->findBy([], ['sortOrder' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_vslider_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        VsliderRepository $vsliderRepository
    ): Response {
        $currentCount = $vsliderRepository->count([]);

        if ($currentCount >= 5) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Maximum limit reached! You can only have up to 5 banner slides.'
                ], 400);
            }

            $this->addFlash('error', 'Maximum limit reached! You can only have up to 5 banner slides.');
            return $this->redirectToRoute('app_vslider_index');
        }

        $vslider = new Vslider();
        $form = $this->createForm(VsliderType::class, $vslider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $mediaFile */
            $mediaFile = $form->get('mediaFile')->getData();
            $mediaType = $vslider->getMediaType() ?? 'image'; // default image

            if ($mediaFile) {
                try {
                    if ($mediaType === 'image') {
                        $filename = $this->saveMediaFile($mediaFile, $slugger, 'image');
                    } else {
                        $filename = $this->saveMediaFile($mediaFile, $slugger, 'video');
                    }
                    $vslider->setMediaPath($filename);
                    $vslider->setMediaType($mediaType);
                } catch (\Exception $e) {
                    if ($request->isXmlHttpRequest()) {
                        return new JsonResponse([
                            'success' => false,
                            'message' => 'Failed to upload media file. Please try again.'
                        ], 400);
                    }

                    $this->addFlash('error', 'Failed to upload media file. Please try again.');
                    return $this->redirectToRoute('app_vslider_new');
                }
            }

            $vslider->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->persist($vslider);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success'  => true,
                    'message'  => 'Banner slide created successfully!',
                    'redirect' => $this->generateUrl('app_vslider_index')
                ]);
            }

            $this->addFlash('success', 'Banner slide created successfully!');
            return $this->redirectToRoute('app_vslider_index');
        }

        return $this->render('vslider/new.html.twig', [
            'vslider' => $vslider,
            'form'    => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vslider_show', methods: ['GET'])]
    public function show(Vslider $vslider): Response
    {
        return $this->render('vslider/show.html.twig', [
            'vslider' => $vslider,
        ]);
    }
    #[Route('/{id}/edit', name: 'app_vslider_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Vslider $vslider,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(VsliderType::class, $vslider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $mediaFile */
            $mediaFile = $form->get('mediaFile')->getData();
            $mediaType = $vslider->getMediaType() ?? 'image';

            if ($mediaFile) {
                // Delete old file first
                $this->deleteMediaFile($vslider->getMediaPath());

                try {
                    $filename = ($mediaType === 'image')
                        ? $this->saveMediaFile($mediaFile, $slugger, 'image')
                        : $this->saveMediaFile($mediaFile, $slugger, 'video');

                    $vslider->setMediaPath($filename);
                    $vslider->setMediaType($mediaType);
                } catch (\Exception $e) {
                    if ($request->isXmlHttpRequest()) {
                        return new JsonResponse([
                            'success' => false,
                            'message' => 'Failed to upload media file'
                        ], 400);
                    }

                    $this->addFlash('error', 'Failed to upload media file');
                    return $this->redirectToRoute('app_vslider_edit', ['id' => $vslider->getId()]);
                }
            }

            $vslider->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success'  => true,
                    'message'  => 'Banner slide updated successfully!',
                    'redirect' => $this->generateUrl('app_vslider_index')
                ]);
            }

            $this->addFlash('success', 'Banner slide updated successfully!');
            return $this->redirectToRoute('app_vslider_index');
        }

        return $this->render('vslider/edit.html.twig', [
            'vslider' => $vslider,
            'form'    => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vslider_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Vslider $vslider,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $vslider->getId(), $request->request->get('_token'))) {
            $this->deleteMediaFile($vslider->getMediaPath());
            $entityManager->remove($vslider);
            $entityManager->flush();

            $this->addFlash('success', 'Banner slide deleted successfully!');
        }

        return $this->redirectToRoute('app_vslider_index');
    }

    #[Route('/home-slider', name: 'app_vslider_home_slider', methods: ['GET'])]
    public function HomeSlider(VsliderRepository $vsliderRepository): Response
    {
        $slides = $vsliderRepository->findAllOrderedBySortOrder();

        return $this->render('vslider/_home_slider.html.twig', [
            'slides' => $slides,
        ]);
    }

    /**
     * Save ANY media file to media_directory
     * Images → .webp, Videos → original extension
     */
    private function saveMediaFile(UploadedFile $file, SluggerInterface $slugger, string $type): string
    {
        $mediaDir = $this->getParameter('media_directory');
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);

        if ($type === 'image') {
            // Convert to webp
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());

            $newFilename = $safeFilename . '-' . uniqid() . '.webp';
            $path = $mediaDir . '/' . $newFilename;

            $image->toWebp(80)->save($path);
        } else {
            // Video - keep original extension
            $extension = $file->guessExtension();
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

            $file->move($mediaDir, $newFilename);
        }

        return $newFilename;
    }

    /**
     * Delete media file from media_directory
     */
    private function deleteMediaFile(?string $mediaPath): void
    {
        if (!$mediaPath) {
            return;
        }

        $path = $this->getParameter('media_directory') . '/' . $mediaPath;
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
