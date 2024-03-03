<?php

namespace App\Controller\User;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Invoice;
use App\Entity\Item;
use App\Entity\Mail;
use App\Entity\Payment;
use App\Enum\InvoiceStatusEnum;
use App\Enum\PaymentMethodEnum;
use App\Enum\PaymentStatusEnum;
use App\Service\CompanySession;
use App\Service\Mailer;
use App\Form\Invoice\InvoiceFormType;
use App\Form\Invoice\InvoiceSearchType;
use App\Form\Invoice\InvoiceCustomerFormType;
use App\Form\Invoice\InvoiceCategoryFormType;
use App\Form\Invoice\InvoiceStatusFormType;
use App\Form\Invoice\InvoiceDueDateFormType;
use App\Form\Item\ItemStepTwoFormType;
use App\Form\Mail\MailFormType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Dompdf\Dompdf;

class InvoiceController extends AbstractController
{
    #[Route('/invoice', name : 'app_user_invoice_index')]
    public function list(
        EntityManagerInterface $entityManager,
        Request $request,
        PaginatorInterface $paginator,
        CompanySession $companySession,
    ): Response {
        $form = $this->createForm(
            InvoiceSearchType::class,
        );

        $form->handleRequest($request);

        $company = $companySession->getCurrentCompany();

        $invoices = $paginator->paginate(
            $entityManager->getRepository(Invoice::class)
                ->getInvoicesByFilters($company, $form->getData()),
            $request->query->getInt('page', 1),
            $request->query->getInt('items', 20)
        );

        return $this->render('invoices/invoice_index.html.twig', [
            'invoices' => $invoices,
            'form' => $form,
        ]);
    }

    #[Route('/invoice/show/{id}', name : 'app_user_invoice_show', methods : ['GET'])]
    #[IsGranted('see', 'invoice')]
    public function show(Invoice $invoice): Response
    {
        return $this->render('invoices/invoice_show.html.twig', [
            'invoice' => $invoice,
        ]);
    }

    #[Route('invoice/add', name : 'app_user_invoice_add', methods : ['GET', 'POST'])]
    #[IsGranted('add', 'invoice')]
    public function add(Request $request, EntityManagerInterface $entityManager, CompanySession $companySession, Mail $mail): Response
    {
        $invoice = new Invoice();
        $form = $this->createForm(InvoiceFormType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invoice->setStatus(InvoiceStatusEnum::DRAFT);
            $invoice->setCompany($companySession->getCurrentCompany());

            $entityManager->persist($invoice);
            $entityManager->flush();
            $this->addFlash('success', 'La facture a été ajoutée');

            return $this->redirectToRoute('app_user_invoice_index');
        }

        return $this->render('action.html.twig', [
            'action' => 'Ajouter une facture',
            'invoice' => $invoice,
            'form' => $form,
        ]);
    }

    #[Route('invoice/step_one/{id}', name : 'app_user_invoice_step_one', defaults : ['id' => null], methods : ['GET', 'POST'])]
    public function stepOne(Request $request, EntityManagerInterface $entityManager, Invoice $invoice = null, CompanySession $companySession): Response
    {
        if (!$this->isGranted('add', $invoice) && !$this->isGranted('edit', $invoice)) {
            throw $this->createAccessDeniedException();
        }

        if (!$invoice) {
            $company = $companySession->getCurrentCompany();
            $invoice = new Invoice();
            $invoice->setCompany($company);
            $invoice->setStatus(InvoiceStatusEnum::DRAFT);
            $invoice->setDetails($company->getInvoiceDetails());

            $entityManager->persist($invoice);
            $entityManager->flush();
        }

        if ($invoice->getStatus() != InvoiceStatusEnum::DRAFT) {
            $this->addFlash('danger', 'La facture ' . $invoice->getId() . ' ne peut être modifiée');

            return $this->redirectToRoute('app_user_invoice_index');
        }

        $form = $this->createForm(InvoiceCustomerFormType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($invoice);
            $entityManager->flush();
        }

        return $this->render('layout/step_one.html.twig', [
            'form' => $form,
            'name' => [
                'title' => 'Factures',
                'entity' => 'invoice',
            ],
            'value' => $invoice,
            'customer' => $invoice->getCustomer(),
        ]);
    }

