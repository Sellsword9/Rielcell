<?php

namespace App\Controller;

use App\Entity\Repo;
use App\Form\RepoWebType;
use App\Repository\RepoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;

final class MainController extends AbstractController
{
    private string $reposPath;

    public function __construct(ParameterBagInterface $params)
    {
        // Define la ruta base a los repositorios
        $this->reposPath = rtrim($params->get('repos_path'), '/') . '/';
    }

    #[Route('/', name: 'app_main')]
    public function index(RepoRepository $repoRepository): Response
    {
        # If user is deleted, redirect to login
        $user = $this->getUser();
        if ($user instanceof User && $user->getDeleted() == "1") {
            return $this->redirectToRoute('app_logout');
        }
        $repos = $repoRepository->findNonDeletedBy(['owner' => $this->getUser()]);
        if (!$repos) {
            $this->addFlash('info', 'No repositories found for this user.');
        }

        foreach ($repos as $repo) {
            if ($repo->getName() === null) {
                $repo->setName('Unnamed');
            }
        }

        #Check admin
        $isAdmin = false;
        $user = $this->getUser();
        if ($user instanceof User) {
            if ($user && ($user->getRole() == 'admin' || $user->getRole() == 'superadmin')) {
                $isAdmin = true;
            }
        }

        return $this->render('main/index.html.twig', [
            'repos' => $repos,
            'controller_name' => 'MainController',
            'isAdmin' => $isAdmin,
        ]);
    }

    public function createGitRepo(Repo $repo): void
    {
        $serverPath = $repo->getServerpath();

        // Crea un repositorio bare usando Symfony Process
        $process = new Process(['git', 'init', '--bare', $serverPath]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    #[Route('/new_repo', name: 'app_web_repo')]
    public function newRepo(Request $request, EntityManagerInterface $em): Response
    {
        $repo = new Repo();
        $repo->setDeleted(false);
        $repo->setOwner($this->getUser());

        $blacklist = $repo->getOwner()->getNamesBlacklist();

        $form = $this->createForm(RepoWebType::class, $repo, [
            'blacklist' => $blacklist,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repo->setVcs($form->get('vcs')->getData());
            $repo->setIsPrivate($form->get('isPrivate')->getData());

            $rawName = $form->get('name')->getData();
            $n = strtolower($rawName);
            $repo->setName($n);

            $serverPath = $this->reposPath . $repo->getOwner()->getUsername() . '/' . $n . '.git';
            $repo->setServerpath($serverPath);

            if ($repo->getVcs() === 'git') {
                if (!is_dir($serverPath)) {
                    $dirCreated = mkdir($serverPath, 0777, true);
                    $repo->setHasDirectorymade($dirCreated);

                    if ($dirCreated) {
                        $this->createGitRepo($repo);
                    }
                } else {
                    $repo->setHasDirectorymade(true);
                    $this->createGitRepo($repo);
                }
            }

            $em->persist($repo);
            $em->flush();

            return $this->redirectToRoute('app_main');
        }

        return $this->render('repo/web_repo.html.twig', [
            'form' => $form->createView(),
            'blacklist' => $blacklist,
        ]);
    }
}
