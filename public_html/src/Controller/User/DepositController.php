<?php

namespace App\Controller\User;

use App\Entity\Deposit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DepositController extends AbstractController
{
    #[Route('/deposit', name: 'app_user_deposit_index')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $users = $this->getUser();
        $deposit = $entityManager->getRepository(Deposit::class)->findBy([$users]);

        return $this->render('deposit/deposit_index.html.twig', [
            'deposit' => $deposit,
        ]);
    }
}
