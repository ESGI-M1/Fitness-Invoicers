<?php

namespace App\Service;

use App\Entity\Invoice;
use App\Entity\Mail;
use App\Entity\Payment;
use App\Entity\Quote;
use App\Enum\InvoiceStatusEnum;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Part\DataPart;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;


use Dompdf\Dompdf;

class Mailer
{
    private MailerInterface $mailer;
    private string $supportEmail;

    private Security $security;

    private Environment $twig;

    private EntityManagerInterface $entityManager;

    public function __construct(MailerInterface $mailer, string $supportEmail, Security $security, Environment $twig, EntityManagerInterface $entityManager)
    {
        $this->mailer = $mailer;
        $this->supportEmail = $supportEmail;
        $this->security = $security;
        $this->twig = $twig;
        $this->entityManager = $entityManager;
    }

    public function sendInvoice(Invoice $invoice, Mail $mail)
    {

        $pdf = $this->generatePdfInvoice($invoice);

        $mail->setDate(new \DateTimeImmutable());
        $mail->setInvoice($invoice);
        $mail->setCustomer($invoice->getCustomer());

        $email = (new TemplatedEmail())
            ->from($this->supportEmail)
            ->to($invoice->getCustomer()->getEmail())
            ->subject($mail->getObject())
            ->text($mail->getContent())
            ->htmlTemplate('mails/mail.html.twig')
            ->context([
                'invoice' => $invoice,
                'company' => $invoice->getCompany(),
                'mail' => $mail,
                'user' => $this->security->getUser()
            ]);

        if($mail->isJoinPDF()) {
            $email->addPart(new DataPart($pdf, 'facture.pdf', 'application/pdf'));
        }

        $this->mailer->send($email);

        $this->entityManager->persist($mail);
        $this->entityManager->flush();
    }

    public function sendQuote(Quote $quote, Mail $mail)
    {
        $pdf = $this->generatePdfQuote($quote);

        $mail->setDate(new \DateTimeImmutable());
        $mail->setQuote($quote);
        $mail->setCustomer($quote->getCustomer());

        $email = (new TemplatedEmail())
            ->from($this->supportEmail)
            ->to($quote->getCustomer()->getEmail())
            ->subject($mail->getObject())
            ->text($mail->getContent())
            ->htmlTemplate('mails/mail.html.twig')
            ->context([
                'quote' => $quote,
                'company' => $quote->getCompany(),
                'mail' => $mail,
                'user' => $this->security->getUser()
            ]);

        if($mail->isJoinPDF()) {
            $email->addPart(new DataPart($pdf, 'devis.pdf', 'application/pdf'));
        }

        $this->mailer->send($email);

        $this->entityManager->persist($mail);
        $this->entityManager->flush();
    }

    public function sendConfirmationDeletedCustomer($customer, $company)
    {

        $mail = new Mail();
        $mail->setObject('Suppression de vos données : ' . $company->getName());
        $mail->setContent('Madame, Monsieur,
        
        Nous vous informons que vos données ont été supprimées de notre base de données.
        Vous trouverez en pièce jointe l\'ensemble de vos factures.');

        $user = $this->security->getUser();
        $mail->setSignature($user->getMailSignature());
        $invoices = $customer->getInvoices();

        $email = (new TemplatedEmail())
            ->from($this->supportEmail)
            ->to($customer->getEmail())
            ->subject($mail->getObject())
            ->text($mail->getContent())

            ->htmlTemplate('mails/customer_deleted.html.twig')
            ->context([
                'mail' => $mail,
                'user' => $this->security->getUser()
            ]);

        foreach ($invoices as $invoice) {
            if($invoice->getStatus() === InvoiceStatusEnum::VALIDATED || $invoice->getStatus() === InvoiceStatusEnum::SENT) {
                $pdf = $this->generatePdfInvoice($invoice);
                $email->addPart(new DataPart($pdf, 'facture_' . $invoice->getId() . '.pdf', 'application/pdf'));
            }
        }

        $this->mailer->send($email);

        $this->entityManager->persist($mail);
        $this->entityManager->flush();
    }

    public function sendPaymentReminder(Payment $payment)
    {

        $invoice = $payment->getInvoice();
        $dueDate = $invoice->getDueDate();
        $company = $invoice->getCompany();
        $customer = $invoice->getCustomer();

        $mail = new Mail();
        $mail->setObject('Paiement en attente de la facture : ' . $invoice->getId());
        $mail->setContent('Madame, Monsieur,
        
        Nous vous informons que votre paiement pour la facture n°' . $invoice->getId() . ' est en attente.
        
        La date limite de paiement est le ' . $dueDate->format('d/m/Y'));
        $mail->setDate(new \DateTime());
        $mail->setCustomer($customer);
        $mail->setInvoice($invoice);

        $email = (new TemplatedEmail())
            ->from($this->supportEmail)
            ->to($customer->getEmail())
            ->subject($mail->getObject())
            ->text($mail->getContent())
            ->htmlTemplate('mails/payment_notification.html.twig')
            ->context([
                'invoice' => $invoice,
                'company' => $company,
                'mail' => $mail,
            ]);

        $this->mailer->send($email);

        $this->entityManager->persist($mail);
        $this->entityManager->flush();
    }

    private function generatePdfInvoice(Invoice $invoice)
    {
        $dompdf = new Dompdf();

        $html = $this->twig->render('invoices/invoice_pdf.html.twig', [
            'invoice' => $invoice
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->output();
    }

    private function generatePdfQuote(Quote $quote)
    {
        $dompdf = new Dompdf();

        $html = $this->twig->render('quotes/quote_pdf.html.twig', [
            'quote' => $quote
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->output();
    }

}
