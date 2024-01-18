<?php

namespace App\Controller\User;

use App\Entity\Invoice;
use App\Form\Invoice\InvoiceFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController
{
    #[Route('/invoice', name: 'app_user_invoice_index')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $users = $this->getUser();
        $invoices = $entityManager->getRepository(Invoice::class)->findBy([$users]);

        return $this->render('invoices/invoice_index.html.twig', [
            'invoices' => $invoices,
        ]);
    }

    #[Route('/add', name: 'app_user_invoice_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $invoice = new Invoice();
        $form = $this->createForm(InvoiceFormType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($invoice);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('action.html.twig', [
            'action' => 'Ajouter une facture',
            'invoice' => $invoice,
            'form' => $form,
        ]);
    }

    #[Route('invoice/edit/{id}', name: 'app_user_invoice_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InvoiceFormType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('action.html.twig', [
            'action' => 'Modifier la facture n°' . $invoice->getId(),
            'categories' => $invoice,
            'form' => $form,
        ]);
    }

    #[Route('invoice/delete/{id}', name: 'app_user_invoice_delete', methods: ['POST'])]
    public function delete(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$invoice->getId(), $request->request->get('_token'))) {
            $entityManager->remove($invoice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_invoice_index', [], Response::HTTP_SEE_OTHER);
    }
}
