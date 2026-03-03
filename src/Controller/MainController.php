<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(): Response
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
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

    #[Route('/notices', name: 'app_notices')]
    public function PgNotices(): Response
    {
        return $this->render('main/notices.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/gallery', name: 'app_gallery')]
    public function PgGallery(): Response
    {
        return $this->render('main/gallery.html.twig', [
            'controller_name' => 'MainController',
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
