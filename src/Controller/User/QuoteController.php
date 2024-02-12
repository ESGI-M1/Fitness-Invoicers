<?php

namespace App\Controller\User;

use App\Entity\Quote;
use App\Enum\QuoteStatusEnum;
use App\Form\Quote\QuoteFormType;
use App\Form\Quote\QuoteSearchType;
use App\Service\CompanySession;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuoteController extends AbstractController
{
    private $companySession;

    public function __construct(CompanySession $companySession)
    {
        $this->companySession = $companySession;
    }

    #[Route('/quote', name: 'app_user_quote_index')]
    public function list(EntityManagerInterface $entityManager, Request $request, PaginatorInterface $paginator): Response
    {
        $form = $this->createForm(
            QuoteSearchType::class,
        );

        $form->handleRequest($request);

        $company = $this->companySession->getCurrentCompany();

        $quotes = $paginator->paginate(
            $company->getQuotes(),
            $request->query->getInt('page', 1),
            $request->query->getInt('items', 20)
        );

        return $this->render('quotes/quote_index.html.twig', [
            'quotes' => $quotes,
            'form' => $form
        ]);
    }

    #[Route('/quote/show/{id}', name: 'app_user_quote_show', methods: ['GET'])]
    public function show(Quote $quote, Request $request): Response
    {
        $form = $this->createForm(QuoteFormType::class, $quote);
        $form->handleRequest($request);

        return $this->render('show.html.twig', [
            'entity' => 'Devis',
            'form' => $form
        ]);
    }

    #[Route('quote/add', name: 'app_user_quote_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $quote = new Quote();
        $form = $this->createForm(QuoteFormType::class, $quote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quote->setStatus(QuoteStatusEnum::DRAFT);
            $quote->setCompany($this->companySession->getCurrentCompany());
            $entityManager->persist($quote);
            $entityManager->flush();
            $this->addFlash('success', 'Le devis a bien été ajouté.');

            return $this->redirectToRoute('app_user_quote_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('action.html.twig', [
            'action' => 'Ajouter un devis',
            'quote' => $quote,
            'form' => $form,
        ]);
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
        if ($this->isCsrfTokenValid('delete'.$quote->getId(), $token)) {
            $entityManager->remove($quote);
            $entityManager->flush();
            $this->addFlash('success', 'Le devis a bien été supprimé.');
        }

        return $this->redirectToRoute('app_user_quote_index', [], Response::HTTP_SEE_OTHER);
    }
}
