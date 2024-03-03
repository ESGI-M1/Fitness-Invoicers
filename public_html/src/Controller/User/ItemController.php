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

class ItemController extends AbstractController
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

    #[Route('/add', name: 'app_user_item_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $item = new Item();
        $form = $this->createForm(ItemFormType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($item);
            $entityManager->flush();

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

            return $this->redirectToRoute('app_user_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('action.html.twig', [
            'action' => 'Modifier l\'item n°' . $item->getId(),
            'item' => $item,
            'form' => $form,
        ]);
    }

    #[Route('item/delete/{id}', name: 'app_user_item_delete', methods: ['POST'])]
    public function delete(Request $request, Item $item, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$item->getId(), $request->request->get('_token'))) {
            $entityManager->remove($item);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_item_index', [], Response::HTTP_SEE_OTHER);
    }
}
