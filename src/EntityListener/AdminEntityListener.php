<?php

namespace App\EntityListener;

use App\Entity\Admin;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, entity: Admin::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Admin::class)]
class AdminEntityListener
{

    public function __invoke(Admin $admin) : void
    {
       [$username,] = explode('@', $admin->getEmail());
       $admin->setUsername($username);
    }
}