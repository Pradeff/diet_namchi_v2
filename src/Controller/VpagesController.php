<?php
/* src/Controller/VpagesController.php */

namespace App\Controller;

use App\Entity\Vpages;
use App\Form\VpagesType;
use App\Repository\VpagesRepository;
use App\Service\SvgSanitizer;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('/vpages')]
class VpagesController extends AbstractController
{
    // ── Index ────────────────────────────────────────────────────────────

    #[Route('/', name: 'app_vpages_index', methods: ['GET'])]
    public function index(VpagesRepository $vpagesRepository): Response
    {
        return $this->render('vpages/index.html.twig', [
            'vpages' => $vpagesRepository->findAllOrderedByTitle(),
        ]);
    }

    // ── New ──────────────────────────────────────────────────────────────

    #[Route('/new', name: 'app_vpages_new', methods: ['GET', 'POST'])]
    public function new(
        Request                $request,
        EntityManagerInterface $entityManager,
        VpagesRepository       $vpagesRepository,
        SvgSanitizer           $svgSanitizer
    ): Response {
        $vpage = new Vpages();
        $form  = $this->createForm(VpagesType::class, $vpage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Sanitize SVG icon
                if ($vpage->getIcon()) {
                    $vpage->setIcon($svgSanitizer->sanitize($vpage->getIcon()));
                }

                // Ensure slug is set (Gedmo listener may not be registered)
                if (!$vpage->getSlug()) {
                    $vpage->setSlug(
                        $this->generateUniqueSlug($vpage->getTitle(), $vpagesRepository)
                    );
                }

                // Handle image upload → save as WebP
                $imageFile = $form->get('imageFile')->getData();
                if ($imageFile) {
                    $vpage->setImage($this->convertToWebp($imageFile->getPathname()));
                }

                $entityManager->persist($vpage);
                $entityManager->flush();

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success'      => true,
                        'message'      => 'Page created successfully!',
                        'redirect_url' => $this->generateUrl('app_vpages_index'),
                    ]);
                }

                $this->addFlash('success', 'Page created successfully!');
                return $this->redirectToRoute('app_vpages_index');

            } catch (\Exception $e) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Error creating page: ' . $e->getMessage(),
                    ], 400);
                }
                $this->addFlash('error', 'Error creating page: ' . $e->getMessage());
            }
        }

        if ($request->isXmlHttpRequest() && $form->isSubmitted()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Form validation failed',
                'errors'  => $this->collectFormErrors($form),
            ], 400);
        }

        return $this->render('vpages/new.html.twig', [
            'vpage' => $vpage,
            'form'  => $form,
        ]);
    }

    // ── Show ─────────────────────────────────────────────────────────────

    #[Route('/{id}', name: 'app_vpages_show', methods: ['GET'])]
    public function show(Vpages $vpage): Response
    {
        return $this->render('vpages/show.html.twig', [
            'vpage' => $vpage,
        ]);
    }

    // ── Edit ─────────────────────────────────────────────────────────────

    #[Route('/{id}/edit', name: 'app_vpages_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request                $request,
        Vpages                 $vpage,
        EntityManagerInterface $entityManager,
        VpagesRepository       $vpagesRepository,
        SvgSanitizer           $svgSanitizer
    ): Response {
        $existingImage = $vpage->getImage();
        $form          = $this->createForm(VpagesType::class, $vpage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Sanitize SVG icon
                if ($vpage->getIcon()) {
                    $vpage->setIcon($svgSanitizer->sanitize($vpage->getIcon()));
                }

                // Re-generate slug if title changed and slug is now empty,
                // or if Gedmo didn't update it automatically
                if (!$vpage->getSlug()) {
                    $vpage->setSlug(
                        $this->generateUniqueSlug($vpage->getTitle(), $vpagesRepository, $vpage->getId())
                    );
                }

                // Handle image upload → save as WebP, delete old file
                $imageFile = $form->get('imageFile')->getData();
                if ($imageFile) {
                    $this->deleteImage($existingImage);
                    $vpage->setImage($this->convertToWebp($imageFile->getPathname()));
                } else {
                    $vpage->setImage($existingImage);
                }

                $entityManager->flush();

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success'      => true,
                        'message'      => 'Page updated successfully!',
                        'redirect_url' => $this->generateUrl('app_vpages_index'),
                    ]);
                }

                $this->addFlash('success', 'Page updated successfully!');
                return $this->redirectToRoute('app_vpages_index');

            } catch (\Exception $e) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Error updating page: ' . $e->getMessage(),
                    ], 400);
                }
                $this->addFlash('error', 'Error updating page: ' . $e->getMessage());
            }
        }

        if ($request->isXmlHttpRequest() && $form->isSubmitted()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Form validation failed',
                'errors'  => $this->collectFormErrors($form),
            ], 400);
        }

        return $this->render('vpages/edit.html.twig', [
            'vpage' => $vpage,
            'form'  => $form,
        ]);
    }

    // ── Delete ───────────────────────────────────────────────────────────

    #[Route('/{id}', name: 'app_vpages_delete', methods: ['POST'])]
    public function delete(
        Request                $request,
        Vpages                 $vpage,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $vpage->getId(), $request->request->get('_token'))) {
            try {
                $this->deleteImage($vpage->getImage());
                $entityManager->remove($vpage);
                $entityManager->flush();

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(['success' => true, 'message' => 'Page deleted successfully!']);
                }
                $this->addFlash('success', 'Page deleted successfully!');

            } catch (\Exception $e) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(['success' => false, 'message' => 'Error deleting page: ' . $e->getMessage()], 400);
                }
                $this->addFlash('error', 'Error deleting page: ' . $e->getMessage());
            }
        } else {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 400);
            }
            $this->addFlash('error', 'Invalid CSRF token');
        }

        return $this->redirectToRoute('app_vpages_index');
    }

    // ── Ajax list ────────────────────────────────────────────────────────

    #[Route('/ajax-list', name: 'app_vpages_ajax_list', methods: ['GET'])]
    public function ajaxList(VpagesRepository $vpagesRepository): JsonResponse
    {
        $data = array_map(
            fn(Vpages $page) => ['id' => $page->getId(), 'title' => $page->getTitle()],
            $vpagesRepository->findAllOrderedByTitle()
        );

        return new JsonResponse($data);
    }

    // ── Private helpers ──────────────────────────────────────────────────

    /**
     * Generate a URL-safe slug from $title and ensure it is unique
     * in the vpages table. Appends -2, -3, … if a collision is found.
     * Pass $excludeId when editing so the current record is not treated
     * as a conflict with itself.
     */
    private function generateUniqueSlug(
        string           $title,
        VpagesRepository $repository,
        ?int             $excludeId = null
    ): string {
        $slugger = new AsciiSlugger();
        $base    = strtolower($slugger->slug($title)->toString());
        $slug    = $base;
        $counter = 1;

        while (true) {
            $existing = $repository->findOneBy(['slug' => $slug]);

            // No conflict, or the conflict is the record being edited
            if (!$existing || $existing->getId() === $excludeId) {
                break;
            }

            // Collision — append counter and try again
            $counter++;
            $slug = $base . '-' . $counter;
        }

        return $slug;
    }

    /**
     * Read any image format from $sourcePath, encode as WebP at quality 85,
     * save to image_directory, and return the new filename (*.webp).
     */
    private function convertToWebp(string $sourcePath): string
    {
        $manager  = new ImageManager(new Driver());
        $image    = $manager->read($sourcePath);
        $fileName = md5(uniqid()) . '.webp';
        $destPath = $this->getParameter('image_directory') . '/' . $fileName;

        $image->toWebp(85)->save($destPath);

        return $fileName;
    }

    /**
     * Delete an image file from the uploads directory if it exists.
     */
    private function deleteImage(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $filepath = $this->getParameter('image_directory') . '/' . $filename;

        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    /**
     * Collect all form errors into a flat string array.
     */
    private function collectFormErrors(\Symfony\Component\Form\FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }
        return $errors;
    }
}
