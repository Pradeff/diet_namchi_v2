<?php
/*src/controller/UserController.php*/
namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
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

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_new' => true]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Handle profile photo upload
                $profilePhoto = $form->get('profilePhoto')->getData();
                if ($profilePhoto) {
                    $newFilename = $this->uploadProfilePhoto($profilePhoto, $slugger);
                    $user->setProfilePhoto($newFilename);
                }

                // Set default values
                $user->setIsActive($form->get('isActive')->getData() ?? true);

                // Encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );

                $entityManager->persist($user);
                $entityManager->flush();

                return new JsonResponse([
                    'success' => true,
                    'message' => 'User created successfully!',
                    'redirect' => $this->generateUrl('app_user_index')
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'errors' => $this->getFormErrors($form)
                ]);
            }
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, SluggerInterface $slugger, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(UserType::class, $user, [
            'is_new' => false,
            'change_password' => true  // Allow password change for super admin
        ]);

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

                // Handle password change if provided
                $newPassword = $form->get('newPassword')->getData();
                if ($newPassword) {
                    $user->setPassword($userPasswordHasher->hashPassword($user, $newPassword));
                }

                // Update active status
                $user->setIsActive($form->get('isActive')->getData());

                $entityManager->flush();

                return new JsonResponse([
                    'success' => true,
                    'message' => 'User updated successfully!',
                    'redirect' => $this->generateUrl('app_user_index')
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'errors' => $this->getFormErrors($form)
                ]);
            }
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            // Delete profile photo if exists
            if ($user->getProfilePhoto()) {
                $photoFile = $this->getParameter('image_directory') . '/' . $user->getProfilePhoto();
                if (file_exists($photoFile)) {
                    unlink($photoFile);
                }
            }

            $entityManager->remove($user);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'User deleted successfully!'
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Invalid token'
        ]);
    }

    #[Route('/{id}/toggle-status', name: 'app_user_toggle_status', methods: ['POST'])]
    public function toggleStatus(User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('toggle-status'.$user->getId(), $request->request->get('_token'))) {
            $user->setIsActive(!$user->isIsActive());
            $entityManager->flush();

            $status = $user->isIsActive() ? 'activated' : 'deactivated';

            return new JsonResponse([
                'success' => true,
                'message' => "User {$status} successfully!",
                'status' => $user->isIsActive()
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Invalid token'
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

    #[Route('/user/delete-image', name: 'app_user_delete_image', methods: ['POST'])]
    public function deleteImage(Request $request, UserRepository $userRepository): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $filename = $data['filename'] ?? null;
            $userId = $data['userId'] ?? null;

            if (!$filename) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'No filename provided'
                ]);
            }

            // Construct the full file path
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/image/';
            $filePath = $uploadsDir . $filename;

            // Check if file exists and delete it
            if (file_exists($filePath)) {
                if (unlink($filePath)) {
                    // If userId is provided, update the database record
                    if ($userId) {
                        $user = $userRepository->find($userId);
                        if ($user) {
                            $user->setProfilePhoto(null);
                            $userRepository->save($user, true);
                        }
                    }

                    return new JsonResponse([
                        'success' => true,
                        'message' => 'Image deleted successfully'
                    ]);
                } else {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Failed to delete image file'
                    ]);
                }
            } else {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Image file not found'
                ]);
            }

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
}