    #[Route('invoice/step_two/{id}', name : 'app_user_invoice_step_two', methods : ['GET', 'POST'])]
    #[IsGranted('edit', 'invoice')]
    public function stepTwo(Request $request, EntityManagerInterface $entityManager, Invoice $invoice, CompanySession $companySession): Response
    {
        if ($invoice->getStatus() != InvoiceStatusEnum::DRAFT) {
            $this->addFlash('danger', 'La facture ' . $invoice->getId() . ' ne peut être modifiée');

            return $this->redirectToRoute('app_user_invoice_index');
        }

        if (!$invoice->isValidStepOne()) {
            $this->addFlash('danger', 'La facture ' . $invoice->getId() . ' n\'a pas de client');

            return $this->redirectToRoute('app_user_invoice_step_one', ['id' => $invoice->getId()]);
        }

        $categoryForm = $this->createForm(InvoiceCategoryFormType::class);
        $categoryForm->handleRequest($request);

        $productWithoutCategory = $entityManager->getRepository(Product::class)->getWithoutCategory($companySession->getCurrentCompany());

        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {
            $categories = $categoryForm->get('categories')->getData();

            $categoryIds = [];
            // vérifier l'appartenance de la catégorie à la société
            foreach ($categories as $category) {
                $categoryIds[] = $category->getId();
            }
            $request->getSession()->set('categoryIds-' . $invoice->getId(), $categoryIds);
        }

        $productFromCategory = [];
        if ($productFromCategoryIds = $request->getSession()->get('categoryIds-' . $invoice->getId())) {
            foreach ($productFromCategoryIds as $categoryId) {
                $productFromCategory[] = $entityManager->getRepository(Category::class)->find($categoryId);
            }
        }

        if (isset($request->request->all()['item_step_two_form'])) {
            $data = $request->request->all()['item_step_two_form'];
            if ($data['id']) {
                $item = $entityManager->getRepository(Item::class)->find($data['id']);
                $item->setQuantity((int)$data['quantity']);
                $item->setTaxes((float)$data['taxes']);
                $item->setDiscountAmountOnTotal((float)$data['discountAmountOnTotal']);
                $item->setDiscountAmountOnItem((float)$data['discountAmountOnItem']);
                
                $entityManager->flush();
            }
        }

        $items = $invoice->getItems();
        $invoiceItems = [];
        foreach ($items as $item) {
            $invoiceItems[$item->getId()]['item'] = $item;
            $invoiceItems[$item->getId()]['form'] = $this->createForm(ItemStepTwoFormType::class, $item)->createView();
        }

        return $this->render('layout/step_two.html.twig', [
            'productFromCategory' => $productFromCategory,
            'productWithoutCategory' => $productWithoutCategory,
            'categoryForm' => $categoryForm,
            'items' => $invoiceItems,
            'name' => [
                'title' => 'Factures',
                'entity' => 'invoice',
            ],
            'value' => $invoice,
        ]);
    }

