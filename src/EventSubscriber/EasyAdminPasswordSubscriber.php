<?php

namespace App\EventSubscriber;

use App\Controller\Admin\SecurityController;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EasyAdminPasswordSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SecurityController $securityController
    )
    {

    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['modifyUser'],
            BeforeEntityUpdatedEvent::class => ['modifyUser'],
        ];
    }

    public function modifyUser(BeforeEntityPersistedEvent|BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        if (!($entity instanceof User)) {
            return;
        }

        if ($entity->getPlainPassword()) {
            $entity->setPassword( $this->securityController->passwordHasher($entity, $entity->getPlainPassword()));
        }
    }
}