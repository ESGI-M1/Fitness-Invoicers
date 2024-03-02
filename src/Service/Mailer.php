<?php

namespace App\Service;

use App\Entity\Invoice;
use App\Entity\Mail;
use App\Entity\Quote;
use App\Enum\InvoiceStatusEnum;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Part\DataPart;
use Twig\Environment;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;


use Dompdf\Dompdf;

class Mailer
{
    private MailerInterface $mailer;
    private string $supportEmail;

    private Security $security;

    private Environment $twig;

    private UploaderHelper $uploaderHelper;

    public function __construct(MailerInterface $mailer, string $supportEmail, Security $security, Environment $twig, UploaderHelper $uploaderHelper)
    {
        $this->mailer = $mailer;
        $this->supportEmail = $supportEmail;
        $this->security = $security;
        $this->twig = $twig;
        $this->uploaderHelper = $uploaderHelper;
    }

    public function sendInvoice(Invoice $invoice, Mail $mail, bool $pdf = true)
    {

        $pdf = $this->generatePdfInvoice($invoice);

        $email = (new TemplatedEmail())
            ->from($this->supportEmail)
            ->to($invoice->getCustomer()->getEmail())
            ->subject($mail->getObject())
            ->text($mail->getContent())
            ->htmlTemplate('mails/mail.html.twig')
            ->addPart(new DataPart($pdf, 'facture.pdf', 'application/pdf'))
            ->context([
                'invoice' => $invoice,
                'company' => $invoice->getCompany(),
                'mail' => $mail,
                'user' => $this->security->getUser()
            ]);

        $this->mailer->send($email);
    }

    public function sendQuote(Quote $quote, Mail $mail, bool $pdf = true)
    {
        $pdf = $this->generatePdfQuote($quote);

        $email = (new TemplatedEmail())
            ->from($this->supportEmail)
            ->to($quote->getCustomer()->getEmail())
            ->subject($mail->getObject())
            ->text($mail->getContent())
            ->htmlTemplate('mails/mail.html.twig')
            ->addPart(new DataPart($pdf, 'devis.pdf', 'application/pdf'))
            ->context([
                'quote' => $quote,
                'company' => $quote->getCompany(),
                'mail' => $mail,
                'user' => $this->security->getUser()
            ]);

        $this->mailer->send($email);
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
