<?php

namespace App\Controller;

use App\Form\Dashboard\DateRangeFormType;
use App\Form\User\CompanyFormType;
use App\Service\CompanySession;
use App\Service\Dashboard;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\User\ProfileFormType;
use Doctrine\ORM\EntityManagerInterface;

class DashboardController extends AbstractController
{

    #[Route('/', name: 'app_index')]
    public function index(Request $request, Dashboard $dashboard, CompanySession $companySession): Response
    {
        $company = $companySession->getCurrentCompany();

        $dateRangeForm = $this->createForm(DateRangeFormType::class);
        $dateRangeForm->handleRequest($request);

        if($dateRangeForm->isSubmitted() && $dateRangeForm->isValid()) {

            $statistics = $dashboard->handleForm($dateRangeForm, $company);

            $dateRangeForm = $this->createForm(DateRangeFormType::class, [
                'startDate' => $statistics['startDate'],
                'endDate' => $statistics['endDate'],
            ]);

            return $this->render('dashboard/index.html.twig', [
                'dateRangeForm' => $dateRangeForm->createView(),
                'statistics' => $statistics
            ]);
        }
        
        return $this->render('dashboard/index.html.twig', [
            'dateRangeForm' => $dateRangeForm->createView(),
            'statistics' => $dashboard->getDefaultStatistics($company),
        ]);
    }

    #[Route('/app_default_dev', name: 'app_default_dev')]
    public function app_default_dev(): Response
    {
        return $this->render('dashboard/index.html.twig');
    }

    #[Route('/design_guide', name: 'design_guide')]
    public function designGuide(): Response
    {
        $this->addFlash('success','Exemple alert !');
        return $this->render('designGuide.html.twig');
    }
}
