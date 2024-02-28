<?php

namespace App\Controller\User;

use App\Entity\Category;
use App\Form\Category\CategoryFormType;
use App\Form\Category\CategorySearchAdminType;
use App\Form\Category\CategorySearchType;
use App\Service\CompanySession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class CategoryController extends AbstractController
{
    #[Route('/category-admin', name: 'app_admin_category_index', methods: ['GET', 'POST'] )]
    public function listadmin(
        EntityManagerInterface $entityManager,
        Request $request,
        PaginatorInterface $paginator,
        CompanySession         $companySession,
    ): Response {
        $company = $companySession->getCurrentCompany();

        $form = $this->createForm(
            CategorySearchAdminType::class,
        );

        $form->handleRequest($request);

        $categories = $entityManager->getRepository(Category::class);

        $category = $paginator->paginate(
            $categories->getCategoriesByFilters($company, $form->getData()),
            $request->query->getInt('page', 1),
            $request->query->getInt('items', 20)
        );

        return $this->render('categories/category_index_admin.html.twig', [
            'categories' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/category', name: 'app_user_category_index', methods: ['GET', 'POST'])]
    public function list(
        EntityManagerInterface $entityManager,
        Request $request,
        CompanySession $companySession,
        PaginatorInterface $paginator
    ): Response {
        
        $company = $companySession->getCurrentCompany();

        $form = $this->createForm(
            CategorySearchType::class,
        );

        $form->handleRequest($request);

        $categories = $entityManager->getRepository(Category::class);

        $pagination = $paginator->paginate(
            $categories->getCategoriesByFilters($company, $form->getData()),
            $request->query->getInt('page', 1),
            $request->query->getInt('items', 20)
        );

        return $this->render('categories/category_index.html.twig', [
            'categories' => $pagination,
            'form' => $form,
        ]);
    }

    #[Route('category/show/{id}', name: 'app_user_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('categories/category_show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('category/add-admin', name: 'app_admin_category_add', methods: ['GET', 'POST'])]
    public function addAdmin(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();

        $form = $this->createForm(
            CategoryFormAdminType::class,
            $category,
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($category);
                $entityManager->flush();

                return $this->redirectToRoute('app_user_category_index');
            }

        return $this->render('action.html.twig', [
            'action' => 'Ajouter une catégorie',
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('category/add', name: 'app_user_category_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager, CompanySession $companySession): Response
    {
        $company = $companySession->getCurrentCompany();

        $category = new Category();

        $form = $this->createForm(
            CategoryFormType::class,
            $category,
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $category->setCompany($company);

            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_category_index');
        }

        return $this->render('action.html.twig', [
            'action' => 'Ajouter une catégorie',
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('category/edit/{id}', name: 'app_user_category_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'category')]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            dump($category);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_category_index');
        }

        return $this->render('action.html.twig', [
            'action' => 'Modifier la catégorie n°' . $category->getId(),
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('category/delete/{id}/{token}', name: 'app_user_category_delete', methods: ['GET'])]
    #[IsGranted('delete', 'category')]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager, string $token): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $token)) {
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_category_index');
    }
}
