<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Repo;
use App\Entity\User;

final class RepoDisplayController extends AbstractController
{
    #[Route('/u/{username}/{repo_name}', name: 'app_repo_display')]
    public function index(string $username, string $repo_name, EntityManagerInterface $em): Response
    {
        $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        $repo_name = htmlspecialchars($repo_name, ENT_QUOTES, 'UTF-8');

        $viewer = $this->getUser();
        $userId = null;
        if ($viewer instanceof \App\Entity\User) {
            $userId = $viewer->getId();
        } else {
            $userId = null;
        }
        $owner = $em->getRepository(User::class)->findOneBy(['username' => $username]);
        if (!$owner) {
            $this->addFlash('error', 'User not found.');
            // TODO: Custom error
            return $this->redirectToRoute('app_main');
        }
        $repo = $em->getRepository(Repo::class)->findOneBy(['owner' => $owner, 'name' => $repo_name, 'deleted' => false]);
        if (!$repo) {
            $this->addFlash('error', 'Repository not found.');
            // TODO: Custom error
            return $this->redirectToRoute('app_main');
        }


        // At this point we have the repo and the owner

        if ($userId !== $owner->getId() && $repo->getIsPrivate()) {
            return $this->redirectToRoute('app_main');
        }

        $files = []; //TODO
        // TODO: Change this
        $exec = "git ls-tree --name-only -r HEAD";
        $serverPath = $repo->getServerpath();
        exec("cd $serverPath && $exec", $files, $return_var);
        $error = false;
        if ($return_var !== 0) {
            $this->addFlash('error', 'Error retrieving files.');
            $error = true;
        }
        //var_dump($files);
        return $this->render('repo_display/index.html.twig', [
            'repo' => $repo,
            'owner' => $owner,
            'viewer' => $viewer,
            'error' => $error,
            'files' => $files
        ]);
    }
}
