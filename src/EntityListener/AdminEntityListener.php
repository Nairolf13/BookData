<?php

namespace App\EntityListener;

use App\Entity\Admin;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::prePersist, entity: Admin::class)]
class AdminEntityListener
{
    public function prePersist(Admin $admin, LifecycleEventArgs $event): void
    {
        $admin->setRoles(['ROLE_USER']);
    }
}