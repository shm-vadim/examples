<?php

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
namespace App\Controller;

use App\Service\MathMng;
use Doctrine\ORM\EntityManagerInterface as EM;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Attempt;
use App\Repository\AttemptRepository as AttR;
use App\Repository\ExampleRepository as ExR;
use Symfony\Component\Routing\Annotation\Route;

 /**
* @Route("/attempt")
*/
class AttemptController extends MainController {
private $attR;

public function __construct(AttR $attR) {
$this->attR=$attR;
}

/**
* @Route("/", name="attempt_index")
*/
public function index(AttR $r) {
return $this->render('attempt/index.html.twig', [
"attempts"=>$r->findAllByCurrentUser(),
"attR"=>$r,
]);
}

/**
*@Route("/{id}/show", name="attempt_show", requirements={"id": "\d+"})
*/
public function show(Attempt $att, ExR $exR, AttR $attR) {
$this->denyAccessUnlessGranted('VIEW', $att);
return $this->render('attempt/show.html.twig', [
"att"=>$att,
"examples"=>$exR->findByAttempt($att),
"exR"=>$exR,
"attR"=>$attR,
]);
}

/**
*@Route("/{id}", name="attempt_solve", requirements={"id": "\d+"})
*/
public function solve(Attempt $att, ExR $exR, AttR $attR, \Symfony\Component\HttpFoundation\Session\Session $s) {
    if (!$this->isGranted("SOLVE", $att)) {
if ($this->isGranted("VIEW", $att)) return $this->redirectToRoute('attempt_show', ['id'=>$att->getId()]);
else throw new AccessDeniedException();
}
return $this->render('attempt/solve.html.twig', [
"jsParams"=>[
"attData"=>$att->setER($attR)->getData(),
'answerRoute'=>$this->generateUrl('attempt_answer', ['id'=>$att->getId()])
],
"att"=>$att,
]);//
}

/**
*@Route("/last", name="attempt_last")
*/
public function last(AttR $attR) {
if ($att=$attR->findLastByCurrentUser()) return $this->redirectToRoute('attempt_solve', ['id'=>$att->getId()]);
return $this->render("attempt/last.html.twig");
}

/**
*@Route("/new", name="attempt_new")
*/
public function new(AttR $attR) {
return $this->redirectToRoute('attempt_solve', ['id'=>$attR->getNewByCurrentUser()->getId()]);
}

/**
*@Route("/{id}/answer", name="attempt_answer")
*/
public function answer(Attempt $att, Request $request, MathMNG $mm, ExR $exR, EM $em) {
if (!$this->isGranted("ANSWER", $att)) return $this->json(['finish'=>true]);

$ex=$exR->findLastByAttemptOrNull($att);
$an=(float) $request->request->get('answer');
$isR=$an === (float) $mm->solveExample($x->getFirst(), $ex->getSecond(), $ex->getSign());
$ex->setAnswer($an)->setIsRight($isR);
$em->flush();

$finish=!$this->isGranted("ANSWER", $att);
if (!$finish) $exR->getNewByAttempt($att);
return $this->json([
'isRight'=>$isR, 
'finish'=>$finish,
'attData'=>$this->getDataByAtt($att),
]);//
}

private function getDataByAtt($att) {
return [];
}

}