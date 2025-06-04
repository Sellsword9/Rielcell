<?php

namespace App\Controller;

use App\Repository\RepoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;

final class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function index(Request $request, RepoRepository $repoRepository): Response
    {
        $name = $request->query->get('name', '');
        $page = $request->query->getInt('page', 1); // Get the page number from the query parameters, default to 1
        $size = 5;
        if (!is_numeric($page) || $page < 1) {
            $page = 1; // Default to the first page if the page number is invalid
        }
        if (!$name) {
            $name = '';
        }
        // Query building
        $offset = ($page - 1) * $size; // Calculate the offset for pagination
        $q = $repoRepository->createQueryBuilder('r')
            ->where('r.deleted = false')
            ->andWhere('r.isPrivate = false')
            ->andWhere('r.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->setFirstResult($offset)
            ->setMaxResults($size)
            ->orderBy('r.name', 'ASC');
        $repos = $q->getQuery()->getResult();
        $total = 0;
        $qtotal = $repoRepository->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.deleted = false')
            ->andWhere('r.isPrivate = false')
            ->andWhere('r.name LIKE :name')
            ->setParameter('name', '%' . $name . '%');
        $total = $qtotal->getQuery()->getSingleScalarResult();
        if ($total === null) {
            $total = 0; // Ensure total is zero if no results found
        }
        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
            'repos' => $repos,
            'total' => $total,
            'pagesize' => count($repos), // Ensure pagesize does not exceed total
            // Important to ceil because the last page may not be full
            'totalPages' => ceil($total / $size),
            'currentPage' => $page,
            'query' => $name,
        ]);
    }
}
