<?php

namespace App\Controller\User;

use App\Entity\Deposit;
use App\Form\Deposit\DepositFormType;
use App\Form\Deposit\DepositSearchType;
use App\Service\CompanySession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\MainController;

class DepositController extends MainController
{

    #[Route('/deposit', name: 'app_user_deposit_index')]
    public function list(
        EntityManagerInterface $entityManager,
        Request $request,
        PaginatorInterface $paginator,
        CompanySession $companySession
    ): Response {
        $form = $this->createForm(
            DepositSearchType::class,
        );

        $form->handleRequest($request);

        $company = $companySession->getCurrentCompany();

        $allDep = $entityManager->getRepository(Deposit::class)->getDepositFromCompany($company,$form->getData());
        $deposits = $paginator->paginate(
            $allDep,
            $request->query->getInt('page', 1),
            $request->query->getInt('items', 20)
        );

        return $this->render('deposit/deposit_index.html.twig', [
            'deposits' => $deposits,
            'form' => $form
        ]);
    }
}
