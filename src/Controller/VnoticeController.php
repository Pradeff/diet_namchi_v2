<?php

namespace App\Controller;

use App\Entity\Vnotice;
use App\Form\VnoticeType;
use App\Repository\VnoticeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/vnotice')]
final class VnoticeController extends AbstractController
{
    public function __construct(
        #[Autowire('%pdf_directory%')]
        private readonly string $pdfDirectory,
        private SluggerInterface $slugger,
    ) {}

    // ──────────────────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────────────────
    #[Route('/', name: 'app_vnotice_index', methods: ['GET'])]
    public function index(VnoticeRepository $vnoticeRepository): Response
    {
        return $this->render('vnotice/index.html.twig', [
            'vnotices' => $vnoticeRepository->findBy([], ['noticeDate' => 'DESC']),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // NEW
    // ──────────────────────────────────────────────────────────────────────────
    #[Route('/new', name: 'app_vnotice_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
    ): Response {
        $vnotice = new Vnotice();
        $form    = $this->createForm(VnoticeType::class, $vnotice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $this->slugger->slug($vnotice->getTitle())->lower();
            $vnotice->setSlug($slug);

            $this->handlePdfUpload($form, $vnotice, $slugger);

            $entityManager->persist($vnotice);
            $entityManager->flush();

            $this->addFlash('success', 'Notice created successfully.');
            return $this->redirectToRoute('app_vnotice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vnotice/new.html.twig', [
            'vnotice'      => $vnotice,
            'form'         => $form,
            'button_label' => 'Create Notice',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────────────────
    #[Route('/{id}', name: 'app_vnotice_show', methods: ['GET'])]
    public function show(Vnotice $vnotice): Response
    {
        return $this->render('vnotice/show.html.twig', [
            'vnotice' => $vnotice,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────────────────
    #[Route('/{id}/edit', name: 'app_vnotice_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Vnotice $vnotice,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
    ): Response {
        $form = $this->createForm(VnoticeType::class, $vnotice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $this->slugger->slug($vnotice->getTitle())->lower();
            $vnotice->setSlug($slug);
            $this->handlePdfUpload($form, $vnotice, $slugger, replaceOld: true);
            $entityManager->flush();

            $this->addFlash('success', 'Notice updated successfully.');
            return $this->redirectToRoute('app_vnotice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vnotice/edit.html.twig', [
            'vnotice'      => $vnotice,
            'form'         => $form,
            'button_label' => 'Update Notice',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DELETE
    // ──────────────────────────────────────────────────────────────────────────
    #[Route('/{id}', name: 'app_vnotice_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Vnotice $vnotice,
        EntityManagerInterface $entityManager,
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $vnotice->getId(), $request->getPayload()->getString('_token'))) {
            $this->removePdfFile($vnotice->getPdfFilename());
            $entityManager->remove($vnotice);
            $entityManager->flush();
            $this->addFlash('success', 'Notice deleted successfully.');
        }

        return $this->redirectToRoute('app_vnotice_index', [], Response::HTTP_SEE_OTHER);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────────────────
    private function handlePdfUpload(
        \Symfony\Component\Form\FormInterface $form,
        Vnotice $vnotice,
        SluggerInterface $slugger,
        bool $replaceOld = false
    ): void {
        $pdfFile = $form->get('pdfFile')->getData();

        if (!$pdfFile) {
            return;
        }

        if ($replaceOld) {
            $this->removePdfFile($vnotice->getPdfFilename());
        }

        $safeFilename = $slugger->slug(
            pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME)
        );
        $newFilename = $safeFilename . '-' . uniqid() . '.pdf';

        $pdfFile->move($this->pdfDirectory, $newFilename);
        $vnotice->setPdfFilename($newFilename);
    }

    private function removePdfFile(?string $filename): void
    {
        if (!$filename) {
            return;
        }
        $path = $this->pdfDirectory . '/' . $filename;
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
