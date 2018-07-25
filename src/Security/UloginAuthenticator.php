<?php

namespace App\Security;

use   Psr\Container\ContainerInterface as Con;
use Symfony\Component\HttpFoundation\{
Request,
RedirectResponse,
};
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use App\Repository\{
UserRepository as UR,
};

class UloginAuthenticator extends AbstractGuardAuthenticator
{
private $con;
private $uR;

public function __construct(Con $con, UR $uR)
{
$this->con=$con;
$this->uR=$uR;
}

    public function supports(Request $request)
    {
return ($request->attributes->get("_route") == "api_ulogin_login");
    }

    public function getCredentials(Request $request)
    {
try {
$r=$request->request;
$token = $r->get('token',
$request->query->get("token"));
$s=file_get_contents('http://ulogin.ru/token.php?token=' . $token . '&host=' . $_SERVER['HTTP_HOST']);
                    $d= json_decode($s, true);
$d+=[
"token"=>$token,
"username"=>$d["network"]."-".$d["uid"]
];
} catch(\Exception $ex) {
return [];
}
//if ($request->isMethod("GET")) dd("");
return $d;
    }

    public function getUser($d, UserProviderInterface $p)
    {
$u=$d["username"] ?? null;
return $u ? ($p->loadUserByUsername($u)) : null;
    }

    public function checkCredentials($d, UserInterface $user=null)
    {
return !!$d;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
return new RedirectResponse("/login");
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
return new RedirectResponse("/");
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        // todo
    }

    public function supportsRememberMe()
    {
return true;
    }
}