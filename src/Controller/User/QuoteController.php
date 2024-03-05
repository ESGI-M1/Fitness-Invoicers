<?php

namespace App\Controller\User;

use App\Entity\Deposit;
use App\Entity\Quote;
use App\Entity\Invoice;
use App\Entity\Product;
use App\Entity\Item;
use App\Entity\Mail;
use App\Entity\Payment;
use App\Entity\Category;
use App\Enum\InvoiceStatusEnum;
use App\Enum\PaymentStatusEnum;
use App\Enum\QuoteStatusEnum;
use App\Form\Quote\QuoteCustomerFormType;
use App\Form\Quote\QuoteFormType;
use App\Form\Quote\QuoteSearchType;
use App\Form\Quote\QuoteCategoryFormType;
use App\Form\Quote\QuoteStatusFormType;
use App\Form\Quote\QuoteExpirationDateFormType;
use App\Form\Quote\QuoteConvertFormType;
use App\Form\Mail\MailFormType;
use App\Form\Item\ItemStepTwoFormType;
use App\Service\Mailer;
use App\Service\CompanySession;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

use Dompdf\Dompdf;

class QuoteController extends AbstractController
{
    #[Route('/quote', name: 'app_user_quote_index')]
    public function list(
        EntityManagerInterface $entityManager,
        Request                $request,
        PaginatorInterface     $paginator,
        CompanySession         $companySession
    ): Response
    {
        $form = $this->createForm(
            QuoteSearchType::class,
        );

        $form->handleRequest($request);

        $company = $companySession->getCurrentCompany();

        $quotes = $paginator->paginate(
            $entityManager->getRepository(Quote::class)->getQuotesByFilters($company, $form->getData()),
            $request->query->getInt('page', 1),
            $request->query->getInt('items', 20)
        );

        return $this->render('quotes/quote_index.html.twig', [
            'quotes' => $quotes,
            'form' => $form
        ]);
    }

    #[Route('/quote/show/{id}', name: 'app_user_quote_show', methods: ['GET'])]
    #[IsGranted('see', 'quote')]
    public function show(Quote $quote): Response
    {
        return $this->render('quotes/quote_show.html.twig', [
            'quote' => $quote,
        ]);
    }

    #[Route('quote/step_one/{id}', name: 'app_user_quote_step_one', defaults: ['id' => null], methods: ['GET', 'POST'])]
    public function stepOne(Request $request, EntityManagerInterface $entityManager, Quote $quote = null, CompanySession $companySession): Response
    {

        if (!$this->isGranted('add', $quote) && !$this->isGranted('edit', $quote)) {
            throw $this->createAccessDeniedException();
        }

        if (!$quote) {
            $company = $companySession->getCurrentCompany();
            $quote = new quote();
            $quote->setCompany($company);
            $quote->setStatus(QuoteStatusEnum::DRAFT);
            $quote->setDetails($company->getInvoiceDetails());

            $entityManager->persist($quote);
            $entityManager->flush();
        }

        if ($quote->getStatus() != QuoteStatusEnum::DRAFT) {
            $this->addFlash('danger', 'Le devis ' . $quote->getId() . ' ne peut être modifiée');
            return $this->redirectToRoute('app_user_quote_index');
        }

        $form = $this->createForm(QuoteCustomerFormType::class, $quote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash('success', 'Le client a bien été sélectionné');

            $entityManager->persist($quote);
            $entityManager->flush();
        }

        return $this->render('layout/step_one.html.twig', [
            'form' => $form,
            'name' => [
               'title' => 'Devis',
               'entity' => 'quote'
            ],
            'value' => $quote,
            'customer' => $quote->getCustomer()
        ]);
    }

    #[Route('quote/step_two/{id}', name: 'app_user_quote_step_two', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'quote')]
    public function stepTwo(Request $request, EntityManagerInterface $entityManager, Quote $quote, CompanySession $companySession): Response
    {
        if ($quote->getStatus() != QuoteStatusEnum::DRAFT) {
            $this->addFlash('danger', 'Le devis ' . $quote->getId() . ' ne peut être modifiée');
            return $this->redirectToRoute('app_user_quote_index');
        }

        if (!$quote->isValidStepOne()) {
            $this->addFlash('danger', 'Le devis ' . $quote->getId() . ' n\'a pas de client');
            return $this->redirectToRoute('app_user_quote_step_one', ['id' => $quote->getId()]);
        }

        $categoryForm = $this->createForm(QuoteCategoryFormType::class);
        $categoryForm->handleRequest($request);

        $productWithoutCategory = $entityManager->getRepository(Product::class)->getWithoutCategory($companySession->getCurrentCompany());

        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {

            $categories = $categoryForm->get('categories')->getData();

            $categoryIds = [];
            // vérifier l'appartenance de la catégorie à la société
            foreach ($categories as $category) {
                $categoryIds[] = $category->getId();
            }
            $request->getSession()->set('categoryIds-' . $quote->getId(), $categoryIds);

        }

        $productFromCategory = [];
        if ($productFromCategoryIds = $request->getSession()->get('categoryIds-' . $quote->getId())) {
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
                $item->setDiscountAmountOnItem((float)$data['discountAmountOnItem']);
                $item->setDiscountAmountOnTotal((float)$data['discountAmountOnTotal']);

                $entityManager->flush();
            }
        }

