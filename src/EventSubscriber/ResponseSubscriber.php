<?php

namespace App\EventSubscriber;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\UserLoader;
use App\Repository\SessionRepository;
use App\Repository\IpRepository;
use App\Entity\Visit;
use App\Service\AuthChecker;
use Doctrine\ORM\EntityManagerInterface;

class ResponseSubscriber implements EventSubscriberInterface
{
    private $sessionRepository;
    private $userRepository;
    private $request;
    private $userLoader;
    private $authChecker;
    private $session;
    private $ipRepository;
    private $entityManager;
    private $container;

    public function __construct(SessionRepository $sessionRepository, RequestStack $requestStack, UserLoader $userLoader, IpRepository $ipRepository, AuthChecker $authChecker, SessionInterface $session, EntityManagerInterface $entityManager, ContainerInterface $container)
    {
        $this->session = $session;
        $this->sessionRepository = $sessionRepository;
        $this->ipRepository = $ipRepository;
        $this->request = $requestStack->getMasterRequest();
        $this->userLoader = $userLoader;
        $this->authChecker = $authChecker;
        $this->entityManager = $entityManager;
        $this->container = $container;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $this->request;
        $entityManager = $this->entityManager;
        $currentUserSession = $this->sessionRepository->findOneByCurrentUser();
        $missResponseEvent = $this->session->getFlashBag()->get('missResponseEvent', []);

        if ($request && $event->isMasterRequest() && $currentUserSession && !$missResponseEvent) {
            $response = $event->getResponse();
            $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);

            $currentUserSession->setLastTime(new \DateTime());
            $user = $this->userLoader->getUser();
            $ip = $this->ipRepository->findOneByIpOrNew($request->getClientIp());
            $uri = $request->getRequestUri();
            $routeName = $request->attributes->get('_route', $uri);

            if ('_wdt' != $routeName &&
                !$this->authChecker->isGranted('ROLE_ADMIN')) {
                $visit = (new Visit())
                    ->setUri($uri)
                    ->setRouteName($routeName)
                    ->setMethod($request->getMethod())
                    ->setSession($currentUserSession)
                    ->setStatusCode($event->getResponse()->getStatusCode());

                $entityManager->persist($visit);
            }

            if ($ip) {
                if (!$this->userLoader->isGuest()) {
                    $user->addIp($ip);
                }
                $currentUserSession->setIp($ip);
            }

            $entityManager->flush();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.response' => 'onKernelResponse',
        ];
    }
}
