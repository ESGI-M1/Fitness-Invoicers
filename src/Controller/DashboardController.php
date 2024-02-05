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
        // TODO : mettre en place une vrai entreprise par défaut
        $user = $this->getUser();
        $companies = $user->getcompanyMemberships()->getValues();
        $company = $companies[0]->getCompany();

        dump($company);

        return $this->render('dashboard/index.html.twig');
    }

    #[Route('/app_default_dev', name: 'app_default_dev')]
    public function app_default_dev(): Response
    {

        return $this->render('dashboard/index.html.twig');
    }

}
