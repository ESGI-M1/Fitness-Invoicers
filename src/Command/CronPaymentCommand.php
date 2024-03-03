<?php

namespace App\Command;

use App\Entity\Payment;
use App\Enum\PaymentStatusEnum;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name : 'app:cron-payment',
    description : 'Add a short description for your command',
)]
class CronPaymentCommand extends Command
{
    public function __construct(
        private Mailer $mailer,
        private EntityManagerInterface $entityManager,

    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $payments = $this->entityManager->getRepository(Payment::class)->findBy(['status' => PaymentStatusEnum::PENDING]);
        $now = new \DateTime();
        $threeDaysDueDate = (new \DateTime())->modify('+3 days');
        $sevenDaysDueDate = (new \DateTime())->modify('+7 days');

        foreach ($payments as $payment) {
            $dueDate = $payment->getInvoice()->getDueDate();
            if($this->sameDay($now, $dueDate) || $this->sameDay($threeDaysDueDate, $dueDate) || $this->sameDay($sevenDaysDueDate, $dueDate)) {
                $this->mailer->sendPaymentReminder($payment);
            }
        }

        $io->success('Success');

        return Command::SUCCESS;
    }

    private function sameDay(\DateTime $date1, \DateTimeImmutable $date2): bool
    {
        return $date1->format('Y-m-d') === $date2->format('Y-m-d');
    }
}
