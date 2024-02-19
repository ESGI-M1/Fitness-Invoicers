<?php

namespace App\Controller\User;

use App\Entity\Item;
use App\Form\Category\CategoryFormType;
use App\Form\Item\ItemFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\MainController;

class ItemController extends MainController
{
    #[Route('/item', name: 'app_user_item_index')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $users = $this->getUser();
        $items = $entityManager->getRepository(Item::class)->findBy([$users]);

        return $this->render('items/item_index.html.twig', [
            'items' => $items,
        ]);
    }

    #[Route('/item/show/{id}', name: 'app_user_item_show', methods: ['GET'])]
    public function show(Item $item): Response
    {
        return $this->render('items/item_show.html.twig', [
            'item' => $item,
        ]);
    }

    #[Route('item/add', name: 'app_user_item_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $item = new Item();
        $form = $this->createForm(ItemFormType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($item);
            $entityManager->flush();
            $this->addFlash('success', 'L\'item a bien été ajouté');

            return $this->redirectToRoute('app_user_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('action.html.twig', [
            'action' => 'Ajouter un item',
            'item' => $item,
            'form' => $form,
        ]);
    }

    #[Route('item/edit/{id}', name: 'app_user_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Item $item, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ItemFormType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'L\'item a bien été modifié');

            return $this->redirectToRoute('app_user_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('action.html.twig', [
            'action' => 'Modifier l\'item n°' . $item->getId(),
            'item' => $item,
            'form' => $form,
        ]);
    }

    #[Route('item/delete/{id}/{token}', name: 'app_user_item_delete', methods: ['GET'])]
    public function delete(Request $request, Item $item, EntityManagerInterface $entityManager, string $token): Response
    {
        if ($this->isCsrfTokenValid('delete'.$item->getId(), $token)) {
            $entityManager->remove($item);
            $entityManager->flush();
            $this->addFlash('success', 'L\'item a bien été supprimé');
        }

        return $this->redirectToRoute('app_user_item_index', [], Response::HTTP_SEE_OTHER);
    }
}
