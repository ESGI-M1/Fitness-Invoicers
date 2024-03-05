<?php

namespace App\Controller\User;

use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Address;
use App\Form\Address\AddressFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;


class AddressController extends AbstractController
{

    #[Route('/address/add/customer/{id}', name: 'app_user_address_add', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'customer')]
    public function add(EntityManagerInterface $entityManager, Request $request, Customer $customer, int $invoice_id = null): Response
    {

        $deliveryAddress = new Address();
        $form = $this->createForm(AddressFormType::class, $deliveryAddress);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customer->setDeliveryAddress($deliveryAddress);

            $billingAddress = new Address();
            $billingAddress->setPostalCode($deliveryAddress->getPostalCode());
            $billingAddress->setCity($deliveryAddress->getCity());
            $billingAddress->setStreet($deliveryAddress->getStreet());
            $billingAddress->setCountry($deliveryAddress->getCountry());
            $customer->setBillingAddress($billingAddress);

            $entityManager->persist($deliveryAddress);
            $entityManager->persist($billingAddress);
            $entityManager->flush();

            $this->addFlash('success', 'L\'adresse a bien été ajoutée');

            if ($invoice_id) {
                return $this->redirectToRoute('app_user_invoice_step_one', ['id' => $invoice_id]);
            }

            return $this->redirectToRoute('app_user_customer_index');
        }

        return $this->render('action.html.twig', [
            'action' => 'Ajouter une adresse à : ' . $customer->getFullName(),
            'form' => $form,
        ]);
    }

    #[Route('/address/add/company/{id}', name: 'app_user_address_add_company', methods: ['GET', 'POST'])]
    #[isGranted('referent', 'company')]
    public function companyAdd(EntityManagerInterface $entityManager, Request $request, Company $company): Response
    {

        $address = new Address();
        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $company->setAddress($address);
            $entityManager->persist($address);
            $entityManager->flush();
            $this->addFlash('success', 'L\'adresse a bien été ajoutée');

            return $this->redirectToRoute('app_user_company_show', ['id' => $company->getId()]);
        }

        return $this->render('action.html.twig', [
            'action' => 'Ajouter une adresse à l\'entreprise' . $company->getName(),
            'form' => $form,
        ]);
    }

    #[Route('/address/deliveryAddress/edit/{customer}/{invoice_id}/{quote_id}', name: 'app_user_address_deliveryAddress_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'customer')]
    public function deliveryEdit(
        EntityManagerInterface $entityManager,
        Request                $request,
        Customer               $customer,
        int                    $invoice_id = -1,
        int                    $quote_id = -1,
    ): Response
    {

        $address = $customer->getDeliveryAddress();

        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customer->setDeliveryAddress($address);
            $entityManager->flush();

            if ($invoice_id && $invoice_id != -1) {
                return $this->redirectToRoute('app_user_invoice_step_one', ['id' => $invoice_id]);
            }

            if($quote_id && $quote_id != -1) {
                return $this->redirectToRoute('app_user_quote_step_one', ['id' => $quote_id]);
            }

            return $this->redirectToRoute('app_user_customer_index');

        }

        return $this->render('action.html.twig', [
            'action' => 'Modifier l\'adresse de livraison de : ' . $customer->getFullName(),
            'form' => $form,
        ]);
    }

    #[Route('/address/billingAddress/edit/{customer}/{invoice_id}/{quote_id}', name: 'app_user_address_billingAddress_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'customer')]
    public function billingEdit(
        EntityManagerInterface $entityManager,
        Request                $request,
        Customer               $customer,
        int                    $invoice_id = -1,
        int                    $quote_id = -1,
    ): Response
    {

        $address = $customer->getBillingAddress();

        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$customer->setDeliveryAddress($address);
            $customer->setBillingAddress($address);
            $entityManager->flush();

            if ($invoice_id && $invoice_id != -1) {
                return $this->redirectToRoute('app_user_invoice_step_one', ['id' => $invoice_id]);
            }

            if($quote_id && $quote_id != -1) {
                return $this->redirectToRoute('app_user_quote_step_one', ['id' => $quote_id]);
            }

            return $this->redirectToRoute('app_user_customer_index');

        }

        return $this->render('action.html.twig', [
            'action' => 'Modifier l\'adresse de facturation de : ' . $customer->getFullName(),
            'form' => $form,
        ]);
    }

    #[
        Route('/address/company/edit/{company_id}/{address_id}', name: 'app_user_address_company_edit', methods: ['GET', 'POST'])]
    #[isGranted('referent', 'company')]
    public function companyEdit(
        EntityManagerInterface                 $entityManager,
        Request                                $request,
        #[MapEntity(id: 'address_id')] Address $address,
        #[MapEntity(id: 'company_id')] Company $company,
    ): Response
    {

        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $company->setAddress($address);
            $entityManager->flush();
            $this->addFlash('success', 'L\'adresse a bien été modifiée');

            return $this->redirectToRoute('app_user_customer_index');
        }

        return $this->render('action.html.twig', [
            'action' => 'Modifier l\'adresse l\'entreprise : ' . $company->getName(),
            'form' => $form,
        ]);
    }
}
