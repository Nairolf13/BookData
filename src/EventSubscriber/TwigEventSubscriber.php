<?php

namespace App\EventSubscriber;

use Twig\Environment;
use App\Repository\BookRepository;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TwigEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
                private Environment $twig,
                private BookRepository $bookRepository
            ) { }
        

    public function onKernelController(): void
    {
        $this->twig->addGlobal('book', $this->bookRepository->findAll());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
