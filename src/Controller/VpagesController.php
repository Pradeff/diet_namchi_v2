<?php
/*src/controller/VpagesController.php*/
namespace App\Controller;

use App\Entity\Vpages;
use App\Form\VpagesType;
use App\Repository\VpagesRepository;
use App\Service\ImageUploader;
use App\Service\SvgSanitizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/vpages')]
class VpagesController extends AbstractController
{
    #[Route('/', name: 'app_vpages_index', methods: ['GET'])]
    public function index(VpagesRepository $vpagesRepository): Response
    {
        return $this->render('vpages/index.html.twig', [
            'vpages' => $vpagesRepository->findAllOrderedByTitle(),
        ]);
    }

    #[Route('/new', name: 'app_vpages_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ImageUploader $imageUploader, SvgSanitizer $svgSanitizer): Response
    {
        $vpage = new Vpages();
        $form = $this->createForm(VpagesType::class, $vpage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Sanitize SVG icon
                $icon = $vpage->getIcon();
                if ($icon) {
                    $vpage->setIcon($svgSanitizer->sanitize($icon));
                }

                // Handle image upload
                $imageFile = $form->get('imageFile')->getData();
                if ($imageFile) {
                    $imageName = $imageUploader->upload($imageFile);
                    $vpage->setImage($imageName);
                }

                $entityManager->persist($vpage);
                $entityManager->flush();

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => true,
                        'message' => 'Page created successfully!',
                        'redirect_url' => $this->generateUrl('app_vpages_index')
                    ]);
                }

                $this->addFlash('success', 'Page created successfully!');
                return $this->redirectToRoute('app_vpages_index');
            } catch (\Exception $e) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Error creating page: ' . $e->getMessage()
                    ], 400);
                }

                $this->addFlash('error', 'Error creating page: ' . $e->getMessage());
            }
        }

        if ($request->isXmlHttpRequest() && $form->isSubmitted()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }

            return new JsonResponse([
                'success' => false,
                'message' => 'Form validation failed',
                'errors' => $errors
            ], 400);
        }

        return $this->render('vpages/new.html.twig', [
            'vpage' => $vpage,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_vpages_show', methods: ['GET'])]
    public function show(Vpages $vpage): Response
    {
        return $this->render('vpages/show.html.twig', [
            'vpage' => $vpage,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vpages_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vpages $vpage, EntityManagerInterface $entityManager, ImageUploader $imageUploader, SvgSanitizer $svgSanitizer): Response
    {
        $form = $this->createForm(VpagesType::class, $vpage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Sanitize SVG icon
                $icon = $vpage->getIcon();
                if ($icon) {
                    $vpage->setIcon($svgSanitizer->sanitize($icon));
                }

                // Handle image upload
                $imageFile = $form->get('imageFile')->getData();
                if ($imageFile) {
                    $imageName = $imageUploader->upload($imageFile);
                    $vpage->setImage($imageName);
                }

                $entityManager->flush();

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => true,
                        'message' => 'Page updated successfully!',
                        'redirect_url' => $this->generateUrl('app_vpages_index')
                    ]);
                }

                $this->addFlash('success', 'Page updated successfully!');
                return $this->redirectToRoute('app_vpages_index');
            } catch (\Exception $e) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Error updating page: ' . $e->getMessage()
                    ], 400);
                }

                $this->addFlash('error', 'Error updating page: ' . $e->getMessage());
            }
        }

        if ($request->isXmlHttpRequest() && $form->isSubmitted()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }

            return new JsonResponse([
                'success' => false,
                'message' => 'Form validation failed',
                'errors' => $errors
            ], 400);
        }

        return $this->render('vpages/edit.html.twig', [
            'vpage' => $vpage,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_vpages_delete', methods: ['POST'])]
    public function delete(Request $request, Vpages $vpage, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vpage->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($vpage);
                $entityManager->flush();

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => true,
                        'message' => 'Page deleted successfully!'
                    ]);
                }

                $this->addFlash('success', 'Page deleted successfully!');
            } catch (\Exception $e) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Error deleting page: ' . $e->getMessage()
                    ], 400);
                }

                $this->addFlash('error', 'Error deleting page: ' . $e->getMessage());
            }
        } else {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Invalid CSRF token'
                ], 400);
            }

            $this->addFlash('error', 'Invalid CSRF token');
        }

        return $this->redirectToRoute('app_vpages_index');
    }

    #[Route('/ajax-list', name: 'app_vpages_ajax_list', methods: ['GET'])]
    public function ajaxList(VpagesRepository $vpagesRepository): JsonResponse
    {
        $pages = $vpagesRepository->findAllOrderedByTitle();
        $data = [];

        foreach ($pages as $page) {
            $data[] = [
                'id' => $page->getId(),
                'title' => $page->getTitle()
            ];
        }

        return new JsonResponse($data);
    }
}
