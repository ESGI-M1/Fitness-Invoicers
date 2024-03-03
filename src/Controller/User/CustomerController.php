<?php

namespace App\Controller\User;

use App\Entity\Customer;
use App\Enum\CustomerStatutEnum;
use App\Enum\InvoiceStatusEnum;
use App\Enum\QuoteStatusEnum;
use App\Form\Customer\CustomerSearchType;
use App\Form\Customer\CustomerFormType;
use App\Service\CompanySession;
use App\Service\Mailer;
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
    #[IsGranted('add', 'customer')]
    public function add(Request $request, EntityManagerInterface $entityManager, CompanySession $companySession): Response
    {
        $company = $companySession->getCurrentCompany();
        
        $customer = new Customer();
        $form = $this->createForm(CustomerFormType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customer->setCompany($company);
            $customer->setStatus(CustomerStatutEnum::VALIDATED);
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
    #[IsGranted('edit', 'customer')]
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

    #[Route('/customer/interaction/{id}', name: 'app_user_customer_interaction', methods: ['GET'])]
    #[IsGranted('see', 'customer')]
    public function interaction(EntityManagerInterface $entityManager, Customer $customer): Response
    {

        $interactions = [];
        $invoices = $customer->getInvoices();

        foreach ($invoices as $invoice) {
            if($invoice->getStatus() === InvoiceStatusEnum::VALIDATED  || $invoice->getStatus() === InvoiceStatusEnum::SENT) {
                $interactions
                ['invoice']
                [$invoice->getDate() ? $invoice->getDate()->format('Y-m-d') : null]
                [$invoice->getId()] =
                    $invoice;
            }
        }

        $quotes = $customer->getQuotes();
        foreach ($quotes as $quote) {
            if($quote->getStatus() === QuoteStatusEnum::ACCEPTED || $quote->getStatus() === QuoteStatusEnum::SENT) {
                $interactions['quote'][$quote->getDate()->format('Y-m-d')][$quote->getId()] = $quote;
            }
        }

        $mails = $customer->getMails();
        foreach ($mails as $mail) {
            $interactions['mail'][$mail->getDate()->format('Y-m-d')][$mail->getId()] = $mail;
        }

        return $this->render('customers/customer_interaction.html.twig', [
            'customer' => $customer,
            'interactions' => $interactions
        ]);
    }

    #[Route('/customer/delete/{id}/{token}', name: 'app_user_customer_delete', methods: ['GET'])]
    #[IsGranted('delete', 'customer')]
    public function delete(EntityManagerInterface $entityManager, CompanySession $companySession, Customer $customer, Mailer $mailer, string $token): Response
    {
        if ($this->isCsrfTokenValid('delete' . $customer->getId(), $token)) {

            if($customer->getStatus() === CustomerStatutEnum::DELETED) {
                $this->addFlash('danger', 'Les données du client ont déjà été supprimées');
                return $this->redirectToRoute('app_user_customer_index');
            }

            $company = $companySession->getCurrentCompany();

            $mailer->sendConfirmationDeletedCustomer($customer, $company);
            $customer->setStatus(CustomerStatutEnum::DELETED);
            $customer->setFirstName('DELETED');
            $customer->setLastName('DELETED');
            $customer->setEmail('DELETED');

            $deliveryAddress = $customer->getDeliveryAddress();
            $billingAddress = $customer->getBillingAddress();
            $deliveryAddress ? $entityManager->remove($deliveryAddress) : null;
            $billingAddress ? $entityManager->remove($billingAddress) : null;

            $entityManager->persist($deliveryAddress);
            $entityManager->persist($billingAddress);
            $entityManager->flush();
            $this->addFlash('success', 'Les données du client ont été supprimées');
        }

        return $this->redirectToRoute('app_user_customer_index');
    }
}

