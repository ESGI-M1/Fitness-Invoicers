<?php

namespace App\Controller\User;

use App\Entity\Category;
use App\Form\Category\CategoryFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_user_category_index')]
    public function list(EntityManagerInterface $entityManager, Request $request): Response
    {
        $category =
            $entityManager->getRepository(Category::class)->findAll();

        return $this->render('categories/category_index.html.twig', [
            'categories' => $category,
        ]);
    }

    #[Route('/add', name: 'app_user_category_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();

        $form = $this->createForm(
            CategoryFormType::class,
            $category,
            [
                'action' => $this->generateUrl(
                    'app_user_category_add',
                    [
                    ]
                ),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->request->get('update_fields') != 1) {
                $error = false;

                if (!$error) {
                    $entityManager->persist($category);
                    $entityManager->flush();

                    return $this->redirectToRoute('app_user_category_index', [], Response::HTTP_SEE_OTHER);

//                    return $this->json(
//                        [
//                            'refresh' => $this->generateUrl(
//                                'employees_edit',
//                                [
//                                    '_portal' => $this->portal->getNomAbrege(),
//                                    'tab' => 'assignements',
//                                    'id' => $salarie->getId(),
//                                ]
//                            ),
//                            'target' => '.ajax-content',
//                            'dialog' => false,
//                        ]
//                    );
                }
            }
        }

        return $this->render('action.html.twig', [
            'action' => 'Ajouter une catégorie',
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('category/edit/{id}', name: 'app_user_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('action.html.twig', [
            'action' => 'Modifier la catégorie n°' . $category->getId(),
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('category/delete/{id}', name: 'app_user_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
