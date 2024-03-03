<?php

namespace App\Controller\User;

use App\Entity\Product;
use App\Form\Product\ProductFormType;
use App\Form\Product\ProductSearchType;
use App\Service\CompanySession;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_user_product_index')]
    public function list(EntityManagerInterface $entityManager, Request $request, PaginatorInterface $paginator, CompanySession $companySession): Response
    {

        $company = $companySession->getCurrentCompany();

        $form = $this->createForm(
            ProductSearchType::class,
        );

        $form->handleRequest($request);

        $product = $paginator->paginate(
            $entityManager->getRepository(Product::class)->getProductsByFilters($company, $form->getData()),
            $request->query->getInt('page', 1),
            $request->query->getInt('items', 9)
        );

        return $this->render('products/product_index.html.twig', [
            'products' => $product,
            'form' => $form
        ]);
    }

    #[Route('product/show/{id}', name: 'app_user_product_show', methods: ['GET'])]
    #[IsGranted('see', 'product')]
    public function show(Product $product): Response
    {
        return $this->render('products/product_show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('product/add', name: 'app_user_product_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager, CompanySession $companySession): Response
    {

        $company = $companySession->getCurrentCompany();

        $product = new Product();
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $product->setCompany($company);
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success', 'Le produit a bien été ajouté');

            return $this->redirectToRoute('app_user_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('action.html.twig', [
            'action' => 'Ajouter un produit',
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('product/edit/{id}', name: 'app_user_product_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'product')]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le produit a bien été modifié');

            return $this->redirectToRoute('app_user_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('action.html.twig', [
            'action' => 'Modifier le produit n°' . $product->getId(),
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('product/delete/{id}/{token}', name: 'app_user_product_delete', methods: ['GET'])]
    #[IsGranted('delete', 'product')]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager, string $token): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $token)) {
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success', 'Le produit a bien été supprimé');
        }

        return $this->redirectToRoute('app_user_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
