<?php
/*src/controller/vcontactController.php*/

namespace App\Controller;

use App\Entity\Vcontact;
use App\Form\VcontactType;
use App\Repository\VcontactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/vcontact')]
class VcontactController extends AbstractController
{
    #[Route('/', name: 'app_vcontact_index', methods: ['GET'])]
    public function index(VcontactRepository $vcontactRepository): Response
    {
        return $this->render('vcontact/index.html.twig', [
            'vcontacts' => $vcontactRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_vcontact_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, VcontactRepository $vcontactRepository ): Response
    {
        /*$favicon = $vcontact->getFavicon();
        $logohorizontal = $vcontact->getLogohorizontal();
        $logosquare = $vcontact->getLogosquare();*/

        // Check if a contact already exists
        $existingContact = $vcontactRepository->findAll();
        if (count($existingContact) > 0) {
            $this->addFlash('warning', 'Contact already exists. You can only edit the existing one.');
            return $this->redirectToRoute('app_vcontact_index', [], Response::HTTP_SEE_OTHER);
        }

        $vcontact = new Vcontact();
        $form = $this->createForm(VcontactType::class, $vcontact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // favicon
            $favicon = $form->get('favicon')->getData();
            if ($favicon) {
                $faviconName = $this->generateUniqueFileName() . '.' . $favicon->guessExtension();
                $favicon->move(
                    $this->getParameter('website_directory'),
                    $faviconName
                );
                $vcontact->setFavicon($faviconName);
            }

            // Logo 1
            $logo_1 = $form->get('logo1')->getData();
            if ($logo_1) {
                $logo_1_Name = $this->generateUniqueFileName() . '.' . $logo_1->guessExtension();
                $logo_1->move(
                    $this->getParameter('website_directory'),
                    $logo_1_Name
                );
                $vcontact->setLogo1($logo_1_Name);
            }

            // Logo 2
            $logo_2 = $form->get('logo2')->getData();
            if ($logo_2) {
                $logo_2_Name = $this->generateUniqueFileName() . '.' . $logo_2->guessExtension();
                $logo_2->move(
                    $this->getParameter('website_directory'),
                    $logo_2_Name
                );
                $vcontact->setLogo1($logo_2_Name);
            }

            // Logo 3
            $logo_3 = $form->get('logo3')->getData();
            if ($logo_3) {
                $logo_3_Name = $this->generateUniqueFileName() . '.' . $logo_3->guessExtension();
                $logo_3->move(
                    $this->getParameter('website_directory'),
                    $logo_3_Name
                );
                $vcontact->setLogo1($logo_3_Name);
            }

            // Logo 4
            $logo_4 = $form->get('logo4')->getData();
            if ($logo_4) {
                $logo_4_Name = $this->generateUniqueFileName() . '.' . $logo_4->guessExtension();
                $logo_4->move(
                    $this->getParameter('website_directory'),
                    $logo_4_Name
                );
                $vcontact->setLogo1($logo_4_Name);
            }


            $entityManager->persist($vcontact);
            $entityManager->flush();

            $this->addFlash('success', 'Item Added.');

            return $this->redirectToRoute('app_vcontact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vcontact/new.html.twig', [
            'vcontact' => $vcontact,
            'form' => $form,
        ]);
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }

    #[Route('/{id}', name: 'app_vcontact_show', methods: ['GET'])]
    public function show(Vcontact $vcontact): Response
    {
        return $this->render('vcontact/show.html.twig', [
            'vcontact' => $vcontact,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vcontact_edit', methods: ['GET', 'POST'])]
    public function edit($id, Request $request, Vcontact $vcontact, EntityManagerInterface $entityManager): Response
    {
        $faviconname = $vcontact->getFavicon();
        $logo_1_name = $vcontact->getLogo1();
        $logo_2_name = $vcontact->getLogo2();
        $logo_3_name = $vcontact->getLogo3();
        $logo_4_name = $vcontact->getLogo4();

        $entity = $entityManager->getRepository(Vcontact::class)->find($id);

        $form = $this->createForm(VcontactType::class, $vcontact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Favicon
            $favicon = $form->get('favicon')->getData();
            if ($favicon) {
                if ($faviconname) {
                    $filepath = 'images/WebsiteAssets/' . $faviconname;
                    unlink($filepath);
                }
                $faviconName1 = $this->generateUniqueFileName() . '.' . $favicon->guessExtension();
                $favicon->move(
                    $this->getParameter('website_directory'),
                    $faviconName1
                );
                $vcontact->setFavicon($faviconName1);
            } else {
                $vcontact->setFavicon($faviconname);
            }

            // Logo 1
            $logo1 = $form->get('logo1')->getData();
            if ($logo1) {
                if ($logo_1_name) {
                    $filepath = 'images/WebsiteAssets/' . $logo_1_name;
                    unlink($filepath);
                }
                $logo_1_Name1 = $this->generateUniqueFileName() . '.' . $logo1->guessExtension();
                $logo1->move(
                    $this->getParameter('website_directory'),
                    $logo_1_Name1
                );
                $vcontact->setLogo1($logo_1_Name1);
            } else {
                $vcontact->setLogo1($logo_1_name);
            }

            // Logo 2
            $logo2 = $form->get('logo2')->getData();
            if ($logo2) {
                if ($logo_2_name) {
                    $filepath = 'images/WebsiteAssets/' . $logo_2_name;
                    unlink($filepath);
                }
                $logo_2_Name1 = $this->generateUniqueFileName() . '.' . $logo2->guessExtension();
                $logo2->move(
                    $this->getParameter('website_directory'),
                    $logo_2_Name1
                );
                $vcontact->setLogo2($logo_2_Name1);
            } else {
                $vcontact->setLogo2($logo_2_name);
            }

            // Logo 3
            $logo3 = $form->get('logo3')->getData();
            if ($logo3) {
                if ($logo_3_name) {
                    $filepath = 'images/WebsiteAssets/' . $logo_3_name;
                    unlink($filepath);
                }
                $logo_3_Name1 = $this->generateUniqueFileName() . '.' . $logo3->guessExtension();
                $logo3->move(
                    $this->getParameter('website_directory'),
                    $logo_3_Name1
                );
                $vcontact->setLogo3($logo_3_Name1);
            } else {
                $vcontact->setLogo3($logo_3_name);
            }

            // Logo 4
            $logo4 = $form->get('logo4')->getData();
            if ($logo4) {
                if ($logo_4_name) {
                    $filepath = 'images/WebsiteAssets/' . $logo_4_name;
                    unlink($filepath);
                }
                $logo_4_Name1 = $this->generateUniqueFileName() . '.' . $logo4->guessExtension();
                $logo4->move(
                    $this->getParameter('website_directory'),
                    $logo_4_Name1
                );
                $vcontact->setLogo4($logo_4_Name1);
            } else {
                $vcontact->setLogo4($logo_4_name);
            }


            $entityManager->flush();
            $this->addFlash('success', 'Contact Updated.');

            return $this->redirectToRoute('app_vcontact_index', [], Response::HTTP_SEE_OTHER);
        }

        $imageUrl_favicon = $entity->getFavicon();
        $imageUrl_logo1 = $entity->getLogo1();
        $imageUrl_logo2 = $entity->getLogo2();
        $imageUrl_logo3 = $entity->getLogo3();
        $imageUrl_logo4 = $entity->getLogo4();
        return $this->render('vcontact/edit.html.twig', [
            'vcontact' => $vcontact,
            'form' => $form,
            'imageUrl_favicon' => $imageUrl_favicon,
            'imageUrl_logo1' => $imageUrl_logo1,
            'imageUrl_logo2' => $imageUrl_logo2,
            'imageUrl_logo3' => $imageUrl_logo3,
            'imageUrl_logo4' => $imageUrl_logo4,
        ]);
    }

    #[Route('/{id}', name: 'app_vcontact_delete', methods: ['POST'])]
    public function delete(Request $request, Vcontact $vcontact, EntityManagerInterface $entityManager): Response
    {
        $favicon = $vcontact->getFavicon();
        $logohorizontal = $vcontact->getLogohorizontal();
        $logosquare = $vcontact->getLogosquare();
        if ($this->isCsrfTokenValid('delete' . $vcontact->getId(), $request->request->get('_token'))) {
            $entityManager->remove($vcontact);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_vcontact_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('', name: '')]
    public function TopContactDetails(VcontactRepository $vcontactRepository): Response
    {
        $vcontactRepository = $vcontactRepository->findAll();
        return $this->render('vcontact/top-contact-details.html.twig', array(
            'toptactdetails' => $vcontactRepository,
        ));
    }

    public function HeaderTopCont(VcontactRepository $vcontactRepository): Response
    {
        $vcontactRepository = $vcontactRepository->findAll();
        return $this->render('vcontact/header-top.html.twig', array(
            'toptactdetails' => $vcontactRepository,
        ));
    }

    public function FooterContactDetails(VcontactRepository $vcontactRepository): Response
    {
        $contacts = $vcontactRepository->findAll();
        return $this->render('vcontact/footer-contact-details.html.twig', array(
            'footcontactdetails' => $contacts,
        ));
    }

    public function FooterSocialLinks(VcontactRepository $vcontactRepository): Response
    {
        $vcontactRepository = $vcontactRepository->findAll();
        return $this->render('vcontact/footer-social-link.html.twig', array(
            'footersociallink' => $vcontactRepository,
        ));
    }

    public function FloatingPhNo(VcontactRepository $vcontactRepository): Response
    {
        $floatingno = $vcontactRepository->findAll();
        return $this->render('vcontact/floating-contact-no.html.twig', array(
            'floatingno' => $floatingno,
        ));
    }

    public function WhatsNo(VcontactRepository $vcontactRepository): Response
    {
        $whatsappno = $vcontactRepository->findAll();
        return $this->render('vcontact/whatsappno.html.twig', array(
            'whatsappno' => $whatsappno,
        ));
    }

    public function SiteTitle(VcontactRepository $vcontactRepository): Response
    {
        $sitetitle = $vcontactRepository->findAll();
        return $this->render('vcontact/sitename.html.twig', [
            'sitetitle' => $sitetitle,
        ]);
    }

    public function SiteLink(VcontactRepository $vcontactRepository): Response
    {
        $sitelink = $vcontactRepository->findAll();
        return $this->render('vcontact/sitelink.html.twig', array(
            'sitelink' => $sitelink,
        ));
    }

    public function SiteDesc(VcontactRepository $vcontactRepository): Response
    {
        $sitedesc = $vcontactRepository->findAll();
        return $this->render('vcontact/sitedescription.html.twig', array(
            'sitedesc' => $sitedesc,
        ));
    }

    public function PgFavicon(VcontactRepository $vcontactRepository): Response
    {
        $favicon = $vcontactRepository->findAll();
        return $this->render('vcontact/favicon.html.twig', [
            'FavIcon' => $favicon,
        ]);
    }

    //Dark Logo
    public function LogoDark(VcontactRepository $vcontactRepository): Response
    {
        $logoDark = $vcontactRepository->findAll();
        return $this->render('vcontact/logo_dark.html.twig', [
            'logo_1' => $logoDark,
        ]);
    }

    //Light Logo
    public function LogoLight(VcontactRepository $vcontactRepository): Response
    {
        $logoLight = $vcontactRepository->findAll();
        return $this->render('vcontact/logo_light.html.twig', [
            'logo_2' => $logoLight,
        ]);
    }


    public function SiteKeyword(VcontactRepository $vcontactRepository): Response
    {
        $sitekeywords = $vcontactRepository->findAll();
        return $this->render('vcontact/sitekeywords.html.twig', array(
            'sitekeywords' => $sitekeywords,
        ));
    }

    public function EmailOne(VcontactRepository $vcontactRepository): Response
    {
        $email1 = $vcontactRepository->findAll();
        return $this->render('vcontact/email1.html.twig', array(
            'email1' => $email1,
        ));
    }

    public function PhoneOne(VcontactRepository $vcontactRepository): Response
    {
        $phone1 = $vcontactRepository->findAll();
        return $this->render('vcontact/phone1.html.twig', array(
            'phone1' => $phone1,
        ));
    }

}
