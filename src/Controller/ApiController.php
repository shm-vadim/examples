<?php

namespace App\Controller;

use App\Security\VkAuthenticator as VkAuth;
use   Psr\Container\ContainerInterface as Con;
use App\Repository\{
SessionRepository,
TransferRepository,
ProfileRepository as PR,
UserRepository,
};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{
Request,
Response,
JsonResponse,
};
use App\Service\JsonLogger as L;

    /**
     * @Route("/api")
     */
class ApiController extends Controller
{
use BaseTrait;

    /**
     * @Route("/request/yandex", name="api_request_yandex", methods="POST")
     */
public function request(Request $req, UserRepository $uR, TransferRepository $tR, L $l) {
$r=$req->request;

$label=$r->get("label");
$wa=$r->get("withdraw_amount");
$un=$r->get("unaccepted");

$code=400;
$an=["error"=>"No transfer with $label label"];
$t=$tR->findOneBy(["label"=>$label, "held"=>false]);
$u= $t ? $t->getUser() : null;

if ($u && $un != "true") {
$u->addMoney($wa);
$t->setMoney($wa)
->setHeldTime(new \DateTime)
->setHeld(true);
$this->em()->flush();
$code=200;
$an["error"]=false;
}

return new JsonResponse($an, $code);
}

    /**
     * @Route("/login/vk", name="api_login_vk")
     */
public function vk(Request $req, Con $con) {
dd($req->query->all(), 123);
return $this->redirectToRoute("fos_user_security_login");
}

    /**
     * @Route("/register/vk", name="api_register_vk")
     */
public function registerVk(Request $req, VkAuth $vkAuth, UserRepository $uR) {
$d=$vkAuth->getCredentials($req);
if ($vkAuth->checkCredentials($d)) $uR->findOneByVkCredentialsOrNew($d);
return $this->redirectToRoute("api_login_vk", $d);
}
}
