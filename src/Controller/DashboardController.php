<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Invoice;
use App\Entity\Product;
use App\Entity\Quote;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


class DashboardController extends AbstractController
{

    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    #[Route('/utilisateurs', name: 'app_users')]
    public function users(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('dashboard/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/produits', name: 'app_products')]
    public function products(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(Product::class)->findAll();

        return $this->render('dashboard/products.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/categories', name: 'app_categories')]
    public function categories(EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager->getRepository(Category::class)->findAll();

        return $this->render('dashboard/categories.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/devis', name: 'app_quotes')]
    public function quotes(EntityManagerInterface $entityManager): Response
    {
        $users = null;
        $quotes = $entityManager->getRepository(Quote::class)->findBy([$users => $users]);

        return $this->render('dashboard/quotes.html.twig', [
            'quotes' => $quotes,
        ]);
    }

    #[Route('/rapports', name: 'app_reports')]
    public function reports(): Response
    {
        return $this->render('dashboard/reports.html.twig');
    }

}
