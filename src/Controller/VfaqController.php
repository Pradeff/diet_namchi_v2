<?php
// src/Controller/VfaqController.php
namespace App\Controller;

use App\Entity\Vfaq;
use App\Form\VfaqFormType;
use App\Repository\VfaqRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/vfaq')]
class VfaqController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    // ── Index ──────────────────────────────────────────────────────────────
    #[Route('/', name: 'app_vfaq_index', methods: ['GET'])]
    public function index(VfaqRepository $vfaqRepository): Response
    {
        return $this->render('vfaq/index.html.twig', [
            'vfaqs' => $vfaqRepository->findAllOrdered(),
        ]);
    }

    // ── New ────────────────────────────────────────────────────────────────
    #[Route('/new', name: 'app_vfaq_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $vfaq = new Vfaq();
        $form = $this->createForm(VfaqFormType::class, $vfaq);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($vfaq);
            $this->entityManager->flush();

            // "Save & New" button was clicked — redirect back to a fresh new form
            if ($request->request->has('_save_and_new')) {
                $this->addFlash('success', 'FAQ saved! You can now add another.');
                return $this->redirectToRoute('app_vfaq_new');
            }

            // Default "Save" button — go to index
            $this->addFlash('success', 'FAQ created successfully!');
            return $this->redirectToRoute('app_vfaq_index');
        }

        return $this->render('vfaq/new.html.twig', [
            'vfaq' => $vfaq,
            'form' => $form,
        ]);
    }

    // ── Edit ───────────────────────────────────────────────────────────────
    #[Route('/{id}/edit', name: 'app_vfaq_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vfaq $vfaq): Response
    {
        $form = $this->createForm(VfaqFormType::class, $vfaq);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'FAQ updated successfully!');
            return $this->redirectToRoute('app_vfaq_index');
        }

        return $this->render('vfaq/edit.html.twig', [
            'vfaq' => $vfaq,
            'form' => $form,
        ]);
    }

    // ── Delete ─────────────────────────────────────────────────────────────
    #[Route('/{id}', name: 'app_vfaq_delete', methods: ['POST'])]
    public function delete(Request $request, Vfaq $vfaq): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vfaq->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($vfaq);
            $this->entityManager->flush();
            $this->addFlash('success', 'FAQ deleted successfully!');
        }

        return $this->redirectToRoute('app_vfaq_index');
    }
}
