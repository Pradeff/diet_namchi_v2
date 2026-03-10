<?php
namespace App\Controller;

use App\Repository\VgalleryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Vgallery;
use App\Repository\VfaqRepository;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(
        VfaqRepository $vfaqRepository
    ): Response
    {
        return $this->render('main/index.html.twig', [
            'vfaqs' => $vfaqRepository->findAllOrdered(),
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function PgAbout(): Response
    {
        return $this->render('main/about.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function PgContact(): Response
    {
        return $this->render('main/contact.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

 /*   #[Route('/faq', name: 'app_faq')]
    public function faq(VfaqRepository $vfaqRepository): Response
    {
        return $this->render('main/faq.html.twig', [
            'vfaqs' => $vfaqRepository->findAllOrdered(),
        ]);
    }*/

    #[Route('/notices', name: 'app_notices')]
    public function PgNotices(): Response
    {
        return $this->render('main/notices.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/gallery', name: 'app_gallery')]
    public function PgGallery(VgalleryRepository $vgalleryRepository): Response
    {
        return $this->render('main/gallery.html.twig', [
            'vgalleries' => $vgalleryRepository->findAll(),
        ]);
    }
    #[Route('/gallery/{slug}', name: 'app_gallery_detail')]
    public function PgGalleryDetail(string $slug, VgalleryRepository $vgalleryRepository): Response
    {
        $vgallery = $vgalleryRepository->findOneBy(['slug' => $slug]);

        if (!$vgallery) {
            throw $this->createNotFoundException('Gallery not found.');
        }

        return $this->render('main/gallery_detail.html.twig', [
            'vgallery' => $vgallery,
        ]);
    }

    #[Route('/training', name: 'app_training')]
    public function PgTraining(): Response
    {
        return $this->render('main/training.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/rti', name: 'app_rti')]
    public function PgRti(): Response
    {
        return $this->render('main/rti.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/admission', name: 'app_admission')]
    public function PgAdmission(): Response
    {
        return $this->render('main/admission.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/team', name: 'app_team')]
    public function PgTeam(): Response
    {
        return $this->render('main/team.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/privacy', name: 'app_privacy')]
    public function PgPrivacy(): Response
    {
        return $this->render('main/privacy.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/terms', name: 'app_terms')]
    public function PgTerms(): Response
    {
        return $this->render('main/terms.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/vision-mission', name: 'app_vision_mission')]
    public function PgVisionMission(): Response
    {
        return $this->render('main/vision-mission.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/history', name: 'app_history')]
    public function PgHistory(): Response
    {
        return $this->render('main/history.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/downloads', name: 'app_downloads')]
    public function PgDownloads(): Response
    {
        return $this->render('main/downloads.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
}
