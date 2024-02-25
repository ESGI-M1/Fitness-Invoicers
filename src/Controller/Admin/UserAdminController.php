<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\User\UserSearchType;
use App\Form\User\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class UserAdminController extends AbstractController
{
    #[Route('/user_admin', name: 'app_admin_user_index')]
    public function list(
        EntityManagerInterface $entityManager,
        Request $request,
        PaginatorInterface $paginator,
    ): Response {
        $form = $this->createForm(
            UserSearchType::class,
        );

        $form->handleRequest($request);

        $users = $paginator->paginate(
            $entityManager->getRepository(User::class)->getUsersByFilters($form->getData()),
            $request->query->getInt('page', 1),
            $request->query->getInt('items', 20)
        );

        return $this->render('users/user_index_admin.html.twig', [
            'users' => $users,
            'form' => $form
        ]);
    }

    #[Route('user_admin/add', name: 'app_admin_user_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('action.html.twig', [
            'action' => 'Ajouter un utilisateur',
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('user_admin/edit/{id}', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('action.html.twig', [
            'action' => 'Modifier l\'utilisateur n°' . $user->getId(),
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('user_admin/delete/{id}', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