    #[Route('invoice/step_three/{id}', name : 'app_user_invoice_step_three', methods : ['GET', 'POST'])]
    #[IsGranted('edit', 'invoice')]
    public function stepThree(Request $request, EntityManagerInterface $entityManager, Invoice $invoice): Response
    {
        if ($invoice->getStatus() != InvoiceStatusEnum::DRAFT) {
            $this->addFlash('danger', 'La facture ' . $invoice->getId() . ' ne peut être modifiée');

            return $this->redirectToRoute('app_user_invoice_index');
        }

        if (!$invoice->isValidStepTwo()) {
            $this->addFlash('danger', 'La facture ' . $invoice->getId() . ' n\'a pas de produit');

            return $this->redirectToRoute('app_user_invoice_step_two', ['id' => $invoice->getId()]);
        }

        $formStatus = $this->createForm(InvoiceStatusFormType::class, $invoice);
        $formStatus->handleRequest($request);

        if ($formStatus->isSubmitted() && $formStatus->isValid()) {
            if ($invoice->getStatus() == InvoiceStatusEnum::VALIDATED || $invoice->getStatus() == InvoiceStatusEnum::SENT) {
                $invoice->setDate(new \DateTimeImmutable());

                $payment = new Payment();
                $payment->setDate(new \DateTimeImmutable());
                $payment->setAmount($invoice->getTotalAmount());
                $payment->setInvoice($invoice);
                $payment->setStatus(PaymentStatusEnum::PENDING);
                $payment->setMethod($formStatus->get('paymentMethod')->getData());

                $entityManager->persist($payment);
            }

            $entityManager->flush();
            $this->addFlash('success', 'La facture a été modifiée');
        }

        $form = $this->createForm(InvoiceDueDateFormType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $invoice->getStatus() == InvoiceStatusEnum::DRAFT) {
            $entityManager->flush();
            $this->addFlash('success', 'La facture a été modifiée');
        }

        return $this->render('layout/step_three.html.twig', [
            'formStatus' => $formStatus,
            'form' => $form,
            'name' => [
                'title' => 'Factures',
                'entity' => 'invoice',
            ],
            'value' => $invoice,
        ]);
    }

    #[Route('invoice/step_four/{id}', name : 'app_user_invoice_step_four', methods : ['GET', 'POST'])]
    #[IsGranted('mail', 'invoice')]
    public function stepFour(Request $request, EntityManagerInterface $entityManager, Invoice $invoice, Mailer $mailer): Response
    {
        if (!$invoice->isValidStepThree()) {
            $this->addFlash('danger', 'La facture n`\'a pas de date d\'échéance et de statut');

            return $this->redirectToRoute('app_user_invoice_step_three', ['id' => $invoice->getId()]);
        }

        $mail = new Mail();
        $user = $this->getUser();
        $mail->setObject('Votre facture n°' . $invoice->getId() . ' est disponible : ' . $invoice->getCompany()->getName());
        $mail->setContent($user->getInvoiceMailContent());
        $mail->setSignature($user->getMailSignature());

        $form = $this->createForm(MailFormType::class, $mail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getClickedButton() && 'send' === $form->getClickedButton()->getName()) {
                $mailer->sendInvoice($invoice, $mail);
                $invoice->setStatus(InvoiceStatusEnum::SENT);
                $entityManager->flush();

                $this->addFlash('success', 'La facture a été envoyée');
            }
        }

