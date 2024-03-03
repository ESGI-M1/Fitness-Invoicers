<?php

namespace App\EventListener;

use App\Entity\Invoice;
use App\Enum\InvoiceStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Invoice::class)]
class InvoiceListener
{

    public function preUpdate(Invoice $invoice, PreUpdateEventArgs $event)
    {
        $eventChange = $event->getEntityChangeSet();

        if (!isset($eventChange['status'])) {
            return;
        }
        
        $status = $eventChange['status'];

        if ($status[0] != InvoiceStatusEnum::DRAFT->value) {
            // to do
            //throw new \Exception('You can not change status of an invoice already sent');
        }
    }
}