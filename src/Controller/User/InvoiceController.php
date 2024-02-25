<?php

namespace App\Controller\User;

use App\Entity\Product;
use App\Entity\Invoice;
use App\Entity\Item;
use App\Enum\InvoiceStatusEnum;
use App\Service\CompanySession;
use App\Service\Mail;
use App\Form\Invoice\InvoiceFormType;
use App\Form\Invoice\InvoiceSearchType;
use App\Form\Invoice\InvoiceCustomerFormType;
use App\Form\Invoice\InvoiceCategoryFormType;
use App\Form\Invoice\InvoiceProductFormType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Dompdf\Dompdf;


class InvoiceController extends AbstractController
{

    #[Route('/invoice', name: 'app_user_invoice_index')]
    public function list(
        EntityManagerInterface $entityManager,
        Request                $request,
        PaginatorInterface     $paginator,
        CompanySession         $companySession,
    ): Response
    {
        $form = $this->createForm(
            InvoiceSearchType::class,
        );

        $form->handleRequest($request);

        $company = $companySession->getCurrentCompany();

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
    public function add(Request $request, EntityManagerInterface $entityManager, CompanySession $companySession, Mail $mail): Response
    {
        $invoice = new Invoice();
        $form = $this->createForm(InvoiceFormType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invoice->setStatus(InvoiceStatusEnum::DRAFT);
            $invoice->setCompany($companySession->getCurrentCompany());


            $mail->sendInvoice($invoice->getCustomer(), $invoice);

            /*
            if ($invoice->getQuote()) {
                $deposit = new Deposit();
                $deposit->setInvoice($invoice);
                $deposit->setQuote($invoice->getQuote());
                $entityManager->persist($deposit);
            }
            */

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

    #[Route('invoice/step_one/{id}', name: 'app_user_invoice_step_one', methods: ['GET', 'POST'])]
    public function stepOne(Request $request, EntityManagerInterface $entityManager, Invoice $invoice, CompanySession $companySession): Response
    {
        $form = $this->createForm(InvoiceCustomerFormType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$invoice->setStatus(InvoiceStatusEnum::DRAFT);
            //$invoice->setCompany($companySession->getCurrentCompany());

            /*
            if ($invoice->getQuote()) {
                $deposit = new Deposit();
                $deposit->setInvoice($invoice);
                $deposit->setQuote($invoice->getQuote());
                $entityManager->persist($deposit);
            }
            */

            $entityManager->persist($invoice);
            $entityManager->flush();

            return $this->render('invoices/invoice_step_one.html.twig', [
                'form' => $form,
                'invoice' => $invoice,
                'customer' => $invoice->getCustomer()
            ]);

            //return $this->redirectToRoute('app_user_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoices/invoice_step_one.html.twig', [
            'form' => $form,
            'invoice' => $invoice,
            'customer' => $invoice->getCustomer()
        ]);
    }

    #[Route('invoice/step_two/{id}', name: 'app_user_invoice_step_two', methods: ['GET', 'POST'])]
    public function stepTwo(Request $request, EntityManagerInterface $entityManager, Invoice $invoice, CompanySession $companySession): Response
    {
        $categoryForm = $this->createForm(InvoiceCategoryFormType::class);
        $categoryForm->handleRequest($request);

        $productWithoutCategory = $entityManager->getRepository(Product::class)->getWithoutCategory($companySession->getCurrentCompany());
        $productWithoutCategoryForm = $this->createForm(InvoiceProductFormType::class, null, [
            'products' => $productWithoutCategory
        ]);

        $productWithoutCategoryForm->handleRequest($request);

        if ($productWithoutCategoryForm->isSubmitted() && $productWithoutCategoryForm->isValid()) {
            $products = $productWithoutCategoryForm->get('products')->getData();
            foreach ($products as $product) {
                $item = new Item();
                $item->setProduct($product);
                $item->setQuantity(1);

                $entityManager->persist($item);

                $invoice->addItem($item);
                $entityManager->flush();
            }
        } elseif (isset($request->request->all()['invoice_product_form'])) {

            // Traitement du formulaire des produits venant d'une catégorie

            $form = $request->request->all()['invoice_product_form'];
            $products = $form['products'];
            foreach ($products as $product) {
                $product = $entityManager->getRepository(Product::class)->find($product);
                $item = new Item();
                $item->setProduct($product);
                $item->setQuantity(1);
                $entityManager->persist($item);

                $invoice->addItem($item);
                $entityManager->flush();

            }
        }


        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {

            $categories = $categoryForm->get('categories')->getData();

            $formCategories = [];
            foreach ($categories as $category) {
                $formCategories[] = $this->createForm(InvoiceProductFormType::class, null, [
                    'products' => $category->getProducts()
                ])->createView();
            }

            return $this->render('invoices/invoice_step_two.html.twig', [
                'formCategories' => $formCategories,
                'categoryForm' => $categoryForm,
                'productWithoutCategoryForm' => $productWithoutCategoryForm,
                'invoice' => $invoice,
            ]);

            //return $this->redirectToRoute('app_user_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('invoices/invoice_step_two.html.twig', [
            'formCategories' => [],
            'categoryForm' => $categoryForm,
            'productWithoutCategoryForm' => $productWithoutCategoryForm,
            'invoice' => $invoice
        ]);
    }

    #[Route('invoice/remove-item/{id_invoice}/{id_item}', name: 'app_user_invoice_remove_item', methods: ['GET'])]
    public function removeItem(
        EntityManagerInterface                 $entityManager,
        #[MapEntity(id: 'id_invoice')] Invoice $invoice,
        #[MapEntity(id: 'id_item')] Item       $item
    ): Response
    {
        $invoice->removeItem($item);
        $entityManager->remove($item);
        $entityManager->flush();

        return $this->redirectToRoute('app_user_invoice_step_two', ['id' => $invoice->getId()]);
    }

    #[Route('invoice/edit/{id}', name: 'app_user_invoice_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'invoice')]
    public function edit(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InvoiceFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_invoice_index');
        }

        return $this->render('action.html.twig', [
            'action' => 'Modifier la facture n°' . $invoice->getId(),
            'categories' => $invoice,
            'form' => $form
        ]);
    }

    #[Route('invoice/delete/{id}/{token}', name: 'app_user_invoice_delete', methods: ['GET'])]
    #[IsGranted('delete', 'invoice')]
    public function delete(Invoice $invoice, EntityManagerInterface $entityManager, string $token): Response
    {
        if ($this->isCsrfTokenValid('delete' . $invoice->getId(), $token)) {
            $entityManager->remove($invoice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_invoice_index');
    }

    #[Route('invoice/generate-pdf/{id}', name: 'app_user_invoice_genere_pdf', methods: ['GET'])]
    public function generatePdf(Invoice $invoice): Response
    {

        $html = $this->renderView('invoices/invoice_pdf.html.twig', [
            'invoice' => $invoice
        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf'
        ]);
    }

}