        $items = $quote->getItems();
        $quoteItems = [];
        foreach ($items as $item) {
            $quoteItems[$item->getId()]['item'] = $item;
            $quoteItems[$item->getId()]['form'] = $this->createForm(ItemStepTwoFormType::class, $item)->createView();
        }

        return $this->render('layout/step_two.html.twig', [
            'productFromCategory' => $productFromCategory,
            'productWithoutCategory' => $productWithoutCategory,
            'categoryForm' => $categoryForm,
            'items' => $quoteItems,
            'name' => [
                'title' => 'Devis',
                'entity' => 'quote'
            ],
            'value' => $quote,
        ]);
    }

    #[Route('quote/step_three/{id}', name: 'app_user_quote_step_three', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'quote')]
    public function stepThree(Request $request, EntityManagerInterface $entityManager, quote $quote): Response
    {
        if ($quote->getStatus() != QuoteStatusEnum::DRAFT) {
            $this->addFlash('danger', 'Le devis ' . $quote->getId() . ' ne peut être modifiée');
            return $this->redirectToRoute('app_user_quote_index');
        }

        if (!$quote->isValidStepTwo()) {
            $this->addFlash('danger', 'Le devis ' . $quote->getId() . ' n\'a pas de produit');
            return $this->redirectToRoute('app_user_quote_step_two', ['id' => $quote->getId()]);
        }

        if(!$quote->isValid()){
            foreach($quote->getIsNotValidErrors() as $error){
                $this->addFlash('danger', $error);
            }
        }

        $formStatus = $this->createForm(QuoteStatusFormType::class, $quote);
        $formStatus->handleRequest($request);

        if ($formStatus->isSubmitted() && $formStatus->isValid()) {

            if ($quote->getStatus() == QuoteStatusEnum::ACCEPTED) {
                $quote->setDate(new \DateTimeImmutable());
            }

            $entityManager->flush();
            $this->addFlash('success', 'Le devis a bien été modifié.');
        }

        $formExpirationDate = $this->createForm(QuoteExpirationDateFormType::class, $quote);
        $formExpirationDate->handleRequest($request);

        if ($formExpirationDate->isSubmitted() && $formExpirationDate->isValid() && $quote->getStatus() == QuoteStatusEnum::DRAFT) {
            $entityManager->flush();
            $this->addFlash('success', 'La date d\'expiration a bien été modifiée.');
        }

        return $this->render('layout/step_three.html.twig', [
            'name' => [
                'title' => 'Devis',
                'entity' => 'quote'
            ],
            'value' => $quote,
            'formStatus' => $formStatus,
            'formExpirationDate' => $formExpirationDate
        ]);
    }

    #[Route('quote/step_four/{id}', name: 'app_user_quote_step_four', methods: ['GET', 'POST'])]
    #[IsGranted('mail', 'quote')]
    public function stepFour(Request $request, EntityManagerInterface $entityManager, Quote $quote, Mailer $mailer): Response
    {
        if (!$quote->isValidStepThree()) {
            $this->addFlash('danger', 'La devis n`\'a pas de date d\'expiration et de statut');
            return $this->redirectToRoute('app_user_quote_step_three', ['id' => $quote->getId()]);
        }

        $mail = new Mail();
        $user = $this->getUser();
        $mail->setObject('Votre devis n°' . $quote->getId() . ' est disponible : ' . $quote->getCompany()->getName());
        if ($user->getQuoteMailContent()) {
            $mail->setContent($user->getQuoteMailContent());
        }
        if ($user->getMailSignature()) {
            $mail->setSignature($user->getMailSignature());
        }

        $form = $this->createForm(MailFormType::class, $mail);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            if($form->getClickedButton() && 'send' === $form->getClickedButton()->getName()) {
                $mailer->sendQuote($quote, $mail);

                $quote->setStatus(QuoteStatusEnum::SENT);
                $entityManager->flush();

                $this->addFlash('success', 'Le devis a été envoyée');
            }
        }

        return $this->render('layout/step_four.html.twig', [
            'entity' => $quote,
            'title' => 'Devis',
            'form' => $form,
            'mail' => $mail
        ]);
    }

    #[Route('quote/add-item/{id_quote}/{id_product}', name: 'app_user_quote_add_item', methods: ['GET'])]
    #[IsGranted('edit', 'quote')]
    public function addItem(
        EntityManagerInterface                 $entityManager,
        #[MapEntity(id: 'id_quote')] Quote     $quote,
        #[MapEntity(id: 'id_product')] Product $product
    ): Response
    {
        $item = $entityManager->getRepository(Item::class)->findOneBy(['product' => $product, 'quote' => $quote]);

        if ($item) {
            $item->setQuantity($item->getQuantity() + 1);

            $this->addFlash('success', 'La quantité du produit ' . $product->getName() . ' a été augmentée');

        } else {
            $item = new Item();
            $item->setProduct($product);
            $item->setQuantity(1);
            $item->setPrice($product->getPrice());

            $quote->addItem($item);

            $this->addFlash('success', 'Le produit ' . $product->getName() . ' a été ajouté');
        }

        $entityManager->persist($item);
        $entityManager->flush();

        return $this->redirectToRoute('app_user_quote_step_two', ['id' => $quote->getId()]);
    }

    #[Route('quote/remove-item/{id_quote}/{id_item}', name: 'app_user_quote_remove_item', methods: ['GET'])]
    #[IsGranted('edit', 'quote')]
    public function removeItem(
        EntityManagerInterface             $entityManager,
        #[MapEntity(id: 'id_quote')] Quote $quote,
        #[MapEntity(id: 'id_item')] Item   $item
    ): Response
    {
        $quote->removeItem($item);
        $entityManager->remove($item);
        $entityManager->flush();

        return $this->redirectToRoute('app_user_quote_step_two', ['id' => $quote->getId()]);
    }

    #[Route('quote/increase-quantity-item/{id_item}', name: 'app_user_quote_increase_quantity_item', methods: ['GET'])]
    #[IsGranted('edit', 'item')]
    public function increaseQuantityItem(
        EntityManagerInterface           $entityManager,
        #[MapEntity(id: 'id_item')] Item $item
    ): Response
    {
        $item->setQuantity($item->getQuantity() + 1);
        $entityManager->persist($item);
        $entityManager->flush();

        $quote = $item->getQuote();

        return $this->redirectToRoute('app_user_quote_step_two', ['id' => $quote->getId()]);
    }

    #[Route('quote/decrease-quantity-item/{id_item}', name: 'app_user_quote_decrease_quantity_item', methods: ['GET'])]
    #[IsGranted('edit', 'item')]
    public function decreaseQuantityItem(
        EntityManagerInterface           $entityManager,
        #[MapEntity(id: 'id_item')] Item $item
    ): Response
    {
        if ($item->getQuantity() > 1) {
            $item->setQuantity($item->getQuantity() - 1);
            $entityManager->persist($item);
            $entityManager->flush();
        } else {
            $this->addFlash('danger', 'La quantité minimale est 1');
        }

        $quote = $item->getQuote();

        return $this->redirectToRoute('app_user_quote_step_two', ['id' => $quote->getId()]);
    }

    #[Route('quote/delete/{id}/{token}', name: 'app_user_quote_delete', methods: ['GET'])]
    #[IsGranted('delete', 'quote')]
    public function delete(Request $request, Quote $quote, EntityManagerInterface $entityManager, string $token): Response
    {
        if ($this->isCsrfTokenValid('delete' . $quote->getId(), $token)) {
            $items = $quote->getItems();
            foreach ($items as $item) {
                $entityManager->remove($item);
            }

            $entityManager->remove($quote);
            $entityManager->flush();

            $this->addFlash('success', 'Le devis n°' . $quote->getId() . ' a bien été supprimé');
        }

        return $this->redirectToRoute('app_user_quote_index');
    }

    #[Route('quote/convert/{id}/{token}', name: 'app_user_quote_convert', methods: ['GET', 'POST'])]
    #[IsGranted('convert', 'quote')]
    public function convertQuote(EntityManagerInterface $entityManager, Request $request, Quote $quote, string $token): Response
    {

        if (!$quote->isValid()) {
            $this->addFlash('danger', 'Le devis n°' . $quote->getId() . ' n\'est pas valide');
            return $this->redirectToRoute('app_user_quote_index');
        }

        if ($quote->getStatus() != QuoteStatusEnum::ACCEPTED) {
            $this->addFlash('danger', 'Le devis n°' . $quote->getId() . ' ne peut pas être converti car il n\'est pas accepté');
            return $this->redirectToRoute('app_user_quote_index');
        }

        if (!$this->isCsrfTokenValid('convert' . $quote->getId(), $token)) {
            $this->addFlash('danger', 'Le token est invalide');
            return $this->redirectToRoute('app_user_quote_index');
        }

        $form = $this->createForm(QuoteConvertFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $invoice = new Invoice();
            $invoice->setCompany($quote->getCompany());
            $invoice->setCustomer($quote->getCustomer());
            $invoice->setQuote($quote);
            $invoice->setDetails($quote->getDetails());
            $invoice->setDate(new \DateTimeImmutable());
            $invoice->setDueDate(\DateTimeImmutable::createFromMutable($form->get('dueDate')->getData()));
            $invoice->setStatus(InvoiceStatusEnum::SENT);
            $items = $quote->getItems();
            foreach ($items as $item) {
                $invoice->addItem($item);
            }

            $amount = $form->get('amount')->getData();
            $paymentMethod = $form->get('paymentMethod')->getData();
            $success = false;

            switch ($form->get('convertType')->getData()->name) {
                case 'WITHOUT_MODIFICATION':

                    $invoice->setTotalAmount($quote->getTotalAmount());
                    $invoice->setDiscountAmount(0);
                    $success = true;
                    break;


                case 'WITH_DISCOUNT':

                    $invoice->setTotalAmount(($quote->getTotalAmount() - $amount));
                    $invoice->setDiscountAmount(($quote->getDiscountAmount() + $amount));
                    $success = true;
                    break;

                case 'WITH_DEPOSIT':

                    if(!$amount || $amount > $quote->getTotalAmount()) {
                        $this->addFlash('danger', 'Le montant du dépôt ne peut pas être supérieur au montant total');
                        break;
                    }

                    if($paymentMethod == null) {
                        $this->addFlash('danger', 'Le mode de paiement est obligatoire');
                        break;
                    }

                    $deposit = new Deposit();
                    $deposit->setInvoice($invoice);
                    $deposit->setAmount($amount);

                    $payment = new Payment();
                    $payment->setInvoice($invoice);
                    $payment->setAmount($amount);
                    $payment->setMethod($paymentMethod);
                    $payment->setStatus(PaymentStatusEnum::PAID);
                    $payment->setDate(new \DateTimeImmutable());

                    $entityManager->persist($deposit);
                    $entityManager->persist($payment);

                    $invoice->setTotalAmount($quote->getTotalAmount());
                    $invoice->setDeposit($deposit);
                    $success = true;
                    break;

                case 'WITH_DEPOSIT_PERCENTAGE':
                    $amountPercent = $quote->getTotalAmount() * $amount / 100;

                    if(!$amount || $amountPercent > $quote->getTotalAmount()) {
                        $this->addFlash('danger', 'Le montant du dépôt ne peut pas être supérieur au montant total');
                        break;
                    }
                    if($paymentMethod == null) {
                        $this->addFlash('danger', 'Le mode de paiement est obligatoire');
                        break;
                    }

                    $deposit = new Deposit();
                    $deposit->setInvoice($invoice);
                    $deposit->setAmount($amountPercent);

                    $payment = new Payment();
                    $payment->setInvoice($invoice);
                    $payment->setAmount($amountPercent);
                    $payment->setMethod($paymentMethod);
                    $payment->setStatus(PaymentStatusEnum::PAID);
                    $payment->setDate(new \DateTimeImmutable());

                    $entityManager->persist($deposit);
                    $entityManager->persist($payment);

                    $invoice->setTotalAmount($quote->getTotalAmount());
                    $invoice->setDeposit($deposit);
                    $success = true;
                    break;

                default:
                    $this->addFlash('danger', 'L\'action n\'est pas valide');
                    return $this->redirectToRoute('app_user_quote_index');
            }
            
            if($success) {
                $this->addFlash('success', 'Le devis n°' . $quote->getId() . ' a bien été converti en facture n°' . $invoice->getId());
                $entityManager->persist($invoice);
                $entityManager->flush();
                return $this->redirectToRoute('app_user_invoice_index');
            }

        }

        return $this->render('quotes/quote_convert.html.twig', [
            'quote' => $quote,
            'form' => $form
        ]);

    }

    #[Route('quote/pdf/{id}', name: 'app_user_quote_pdf', methods: ['GET'])]
    #[IsGranted('see', 'quote')]
    public function generatePdf(Quote $quote): Response
    {

        if (!$quote->isValid()) {
            $this->addFlash('danger', 'Le devis n°' . $quote->getId() . ' n\'est pas valide');
            return $this->redirectToRoute('app_user_quote_index');
        }

        $dompdf = new Dompdf();

        $html = $this->renderView('quotes/quote_pdf.html.twig', [
            'quote' => $quote
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdf = $dompdf->output();

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf'
        ]);
    }
}
