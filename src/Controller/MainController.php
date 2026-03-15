<?php
namespace App\Controller;

use App\Repository\VaboutRepository;
use App\Repository\VcontactRepository;
use App\Repository\VcourseRepository;
use App\Repository\VgalleryRepository;
use App\Repository\VnoticeRepository;
use App\Repository\VpagesRepository;
use App\Repository\VteamRepository;
use App\Repository\VtrainingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Vgallery;
use App\Repository\VfaqRepository;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(
        VfaqRepository $vfaqRepository,
        VteamRepository $vteamRepository
    ): Response {
        return $this->render('main/index.html.twig', [
            'vfaqs' => $vfaqRepository->findFirstThreeOrdered(),
            'vteams' => $vteamRepository->findTopN(8),
            'principal' => $vteamRepository->findOneBy(['position' => 0]) ?? null,  // First/lowest position
        ]);
    }


    #[Route('/about', name: 'app_about')]
    public function PgAbout(VaboutRepository $vaboutRepository): Response
    {
        $vabout = $vaboutRepository->findFirst();

        return $this->render('main/about.html.twig', [
            'vabout' => $vabout,
        ]);
    }


    #[Route('/contact', name: 'app_contact')]
    public function PgContact(): Response
    {
        return $this->render('main/contact.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    #[Route('/_faq-section', name: 'app_main_sec_faq', methods: ['GET'])]
    public function SecFaq(VfaqRepository $vfaqRepository): Response
    {
        $vfaqs = $vfaqRepository->findFirstThreeOrdered(3);

        return $this->render('main/_faq_section.html.twig', [
            'vfaqs' => $vfaqs,
        ]);
    }

    #[Route('/faq', name: 'app_pg_faq')]
    public function PgFaq(VfaqRepository $vfaqRepository): Response
    {
        $vfaqs = $vfaqRepository->findAllOrdered();

        return $this->render('main/page_faq.html.twig', [
            'vfaqs' => $vfaqs,
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
    public function PgNotices(Request $request, VnoticeRepository $vnoticeRepository): Response
    {
        $currentYear = (int) date('Y');
        $limit       = 5;
        $page        = max(1, (int) $request->query->get('page', 1));

        $notices      = $vnoticeRepository->findCurrentYearPaginated($currentYear, $page, $limit);
        $total        = $vnoticeRepository->countCurrentYear($currentYear);
        $totalPages   = (int) ceil($total / $limit);

        $archivedNotices = $vnoticeRepository->findGroupedByYear($currentYear);

        return $this->render('main/notices.html.twig', [
            'notices'         => $notices,
            'currentPage'     => $page,
            'totalPages'      => $totalPages,
            'archivedNotices' => $archivedNotices,
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
    public function PgTraining(VtrainingRepository $vtrainingRepository): Response
    {
        return $this->render('main/training.html.twig', [
            'vtrainings' => $vtrainingRepository->findAllOrderedByCreatedAt(),
        ]);
    }

    #[Route('/training/details/{slug}', name: 'app_training_details')]
    public function TrainingDetails(string $slug, VtrainingRepository $vtrainingRepository): Response
    {
        $vtraining = $vtrainingRepository->findOneBy(['slug' => $slug]);

        if (!$vtraining) {
            throw $this->createNotFoundException('Training not found.');
        }

        return $this->render('main/training_details.html.twig', [
            'vtraining' => $vtraining,
        ]);
    }

    /**
     * Sub-request action — rendered via:
     * {{ render(controller('App\\Controller\\MainController::TrainingSection')) }}
     */
    public function TrainingSection(VtrainingRepository $vtrainingRepository): Response
    {
        return $this->render('main/_training_section.html.twig', [
            'vtrainings' => $vtrainingRepository->findAllOrderedByCreatedAt(),
        ]);
    }


    #[Route('/course', name: 'app_course')]
    public function PgCourse(VcourseRepository $vcourseRepository): Response
    {
        return $this->render('main/course.html.twig', [
            'vcourses' => $vcourseRepository->findAllOrderedByCreatedAt(),
        ]);
    }

    #[Route('/course/details/{slug}', name: 'app_course_details')]
    public function CourseDetails(string $slug, VcourseRepository $vcourseRepository): Response
    {
        $vcourse = $vcourseRepository->findOneBy(['slug' => $slug]);

        if (!$vcourse) {
            throw $this->createNotFoundException('Course not found.');
        }

        return $this->render('main/course_details.html.twig', [
            'vcourse' => $vcourse,
        ]);
    }

    /**
     * Sub-request action — rendered via:
     * {{ render(controller('App\\Controller\\MainController::CourseSection')) }}
     */
    public function CourseSection(VcourseRepository $vcourseRepository): Response
    {
        return $this->render('main/_course_section.html.twig', [
            'vcourses' => $vcourseRepository->findAllOrderedByCreatedAt(),
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
    public function PgTeam(VteamRepository $vteamRepository): Response   // ← inject repo
    {
        return $this->render('main/team.html.twig', [
            'vteams'          => $vteamRepository->findAllOrdered(),     // ← pass data
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
    public function PgVisionMission(VaboutRepository $vaboutRepository): Response
    {
        // Adjust the slug/id to match however you store this record
        $vabout = $vaboutRepository->findOneBy(['slug' => 'vision-mission']) ?? $vaboutRepository->find(1);

        return $this->render('main/vision-mission.html.twig', [
            'vabout' => $vabout,
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
    #[Route('/page/{slug}', name: 'page_detail')]
    public function pageDetail(
        string $slug,
        VpagesRepository $pageRepo,
        VcontactRepository $contactRepo
    ): Response {
        $page = $pageRepo->findOneBy(['slug' => $slug]);

        if (!$page) {
            throw $this->createNotFoundException('Page not found');
        }

        $contact = $contactRepo->findOneBy([]);
        $footerPages = $pageRepo->findBy([], ['title' => 'ASC'], 5); // Limit to 5 pages for footer

        return $this->render('main/page_details.html.twig', [
            'page' => $page,
            'contact' => $contact,
            'footerPages' => $footerPages,
        ]);
    }
}
