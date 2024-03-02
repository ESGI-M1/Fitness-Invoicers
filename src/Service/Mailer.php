<?php

namespace App\Service;

use App\Entity\Invoice;
use App\Entity\Mail;
use App\Entity\Quote;
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

        $dompdf = new Dompdf();

        $html = $this->twig->render('invoices/invoice_pdf.html.twig', [
            'invoice' => $invoice
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdf = $dompdf->output();

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

        $dompdf = new Dompdf();

        $html = $this->twig->render('quotes/quote_pdf.html.twig', [
            'quote' => $quote
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdf = $dompdf->output();

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

}
