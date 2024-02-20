<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\MainController;

class OnKernelRights
{
    public function onKernelController(ControllerEvent $event)
    {
    }
}
