<?php

namespace App\EventSubscriber;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JwtCreatedSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            Events::JWT_CREATED => [
                ['addUserToToken', 0]
            ]
        ];
    }

    public function __construct(private UserRepository $userRepository, private EntityManagerInterface $entityManager)
    {
    }

    public function addUserToToken(JWTCreatedEvent $event): void
    {
        $user = $this->userRepository->findOneBy(['username' => $event->getData()['username']]);

        if ($user === null) {
            return;
        }

        $payload = $event->getData();
        $payload['email'] = $user->getEmail();
        $payload['language'] = $user->getLanguage();

        // Update connection time
        $user->updateLastLogin();
        $this->entityManager->flush();

        $event->setData($payload);
    }
}
