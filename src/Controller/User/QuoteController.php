<?php

namespace App\Controller\User;

use App\Entity\Deposit;
use App\Entity\Quote;
use App\Entity\Invoice;
use App\Entity\Product;
use App\Entity\Item;
use App\Enum\InvoiceStatusEnum;
use App\Enum\QuoteStatusEnum;
use App\Form\Quote\QuoteCustomerFormType;
use App\Form\Quote\QuoteFormType;
use App\Form\Quote\QuoteSearchType;
use App\Form\Quote\QuoteCategoryFormType;
use App\Form\Quote\QuoteStatusFormType;
use App\Form\Item\ItemStepTwoFormType;
use App\Form\Quote\QuoteConvertFormType;
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

    #[Route('quote/add', name: 'app_user_quote_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager, CompanySession $companySession): Response
    {

        $quote = new Quote();
        $form = $this->createForm(QuoteFormType::class, $quote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $quote->setStatus(QuoteStatusEnum::DRAFT);
            $quote->setCompany($companySession->getCurrentCompany());
            $entityManager->persist($quote);
            $entityManager->flush();
            $this->addFlash('success', 'Le devis a bien été ajouté.');

            return $this->redirectToRoute('app_user_quote_index');
        }

        return $this->render('action.html.twig', [
            'action' => 'Ajouter un devis',
            'quote' => $quote,
            'form' => $form,
        ]);
    }

    #[Route('quote/step_one/{id}', name: 'app_user_quote_step_one', defaults: ['id' => null], methods: ['GET', 'POST'])]
    public function stepOne(Request $request, EntityManagerInterface $entityManager, Quote $quote = null, CompanySession $companySession): Response
    {

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
            $this->addFlash('danger', 'La facture ' . $quote->getId() . ' ne peut être modifiée');
            return $this->redirectToRoute('app_user_quote_index');
        }

        $form = $this->createForm(QuoteCustomerFormType::class, $quote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($quote);
            $entityManager->flush();
        }

        return $this->render('quotes/quote_step_one.html.twig', [
            'form' => $form,
            'quote' => $quote,
            'customer' => $quote->getCustomer()
        ]);
    }

    #[Route('quote/step_two/{id}', name: 'app_user_quote_step_two', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'quote')]
    public function stepTwo(Request $request, EntityManagerInterface $entityManager, Quote $quote, CompanySession $companySession): Response
    {

        if ($quote->getStatus() != QuoteStatusEnum::DRAFT) {
            $this->addFlash('danger', 'La facture ' . $quote->getId() . ' ne peut être modifiée');
            return $this->redirectToRoute('app_user_quote_index');
        }

        if (!$quote->isValidStepOne()) {
            $this->addFlash('danger', 'La facture ' . $quote->getId() . ' n\'a pas de client');
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

        return $this->render('quotes/quote_step_two.html.twig', [
            'productFromCategory' => $productFromCategory,
            'productWithoutCategory' => $productWithoutCategory,
            'categoryForm' => $categoryForm,
            'quoteItems' => $quoteItems,
            'quote' => $quote,
        ]);
    }

    #[Route('quote/step_three/{id}', name: 'app_user_quote_step_three', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'quote')]
    public function stepThree(Request $request, EntityManagerInterface $entityManager, quote $quote): Response
    {

        if ($quote->getStatus() != QuoteStatusEnum::DRAFT) {
            $this->addFlash('danger', 'La facture ' . $quote->getId() . ' ne peut être modifiée');
            return $this->redirectToRoute('app_user_quote_index');
        }

        if (!$quote->isValidStepTwo()) {
            $this->addFlash('danger', 'La facture ' . $quote->getId() . ' n\'a pas de produit');
            return $this->redirectToRoute('app_user_quote_step_two', ['id' => $quote->getId()]);
        }

        $form = $this->createForm(QuoteStatusFormType::class, $quote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($quote->getStatus() == QuoteStatusEnum::ACCEPTED) {
                $quote->setDate(new \DateTimeImmutable());
            }

            $entityManager->flush();
            $this->addFlash('success', 'Le devis a bien été modifié.');

            return $this->redirectToRoute('app_user_quote_show', ['id' => $quote->getId()]);
        }

        return $this->render('quotes/quote_step_three.html.twig', [
            'quote' => $quote,
            'form' => $form
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

    #[Route('quote/edit/{id}', name: 'app_user_quote_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quote $quote, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(QuoteFormType::class, $quote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le devis a bien été modifié.');

            return $this->redirectToRoute('app_user_quote_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('action.html.twig', [
            'action' => 'Modifier le devis n°' . $quote->getId(),
            'quote' => $quote,
            'form' => $form,
        ]);
    }

    #[Route('quote/delete/{id}/{token}', name: 'app_user_quote_delete', methods: ['POST'])]
    public function delete(Request $request, Quote $quote, EntityManagerInterface $entityManager, string $token): Response
    {
        if ($this->isCsrfTokenValid('delete' . $quote->getId(), $token)) {
            $entityManager->remove($quote);
            $entityManager->flush();
            $this->addFlash('success', 'Le devis a bien été supprimé.');
        }

        return $this->redirectToRoute('app_user_quote_index', [], Response::HTTP_SEE_OTHER);
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
            $invoice->setStatus(InvoiceStatusEnum::SENT);
            $items = $quote->getItems();
            foreach ($items as $item) {
                $invoice->addItem($item);
            }

            switch ($form->get('convertType')->getData()->name) {
                case 'WITHOUT_MODIFICATION':
                    $invoice->setTotalAmount($quote->getTotalAmount());
                    $invoice->setDiscountAmount(0);
                    break;
                case 'WITH_DISCOUNT':
                    $invoice->setTotalAmount(($quote->getTotalAmount() - $form->get('amount')->getData()));
                    $invoice->setDiscountAmount(($quote->getDiscountAmount() + $form->get('amount')->getData()));
                    break;
                case 'WITH_DEPOSIT':
                    $deposit = new Deposit();
                    $deposit->setInvoice($invoice);
                    $deposit->setAmount($form->get('amount')->getData());

                    $entityManager->persist($deposit);

                    $invoice->setTotalAmount($quote->getTotalAmount());
                    $invoice->setDeposit($deposit);
                    break;
                case 'WITH_DEPOSIT_PERCENTAGE':
                    $deposit = new Deposit();
                    $deposit->setInvoice($invoice);
                    $deposit->setAmount($quote->getTotalAmount() * $form->get('amount')->getData() / 100);

                    $entityManager->persist($deposit);

                    $invoice->setTotalAmount($quote->getTotalAmount());
                    $invoice->setDeposit($deposit);
                    break;
                default:
                    $this->addFlash('danger', 'L\'action n\'est pas valide');
                    return $this->redirectToRoute('app_user_quote_index');
                    break;
            }

            $entityManager->persist($invoice);
            $entityManager->flush();

            $this->addFlash('success', 'Le devis n°' . $quote->getId() . ' a bien été converti en facture n°' . $invoice->getId());
            return $this->redirectToRoute('app_user_invoice_index');
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
