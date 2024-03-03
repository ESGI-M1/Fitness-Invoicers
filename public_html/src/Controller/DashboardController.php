<?php

namespace App\Controller;

use App\Form\Dashboard\DateRangeFormType;
use App\Service\CompanySession;
use App\Service\Dashboard;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Form\User\ProfileFormType;
use Doctrine\ORM\EntityManagerInterface;

class DashboardController extends AbstractController
{

    #[Route('/', name: 'app_index')]
    public function index(Request $request, CompanySession $companySession, Dashboard $dashboard): Response
    {
        $company = $companySession->getCurrentCompany();
        if($company instanceof RedirectResponse) {
            return $company;
        }

        $dateRangeForm = $this->createForm(DateRangeFormType::class);
        $dateRangeForm->handleRequest($request);

        if($dateRangeForm->isSubmitted() && $dateRangeForm->isValid()) {

            $statistics = $dashboard->handleForm($dateRangeForm, $company);

            return $this->render('dashboard/index.html.twig', [
                'dateRangeForm' => $dateRangeForm->createView(),
                'statistics' => $statistics
            ]);
        }
        
        return $this->render('dashboard/index.html.twig', [
            'dateRangeForm' => $dateRangeForm->createView(),
            'statistics' => null,
        ]);
    }

    #[Route('/profile', name: 'app_index_profile')]
    public function profile(Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(ProfileFormType::class, $this->getUser());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            return $this->redirectToRoute('app_index_profile');
        }

        return $this->render('profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/app_default_dev', name: 'app_default_dev')]
    public function app_default_dev(): Response
    {

        return $this->render('dashboard/index.html.twig');
    }

}
