<?php
/*src/controller/ProfileController.php*/
namespace App\Controller;

use App\Entity\User;
use App\Form\PasswordChangeType;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile', methods: ['GET'])]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Handle profile photo upload
                $profilePhoto = $form->get('profilePhoto')->getData();
                if ($profilePhoto) {
                    // Delete old photo if exists
                    if ($user->getProfilePhoto()) {
                        $oldFile = $this->getParameter('image_directory') . '/' . $user->getProfilePhoto();
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }

                    $newFilename = $this->uploadProfilePhoto($profilePhoto, $slugger);
                    $user->setProfilePhoto($newFilename);
                }

                $entityManager->flush();

                return new JsonResponse([
                    'success' => true,
                    'message' => 'Profile updated successfully!',
                    'redirect' => $this->generateUrl('app_profile')
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'errors' => $this->getFormErrors($form)
                ]);
            }
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/change-password', name: 'app_profile_change_password', methods: ['GET', 'POST'])]
    public function changePassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(PasswordChangeType::class);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Check if current password is correct
                $currentPassword = $form->get('currentPassword')->getData();
                if (!$userPasswordHasher->isPasswordValid($user, $currentPassword)) {
                    return new JsonResponse([
                        'success' => false,
                        'errors' => ['currentPassword' => ['Current password is incorrect']]
                    ]);
                }

                // Update password
                $newPassword = $form->get('newPassword')->getData();
                $user->setPassword($userPasswordHasher->hashPassword($user, $newPassword));
                $entityManager->flush();

                return new JsonResponse([
                    'success' => true,
                    'message' => 'Password changed successfully!',
                    'redirect' => $this->generateUrl('app_profile')
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'errors' => $this->getFormErrors($form)
                ]);
            }
        }

        return $this->render('profile/change_password.html.twig', [
            'form' => $form,
        ]);
    }

    private function uploadProfilePhoto(UploadedFile $file, SluggerInterface $slugger): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move(
                $this->getParameter('image_directory'),
                $newFilename
            );
        } catch (FileException $e) {
            throw new \Exception('Failed to upload profile photo');
        }

        return $newFilename;
    }

    private function getFormErrors($form): array
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors['form'][] = $error->getMessage();
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $fieldErrors = [];
                foreach ($child->getErrors() as $error) {
                    $fieldErrors[] = $error->getMessage();
                }

                if ($fieldErrors) {
                    $errors[$child->getName()] = $fieldErrors;
                }
            }
        }

        return $errors;
    }
}
