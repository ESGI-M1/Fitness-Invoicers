<?php

namespace App\Controller\User;

use App\Entity\Customer;
use App\Entity\Address;
use App\Form\Address\AddressFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Doctrine\ORM\EntityManagerInterface;


class AddressController extends AbstractController
{
    #[Route('/address', name: 'app_address')]
    public function index(): Response
    {
        return $this->render('address/index.html.twig', [
            'controller_name' => 'AddressController',
        ]);
    }

    #[Route('/address/add/{id}', name: 'app_user_address_add', methods: ['GET', 'POST'])]
    public function add(EntityManagerInterface $entityManager, Request $request, Customer $customer, int $invoice_id = null): Response
    {

        $address = new Address();
        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customer->setDeliveryAddress($address);
            $customer->setBillingAddress($address);
            $entityManager->persist($address);
            $entityManager->flush();

            if ($invoice_id) {
                return $this->redirectToRoute('app_user_invoice_step_one', ['id' => $invoice_id]);
            }

            return $this->redirectToRoute('app_user_customer_index');
        }

        return $this->render('action.html.twig', [
            'action' => 'Ajouter une adresse à ' . $customer->getFullName(),
            'form' => $form,
        ]);
    }

    #[Route('/address/deliveryAddress/edit/{customer_id}/{address_id}/{invoice_id}', name: 'app_user_address_deliveryAddress_edit', methods: ['GET', 'POST'])]
    public function deliveryEdit(
        EntityManagerInterface                   $entityManager,
        Request                                  $request,
        #[MapEntity(id: 'address_id')] Address   $address,
        #[MapEntity(id: 'customer_id')] Customer $customer,
        int                                      $invoice_id = null
    ): Response
    {

        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customer->setDeliveryAddress($address);
            $customer->setBillingAddress($address);
            $entityManager->persist($address);
            $entityManager->flush();

            if ($invoice_id) {
                return $this->redirectToRoute('app_user_invoice_step_one', ['id' => $invoice_id]);
            }

            return $this->redirectToRoute('app_user_customer_index');

        }

        return $this->render('action.html.twig', [
            'action' => 'Modifier l\'adresse de livraison de : ' . $customer->getFullName(),
            'form' => $form,
        ]);
    }

    #[Route('/address/billingAddress/edit/{customer_id}/{address_id}', name: 'app_user_address_billingAddress_edit', methods: ['GET', 'POST'])]
    public function edit(
        EntityManagerInterface                   $entityManager,
        Request                                  $request,
        #[MapEntity(id: 'address_id')] Address   $address,
        #[MapEntity(id: 'customer_id')] Customer $customer,
        int                                      $invoice_id = null
    ): Response
    {

        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customer->setDeliveryAddress($address);
            $customer->setBillingAddress($address);
            $entityManager->persist($address);
            $entityManager->flush();

            if ($invoice_id) {
                return $this->redirectToRoute('app_user_invoice_step_one', ['id' => $invoice_id]);
            }

            return $this->redirectToRoute('app_user_customer_index');

        }

        return $this->render('action.html.twig', [
            'action' => 'Modifier l\'adresse de facturation de : ' . $customer->getFullName(),
            'form' => $form,
        ]);
    }
}
