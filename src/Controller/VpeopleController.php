<?php

namespace App\Controller;

use App\Entity\Vpeople;
use App\Form\VpeopleType;
use App\Repository\VpeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

#[Route('/vpeople')]
class VpeopleController extends AbstractController
{
    #[Route('/', name: 'app_vpeople_index', methods: ['GET'])]
    public function index(VpeopleRepository $vpeopleRepository): Response
    {
        $vpeoples = $vpeopleRepository->findAllOrdered(); // your existing custom method

        return $this->render('vpeople/index.html.twig', [
            'vpeoples' => $vpeoples,
        ]);
    }

    #[Route('/new', name: 'app_vpeople_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        VpeopleRepository $vpeopleRepository
    ): Response {
        // Only allow one CM profile record
        if ($vpeopleRepository->countAll() >= 1) {
            $this->addFlash('warning', 'Chief Minister profile already exists. You can only edit it.');
            return $this->redirectToRoute('app_vpeople_index');
        }

        $vpeople = new Vpeople();
        $form = $this->createForm(VpeopleType::class, $vpeople);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $croppedData = $request->request->get('croppedImageData');

            if ($croppedData) {
                // base64 cropped image -> webp
                $fileName = $this->saveCroppedImage($croppedData);
                $vpeople->setImage($fileName);
            } else {
                /** @var UploadedFile|null $file */
                $file = $form->get('image')->getData();
                if ($file) {
                    // uploaded file -> webp
                    $fileName = $this->saveUploadedAsWebp($file);
                    $vpeople->setImage($fileName);
                }
            }

            $entityManager->persist($vpeople);
            $entityManager->flush();

            $this->addFlash('success', 'Chief Minister profile created.');
            return $this->redirectToRoute('app_vpeople_index');
        }

        return $this->render('vpeople/new.html.twig', [
            'vpeople' => $vpeople,
            'form'    => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vpeople_show', methods: ['GET'])]
    public function show(Vpeople $vpeople): Response
    {
        return $this->render('vpeople/show.html.twig', [
            'vpeople' => $vpeople,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vpeople_edit', methods: ['GET', 'POST'])]
    public function edit(
        int $id,
        Request $request,
        Vpeople $vpeople,
        EntityManagerInterface $entityManager
    ): Response {
        $entity   = $entityManager->getRepository(Vpeople::class)->find($id);
        $filename = $vpeople->getImage();

        $form = $this->createForm(VpeopleType::class, $vpeople);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $croppedData = $request->request->get('croppedImageData');

            if ($croppedData) {
                // remove old file if exists
                if ($filename) {
                    $filepath = $this->getParameter('image_directory') . '/' . $filename;
                    if (file_exists($filepath)) {
                        unlink($filepath);
                    }
                }

                $fileName = $this->saveCroppedImage($croppedData); // webp
                $vpeople->setImage($fileName);
            } else {
                /** @var UploadedFile|null $file */
                $file = $form->get('image')->getData();
                if ($file) {
                    // remove old file if exists
                    if ($filename) {
                        $filepath = $this->getParameter('image_directory') . '/' . $filename;
                        if (file_exists($filepath)) {
                            unlink($filepath);
                        }
                    }

                    $fileName1 = $this->saveUploadedAsWebp($file); // webp
                    $vpeople->setImage($fileName1);
                } else {
                    // keep existing filename
                    $vpeople->setImage($filename);
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Person updated successfully.');

            return $this->redirectToRoute('app_vpeople_index', [], Response::HTTP_SEE_OTHER);
        }

        $imageUrl = $entity ? $entity->getImage() : null;

        return $this->render('vpeople/edit.html.twig', [
            'vpeople'   => $vpeople,
            'form'      => $form,
            'image_url' => $imageUrl,
        ]);
    }

    #[Route('/{id}', name: 'app_vpeople_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Vpeople $vpeople,
        VpeopleRepository $vpeopleRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $image = $vpeople->getImage();

        if ($this->isCsrfTokenValid('delete' . $vpeople->getId(), $request->request->get('_token'))) {
            if ($image) {
                $filepath = $this->getParameter('image_directory') . '/' . $image;
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }

            $entityManager->remove($vpeople);
            $entityManager->flush();
            $vpeopleRepository->reorderPositions();

            $this->addFlash('success', 'Person deleted successfully.');
        }

        return $this->redirectToRoute('app_vpeople_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/vpeople/sort', name: 'app_vpeople_sort', methods: ['POST'])]
    public function sort(
        Request $request,
        VpeopleRepository $vpeopleRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $order = $request->request->all('order');

        foreach ($order as $item) {
            $vpeople = $vpeopleRepository->find($item['id']);
            if ($vpeople) {
                $vpeople->setPosition((int) $item['position']);
            }
        }

        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    private function generateUniqueFileName(): string
    {
        return md5(uniqid());
    }

    /**
     * Save uploaded file as webp using intervention/image
     */
    private function saveUploadedAsWebp(UploadedFile $file): string
    {
        $manager = new ImageManager(new Driver()); // or Imagick driver if configured

        $image = $manager->read($file->getRealPath());

        $fileName = $this->generateUniqueFileName() . '.webp';
        $path     = $this->getParameter('image_directory') . '/' . $fileName;

        $image->toWebp(80)->save($path);

        return $fileName;
    }

    /**
     * Save base64 cropped image as webp using intervention/image
     */
    private function saveCroppedImage(string $croppedData): string
    {
        $data    = explode(',', $croppedData);
        $decoded = base64_decode($data[1]);

        $manager = new ImageManager(new Driver());
        $image   = $manager->read($decoded);

        $fileName = $this->generateUniqueFileName() . '.webp';
        $path     = $this->getParameter('image_directory') . '/' . $fileName;

        $image->toWebp(80)->save($path);

        return $fileName;
    }

    #[Route('/cm-profile', name: 'app_vpeople_cm', methods: ['GET'])]
    public function CM(VpeopleRepository $vpeopleRepository): Response
    {
        $cm = $vpeopleRepository->getSingle();

        return $this->render('vpeople/_cm_profile.html.twig', [
            'cm' => $cm,
        ]);
    }
}
