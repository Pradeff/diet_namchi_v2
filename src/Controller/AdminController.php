<?php
/*src/controller/AdminController.php*/
namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(UserRepository $userRepository): Response
    {
        $totalUsers = $userRepository->count([]);
        $activeUsers = $userRepository->count(['isActive' => true]);
        $recentUsers = $userRepository->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'recentUsers' => $recentUsers,
        ]);
    }

    #[Route('/users', name: 'admin_users')]
    public function users(
        UserRepository $userRepository,
        Request $request,
        RateLimiterFactoryInterface $apiRequestsLimiter
    ): Response {
        // Rate limit API requests
        $limiter = $apiRequestsLimiter->create($request->getClientIp());
        if (false === $limiter->consume(1)->isAccepted()) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['error' => 'Rate limit exceeded'], 429);
            }
            $this->addFlash('error', 'Too many requests. Please slow down.');
        }

        $search = $request->query->get('search', '');
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        if ($search) {
            $users = $userRepository->findBySearchTerm($search, $page, $limit);
            $total = $userRepository->countBySearchTerm($search);
        } else {
            $users = $userRepository->findBy([], ['createdAt' => 'DESC'], $limit, ($page - 1) * $limit);
            $total = $userRepository->count([]);
        }

        $totalPages = ceil($total / $limit);

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'total' => $total,
        ]);
    }

    #[Route('/users/create', name: 'admin_users_new')]
    public function createUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        RateLimiterFactoryInterface $userRegistrationLimiter,
        RateLimiterFactoryInterface $adminActionsLimiter
    ): Response {
        // Rate limit user creation
        $registrationLimiter = $userRegistrationLimiter->create($request->getClientIp());
        $adminLimiter = $adminActionsLimiter->create($this->getUser()->getUserIdentifier());

        if (false === $registrationLimiter->consume(1)->isAccepted()) {
            $this->addFlash('error', 'User creation rate limit exceeded.');
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'message' => 'Rate limit exceeded'], 429);
            }
            return $this->redirectToRoute('admin_users');
        }

        if (false === $adminLimiter->consume(1)->isAccepted()) {
            $this->addFlash('error', 'Admin action rate limit exceeded.');
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'message' => 'Admin rate limit exceeded'], 429);
            }
            return $this->redirectToRoute('admin_users');
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User created successfully.');

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => true, 'message' => 'User created successfully.']);
            }

            return $this->redirectToRoute('admin_users');
        }

        if ($request->isXmlHttpRequest() && $form->isSubmitted()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }
            return new JsonResponse(['success' => false, 'errors' => $errors]);
        }

        return $this->render('admin/users/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users/{id}', name: 'admin_users_show')]
    public function showUser(User $user): Response
    {
        return $this->render('admin/users/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/users/{id}/edit', name: 'admin_users_edit')]
    public function editUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        RateLimiterFactoryInterface $adminActionsLimiter
    ): Response {
        // Rate limit admin actions
        $limiter = $adminActionsLimiter->create($this->getUser()->getUserIdentifier());
        if (false === $limiter->consume(1)->isAccepted()) {
            $this->addFlash('error', 'Admin action rate limit exceeded.');
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'message' => 'Rate limit exceeded'], 429);
            }
            return $this->redirectToRoute('admin_users');
        }

        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->has('password') && $form->get('password')->getData()) {
                $plainPassword = $form->get('password')->getData();
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();
            $this->addFlash('success', 'User updated successfully.');

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => true, 'message' => 'User updated successfully.']);
            }

            return $this->redirectToRoute('admin_users');
        }

        if ($request->isXmlHttpRequest() && $form->isSubmitted()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }
            return new JsonResponse(['success' => false, 'errors' => $errors]);
        }

        return $this->render('admin/users/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/users/{id}/delete', name: 'admin_users_delete', methods: ['POST'])]
    public function deleteUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        RateLimiterFactoryInterface $adminActionsLimiter
    ): JsonResponse {
        // Rate limit admin actions
        $limiter = $adminActionsLimiter->create($this->getUser()->getUserIdentifier());
        if (false === $limiter->consume(1)->isAccepted()) {
            return new JsonResponse(['success' => false, 'message' => 'Rate limit exceeded'], 429);
        }

        if (!$this->isCsrfTokenValid('delete_user_' . $user->getId(), $request->getPayload()->getString('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid CSRF token.'], 400);
        }

        // Prevent self-deletion
        if ($user === $this->getUser()) {
            return new JsonResponse(['success' => false, 'message' => 'You cannot delete your own account.'], 400);
        }

        // Prevent deleting super admin
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && !in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles())) {
            return new JsonResponse(['success' => false, 'message' => 'You cannot delete a super admin account.'], 403);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'User deleted successfully.']);
    }

    #[Route('/users/{id}/toggle-status', name: 'admin_users_toggle_status', methods: ['POST'])]
    public function toggleUserStatus(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        RateLimiterFactoryInterface $adminActionsLimiter
    ): JsonResponse {
        // Rate limit admin actions
        $limiter = $adminActionsLimiter->create($this->getUser()->getUserIdentifier());
        if (false === $limiter->consume(1)->isAccepted()) {
            return new JsonResponse(['success' => false, 'message' => 'Rate limit exceeded'], 429);
        }

        if (!$this->isCsrfTokenValid('toggle_status_' . $user->getId(), $request->getPayload()->getString('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid CSRF token.'], 400);
        }

        // Prevent self-deactivation
        if ($user === $this->getUser() && $user->isActive()) {
            return new JsonResponse(['success' => false, 'message' => 'You cannot deactivate your own account.'], 400);
        }

        // Prevent deactivating super admin
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && !in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles())) {
            return new JsonResponse(['success' => false, 'message' => 'You cannot deactivate a super admin account.'], 403);
        }

        $user->setIsActive(!$user->isActive());
        $entityManager->flush();

        $status = $user->isActive() ? 'activated' : 'deactivated';
        return new JsonResponse(['success' => true, 'message' => "User {$status} successfully.", 'isActive' => $user->isActive()]);
    }
}
