<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\{
Session\SessionInterface,
RequestStack,
};
use App\Service\UserLoader;
use App\Repository\{
SessionRepository, 
IpRepository
};
use App\Entity\{
Ip,
Visit,
};

class ResponseSubscriber implements EventSubscriberInterface
{
private $sR;
private $ipR;
private $req;
private $ul;

public function __construct(SessionRepository $sR, RequestStack $rs, UserLoader $ul, IpRepository $ipR) {
$this->sR=$sR;
$this->ipR=$ipR;
$this->req=$rs->getMasterRequest();
$this->ul=$ul;
}

    public function onKernelResponse(FilterResponseEvent $event)
    {
$req=$this->req;
$em=$this->sR->em();

if ($req && $event->isMasterRequest() && $s=$this->sR->findOneByCurrentUser()) {
$v=(new Visit)
->setUri($req->getRequestUri())
->setRouteName($req->attributes->get("_route"))
->setSession($s);
$em->persist($v);

$s->setLastTime(new \DateTime);

$u=$this->ul->getUser();
$ip=$this->ipR->findOneByIpOrNew($req->getClientIp());
if ($ip) {
$u->addIp($ip);
$s->setIp($ip);
}

$em->flush();
}
    }

    public static function getSubscribedEvents()
    {
        return [
           'kernel.response' => 'onKernelResponse',
        ];
    }
}
