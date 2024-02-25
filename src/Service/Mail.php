<?php

namespace App\Service;

use App\Entity\Customer;
use App\Entity\Invoice;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class Mail
{
    private MailerInterface $mailer;
    private string $supportEmail;

    public function __construct(MailerInterface $mailer, string $supportEmail)
    {
        $this->mailer = $mailer;
        $this->supportEmail = $supportEmail;
    }

    public function sendInvoice(Customer $customer, Invoice $invoice)
    {
        $email = (new Email())
            ->from($this->supportEmail)
            ->to($customer->getEmail())
            ->subject('Invoice')
            ->text('Invoice for ' . $invoice->getAmount() . ' EUR');

        $this->mailer->send($email);

    }

}