        return $this->render('layout/step_four.html.twig', [
            'entity' => $invoice,
            'title' => 'Factures',
            'form' => $form,
            'mail' => $mail,
        ]);
    }


    #[Route('invoice/add-item/{id_invoice}/{id_product}', name : 'app_user_invoice_add_item', methods : ['GET'])]
    #[IsGranted('edit', 'invoice')]
    public function addItem(
        EntityManagerInterface $entityManager,
        #[MapEntity(id : 'id_invoice')] Invoice $invoice,
        #[MapEntity(id : 'id_product')] Product $product
    ): Response {
        $item = $entityManager->getRepository(Item::class)->findOneBy(['product' => $product, 'invoices' => $invoice]);

        if ($item) {
            $item->setQuantity($item->getQuantity() + 1);

            $this->addFlash('success', 'La quantité du produit ' . $product->getName() . ' a été augmentée');
        } else {
            $item = new Item();
            $item->setProduct($product);
            $item->setQuantity(1);
            $item->setPrice($product->getPrice());

            $invoice->addItem($item);

            $this->addFlash('success', 'Le produit ' . $product->getName() . ' a été ajouté');
        }

        $entityManager->persist($item);
        $entityManager->flush();

        return $this->redirectToRoute('app_user_invoice_step_two', ['id' => $invoice->getId()]);
    }

    #[Route('invoice/remove-item/{id_invoice}/{id_item}', name : 'app_user_invoice_remove_item', methods : ['GET'])]
    #[IsGranted('edit', 'invoice')]
    public function removeItem(
        EntityManagerInterface $entityManager,
        #[MapEntity(id : 'id_invoice')] Invoice $invoice,
        #[MapEntity(id : 'id_item')] Item $item
    ): Response {
        $invoice->removeItem($item);
        $entityManager->remove($item);
        $entityManager->flush();

        return $this->redirectToRoute('app_user_invoice_step_two', ['id' => $invoice->getId()]);
    }

    #[Route('invoice/increase-quantity-item/{id_item}', name : 'app_user_invoice_increase_quantity_item', methods : ['GET'])]
    #[IsGranted('edit', 'item')]
    public function increaseQuantityItem(
        EntityManagerInterface $entityManager,
        #[MapEntity(id : 'id_item')] Item $item
    ): Response {
        $item->setQuantity($item->getQuantity() + 1);
        $entityManager->persist($item);
        $entityManager->flush();

        $invoice = $item->getInvoices();

        return $this->redirectToRoute('app_user_invoice_step_two', ['id' => $invoice->getId()]);
    }

    #[Route('invoice/decrease-quantity-item/{id_item}', name : 'app_user_invoice_decrease_quantity_item', methods : ['GET'])]
    #[IsGranted('edit', 'item')]
    public function decreaseQuantityItem(
        EntityManagerInterface $entityManager,
        #[MapEntity(id : 'id_item')] Item $item
    ): Response {
        if ($item->getQuantity() > 1) {
            $item->setQuantity($item->getQuantity() - 1);
            $entityManager->persist($item);
            $entityManager->flush();
        } else {
            $this->addFlash('danger', 'La quantité minimale est 1');
        }

        $invoice = $item->getInvoices();

        return $this->redirectToRoute('app_user_invoice_step_two', ['id' => $invoice->getId()]);
    }

    #[Route('invoice/edit/{id}', name : 'app_user_invoice_edit', methods : ['GET', 'POST'])]
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
            'form' => $form,
        ]);
    }

    #[Route('invoice/delete/{id}/{token}', name : 'app_user_invoice_delete', methods : ['GET'])]
    #[IsGranted('delete', 'invoice')]
    public function delete(Invoice $invoice, EntityManagerInterface $entityManager, string $token): Response
    {
        if ($invoice->getStatus() === InvoiceStatusEnum::DRAFT && $this->isCsrfTokenValid('delete' . $invoice->getId(), $token)) {
            $items = $invoice->getItems();

            foreach ($items as $item) {
                $entityManager->remove($item);
            }

            $entityManager->remove($invoice);
            $entityManager->flush();

            $this->addFlash('success', 'La facture n°' . $invoice->getId() . ' a été supprimée');
        }

        return $this->redirectToRoute('app_user_invoice_index');
    }

    #[Route('invoice/pdf/{id}', name : 'app_user_invoice_pdf', methods : ['GET'])]
    #[IsGranted('see', 'invoice')]
    public function generatePdf(Invoice $invoice): Response
    {
        if (!$invoice->isValid()) {
            $this->addFlash('danger', 'La facture n°' . $invoice->getId() . ' n\'est pas valide');

            return $this->redirectToRoute('app_user_invoice_index');
        }

        $dompdf = new Dompdf();

        $html = $this->renderView('invoices/invoice_pdf.html.twig', [
            'invoice' => $invoice,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdf = $dompdf->output();

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }

}
