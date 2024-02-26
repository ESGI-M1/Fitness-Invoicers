<?php

namespace App\Controller\User;

use App\Entity\Customer;
use App\Form\Customer\CustomerSearchType;
use App\Form\Customer\CustomerFormType;
use App\Service\CompanySession;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CustomerController extends AbstractController
{

    #[Route('/customer', name: 'app_user_customer_index', methods: ['GET', 'POST'])]
    public function index(
        EntityManagerInterface $entityManager,
        Request $request,
        PaginatorInterface $paginator,
        CompanySession $companySession
    ): Response {
        $company = $companySession->getCurrentCompany();

        $form = $this->createForm(
            CustomerSearchType::class,
        );

        $form->handleRequest($request);

        $customers = $paginator->paginate(
            $entityManager->getRepository(Customer::class)->getCustomersByFilters($company, $form->getData()),
            $request->query->getInt('page', 1),
            $request->query->getInt('items', 9)
        );

        return $this->render('customers/customer_index.html.twig', [
            'customers' => $customers,
            'form' => $form,
        ]);
    }

    #[Route('/customer/add', name: 'app_user_customer_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager, CompanySession $companySession): Response
    {
        $company = $companySession->getCurrentCompany();
        
        $customer = new Customer();
        $form = $this->createForm(CustomerFormType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customer->setCompany($company);
            $entityManager->persist($customer);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_customer_index');
        }

        return $this->render('action.html.twig', [
            'action' => 'Ajouter un client',
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/customer/edit/{id}', name: 'app_user_customer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, CompanySession $companySession, Customer $customer): Response
    {
        $company = $companySession->getCurrentCompany();
        $form = $this->createForm(CustomerFormType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customer->setCompany($company);
            $entityManager->persist($customer);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_customer_index');
        }

        return $this->render('action.html.twig', [
            'action' => 'Modifier le client ' . $customer->getFullName(),
            'customer' => $customer,
            'form' => $form
        ]);
    }


}

