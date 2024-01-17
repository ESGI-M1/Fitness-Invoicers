<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController
{
    #[Route('/invoicer', name: 'invoicer')]
    public function index(): Response
    {
        return $this->render('invoices/invoices_index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }
}
