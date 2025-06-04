<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Repo;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DeleteController extends AbstractController
{
  #[Route('/delete/{username}/{repo_name}', name: 'app_delete_repo')]
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
      return $this->redirectToRoute('app_main');
    }
    $repo = $em->getRepository(Repo::class)->findOneBy(['owner' => $owner, 'name' => $repo_name, 'deleted' => false]);
    if (!$repo) {
      $this->addFlash('error', 'Repository not found.');
      return $this->redirectToRoute('app_main');
    }
    if ($userId !== $owner->getId()) {
      $this->addFlash('error', 'Operation not allowed.');
      return $this->redirectToRoute('app_main');
    }
    $repo->setDeleted(true);
    $em->persist($repo);
    $em->flush();
    $this->addFlash('success', 'Repository deleted successfully.');
    return $this->redirectToRoute('app_main');
  }
}
