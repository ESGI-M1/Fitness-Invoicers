<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\Invoice;
use App\Entity\Quote;
use App\Enum\QuoteStatusEnum;
use App\Enum\InvoiceStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;

class Dashboard
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) { }

    public function handleForm(Form $form, Company $company){
        dump("handleForm", $form->getclickedButton()->getName());

        switch ($form->getclickedButton()->getName()) {
            case 'submitDay':
                $startDate = new \DateTime();
                $endDate = new \DateTime();
                break;
            case 'submitMonth':
                $startDate = new \DateTime('first day of this month');
                $endDate = new \DateTime('last day of this month');
                break;
            case 'submitYear':
                $startDate = new \DateTime('first day of January this year');
                $endDate = new \DateTime('last day of December this year');
                break;
            case 'submitDayBefore':
                $startDate = new \DateTime('yesterday');
                $endDate = new \DateTime('yesterday');
                break;
            case 'submitMonthBefore':
                $startDate = new \DateTime('first day of last month');
                $endDate = new \DateTime('last day of last month');
                break;
            case 'submitYearBefore':
                $startDate = new \DateTime('first day of January last year');
                $endDate = new \DateTime('last day of December last year');
                break;
            case 'submitDateRange':
                $startDate = $form->get('startDate')->getData();
                $endDate = $form->get('endDate')->getData();
                break;
            default:
                $startDate = new \DateTime('first day of this month');
                $endDate = new \DateTime('last day of this month');
                break;
        }

        $invoices = $this->entityManager->getRepository(Invoice::class)->findByDateRange($company, $startDate, $endDate);
        $quotes = $this->entityManager->getRepository(Quote::class)->findByDateRange($company, $startDate, $endDate);

        $invoicesByStatus = $this->initInvoicesByStatus();
        foreach ($invoices as $invoice) {

            $invoicesByStatus['amount'][$invoice->getStatus()->name] += $invoice->getAmount();
            $invoicesByStatus['invoices'][$invoice->getStatus()->name][] = $invoice;
        }

        $quotesByStatus = $this->initQuotesByStatus();
        foreach ($quotes as $quote) {

            $quotesByStatus['amount'][$quote->getStatus()->name] += $quote->getAmount();
            $quotesByStatus['quotes'][$quote->getStatus()->name][] = $quote;
        }

        $startDate->setTime(0, 0, 0);
        $endDate->setTime(23, 59, 59);

        return [
            'invoices' => $invoices,
            'quotes' => $quotes,
            'invoicesByStatus' => $invoicesByStatus,
            'quotesByStatus' => $quotesByStatus,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

    }

    public function getDefaultStatistics(Company $company){
        $startDate = new \DateTime('first day of this month');
        $endDate = new \DateTime('last day of this month');

        $invoices = $this->entityManager->getRepository(Invoice::class)->findByDateRange($company, $startDate, $endDate);
        $quotes = $this->entityManager->getRepository(Quote::class)->findByDateRange($company, $startDate, $endDate);

        $invoicesByStatus = $this->initInvoicesByStatus();
        foreach ($invoices as $invoice) {

            $invoicesByStatus['amount'][$invoice->getStatus()->name] += $invoice->getAmount();
            $invoicesByStatus['invoices'][$invoice->getStatus()->name][] = $invoice;
        }

        $quotesByStatus = $this->initQuotesByStatus();
        foreach ($quotes as $quote) {

            $quotesByStatus['amount'][$quote->getStatus()->name] += $quote->getAmount();
            $quotesByStatus['quotes'][$quote->getStatus()->name][] = $quote;
        }

        return [
            'invoices' => $invoices,
            'quotes' => $quotes,
            'invoicesByStatus' => $invoicesByStatus,
            'quotesByStatus' => $quotesByStatus,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    private function initInvoicesByStatus(){
        $invoicesByStatus = [];
        foreach (InvoiceStatusEnum::cases() as $status) {
            $invoicesByStatus['amount'][$status->name] = 0;
            $invoicesByStatus['invoices'][$status->name] = [];
        }
        return $invoicesByStatus;
    }

    private function initQuotesByStatus(){
        $quotesByStatus = [];
        foreach (QuoteStatusEnum::cases() as $status) {
            $quotesByStatus['amount'][$status->name] = 0;
            $quotesByStatus['quotes'][$status->name] = [];
        }
        return $quotesByStatus;
    }

}
