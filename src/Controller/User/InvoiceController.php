<?php

namespace App\Controller\User;

use App\Entity\Company;
use App\Entity\Deposit;
use App\Entity\Invoice;
use App\Enum\InvoiceStatusEnum;
use App\Service\CompanySession;
use App\Form\Invoice\InvoiceFormType;
use App\Form\Invoice\InvoiceSearchType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Dompdf\Dompdf;
use App\Controller\MainController;


class InvoiceController extends MainController
{

    #[Route('/invoice', name: 'app_user_invoice_index')]
    public function list(
        EntityManagerInterface $entityManager,
        Request                $request,
        PaginatorInterface     $paginator
    ): Response
    {
        $form = $this->createForm(
            InvoiceSearchType::class,
        );

        $form->handleRequest($request);

        $company = $this->companySession->getCurrentCompany();

        $invoices = $paginator->paginate(
            $company->getInvoices(),
            $request->query->getInt('page', 1),
            $request->query->getInt('items', 20)
        );

        return $this->render('invoices/invoice_index.html.twig', [
            'invoices' => $invoices,
            'form' => $form
        ]);
    }

    #[Route('/invoice/show/{id}', name: 'app_user_invoice_show', methods: ['GET'])]
    public function show(Invoice $invoice, Request $request): Response
    {
        $form = $this->createForm(InvoiceFormType::class, $invoice);
        $form->handleRequest($request);

        return $this->render('show.html.twig', [
            'entity' => 'Factures',
            'form' => $form
        ]);
    }

    #[Route('invoice/add', name: 'app_user_invoice_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $invoice = new Invoice();
        $form = $this->createForm(InvoiceFormType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invoice->setStatus(InvoiceStatusEnum::DRAFT);
            $invoice->setCompany($this->companySession->getCurrentCompany());

//            if ($invoice->getQuote()) {
//                $deposit = new Deposit();
//                $deposit->setInvoice($invoice);
//                $deposit->setQuote($invoice->getQuote());
//                $entityManager->persist($deposit);
//            }

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
    #[IsGranted('edit', 'invoice')]
    public function edit(Request $request, #[MapEntity(mapping: ['company_slug' => 'slug'])] Invoice $invoice, EntityManagerInterface $entityManager): Response
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
            'form' => $form
        ]);
    }

    #[Route('invoice/delete/{id}/{token}', name: 'app_user_invoice_delete', methods: ['GET'])]
    #[IsGranted('delete', 'invoice')]
    public function delete(Request $request, Invoice $invoice, EntityManagerInterface $entityManager, string $token): Response
    {
        if ($this->isCsrfTokenValid('delete' . $invoice->getId(), $token)) {
            $entityManager->remove($invoice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_invoice_index');
    }

    #[Route('invoice/generate-pdf/{id}', name: 'app_user_invoice_genere_pdf', methods: ['GET'])]
    public function generatePdf(Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        $dompdf = new Dompdf();

        $company = $invoice->getCompany();

        $html = $this->renderView('invoices/invoice_pdf_2.html.twig', [
            'invoice' => $invoice,
            'company' => $company
        ]);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        return new Response (
            $dompdf->stream('resume', ["Attachment" => false]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        );

    }

}
