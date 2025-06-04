<?php

namespace App\Controller;

use App\Repository\RepoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

final class AdminController extends AbstractController
{
    public function checkPrivileges($user): bool
    {
        if (!$user || ($user->getRole() !== "admin" && $user->getRole() !== "superadmin")) {
            throw $this->createAccessDeniedException('You do not have permission to access this page.');
        }
        return true;
    }
    #[Route('/admin', name: 'app_admin')]
    public function index(UserRepository $userRepository): Response
    {
        $this->checkPrivileges($this->getUser());
        $users = $userRepository->findBy(['deleted' => "0", 'role' => 'user'], ['username' => 'ASC']);

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'users' => $users,
        ]);
    }

    #[Route('/admin/delete_user/{id}', name: 'app_admin_delete_user')]
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $this->checkPrivileges($this->getUser());
        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_admin');
        }

        // Soft delete the user
        $user->setDeleted("1");
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'User deleted successfully.');
        return $this->redirectToRoute('app_admin');
    }
    #[Route('/admin/see_repos/{id}', name: 'app_admin_see_repos')]
    public function seeRepos(int $id, UserRepository $userRepository): Response
    {
        $this->checkPrivileges($this->getUser());
        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_admin');
        }

        // Fetch repositories for the user
        $repos = $user->getRepos();
        if (!$repos) {
            $this->addFlash('info', 'No repositories found for this user.');
        }

        return $this->render('admin/see_repos.html.twig', [
            'controller_name' => 'AdminController',
            'user' => $user,
            'repos' => $repos,
        ]);
    }
    #[Route('/admin/delete_repo/{id}', name: 'app_admin_delete_repo')]
    public function deleteRepo(int $id, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $this->checkPrivileges($this->getUser());
        $repo = $userRepository->findRepoById($id);
        if (!$repo) {
            $this->addFlash('error', 'Repository not found.');
            return $this->redirectToRoute('app_admin');
        }

        // Soft delete the repository
        $repo->setDeleted(true);
        $em->persist($repo);
        $em->flush();
        $this->addFlash('success', 'Repository deleted successfully.');
        return $this->redirectToRoute('app_admin');
    }
    #[Route('/admin/all_repos', name: 'app_admin_all_repos')]
    public function allRepos(UserRepository $userRepository, RepoRepository $repoRepository): Response
    {
        $this->checkPrivileges($this->getUser());
        $repos = $repoRepository->findNonDeletedBy([]);
        if (!$repos) {
            $this->addFlash('info', 'No repositories found.');
        }

        return $this->render('admin/all_repos.html.twig', [
            'controller_name' => 'AdminController',
            'repos' => $repos,
        ]);
    }
}
