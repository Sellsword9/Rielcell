<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Repo;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserProfileController extends AbstractController
{
    #[Route('/u/{username}', name: 'app_user_profile')]
    public function index(string $username, EntityManagerInterface $em): Response
    {
        $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        $u = $em->getRepository(User::class)->findOneBy(['username' => $username]);
        /** @var User|null $viewer */
        $viewer = $this->getUser();
        $is_owner = false;
        if ($viewer instanceof \App\Entity\User && $viewer->getId() === $u->getId())
            $is_owner = true;
        if (!$u) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_main');
        }
        $repos = $em->getRepository(Repo::class)->findBy(['owner' => $u, 'deleted' => false, 'isPrivate' => false]);
        return $this->render('user_profile/index.html.twig', [
            'repos' => $repos,
            'user' => $u,
            'is_owner' => $is_owner,
        ]);
    }
}
