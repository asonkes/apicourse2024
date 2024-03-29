<?php

namespace App\Events;

use App\Entity\User;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordEncoderSubscriber implements EventSubscriberInterface
{

    public function __construct(private UserPasswordHasherInterface $encoder)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['encodePassword', EventPriorities::PRE_WRITE] // avant écriture === PRE_WHITE
        ];
    }

    public function encodePassword(ViewEvent $event)
    {
        $user = $event->getControllerResult(); // permet de récup l'objet désérialisé(l'objet complet de mon élément)
        $method = $event->getRequest()->getMethod(); // permet de récup la méthode (GET, POST, ...)

        /* Comme il va toujours se faire au kernel VIEW */
        /* On doit vérifier quand la requête est de type POST et concerne un USER */
        if ($user instanceof User && $method === "POST") {
            $hash = $this->encoder->hashPassword($user, $user->getPassword());
            $user->setPassword($hash);
        }
    }
}
