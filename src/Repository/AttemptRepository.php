<?php

namespace App\Repository;

use App\Service\ExampleManager as ExMng;
use App\Service\UserLoader;
use App\Entity\Attempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AttemptRepository extends ServiceEntityRepository
{
use BaseTrait;
private $exR;
private $ul;
private $sR;
private $uR;
private $ch;

    public function __construct(RegistryInterface $registry, ExampleRepository $exR, UserLoader $ul, SessionRepository $sR, UserRepository $uR, AuthorizationCheckerInterface $ch)
    {
        parent::__construct($registry, Attempt::class);
$this->exR=$exR;
$this->ul=$ul;
$this->sR=$sR;
$this->uR=$uR;
$this->ch=$ch;
    }

public function findLastActualByCurrentUser() {
$att=$this->findLastByCurrentUser();
return ($this->ch->isGranted("SOLVE", $att)) ? $att : null;
}

public function findLastByCurrentUser() {
$ul=$this->ul;
$w=(!$ul->isGuest()) ? "s.user = :u" : "a.session = :s";
$q=$this->q("select a from App:Attempt a
join a.session s
where $w
order by a.addTime desc");
!$ul->isGuest() ? $q->setParameter("u", $ul->getUser()) : $q->setParameter("s", $this->sR->findOneByCurrentUserOrGetNew());
$att=$this->v($q);
return $att;
}

public function getTitle($att) {
return "Попытка №".$this->getNumber($att);
}

public function getNumber($att) {
return $this->v(
$this->q("select count(a) from App:Attempt a
join a.session s
where s.user = :u and a.addTime <= :dt
")->setParameters(["u"=>$this->ul->getUser(), "dt"=>$att->getAddTime()])
);
}

public function getFinishTime($att) {
return $this->dt($this->v(
$this->q("select e.answerTime from App:Attempt a
join a.examples e
where a = :att and e.answerTime is not null
order by e.answerTime desc
")->setParameter("att", $att)
)) ?: $att->getAddTime();
}

public function getSolvedExamplesCount($att) {
return $att->getSettings()->isDemanding() ? $this->v(
$this->q("select count(e) from App:Attempt a
join a.examples e
where e.isRight = true and a = :a
")->setParameters(["a"=>$att, ])
) : $this->getAnsweredExamplesCount($att);
}

public function getAnsweredExamplesCount($att) {
return $this->v(
$this->q("select count(e) from App:Attempt a
join a.examples e
where e.answer is not null and a = :a
")->setParameters(["a"=>$att, ])
);
}

public function getErrorsCount($att) {
return $this->exR->count([
"attempt"=>$att,
"isRight"=>false,
]);
}

public function getRating($att) {
return ExMng::rating($att->getExamplesCount(), $this->getRongExamplesCount($att));
}

public function countByCurrentUser() {
return $this->v($this->q("select count(a) from App:Attempt a
join a.session s
join s.user u
where u = :u")
->setParameter("u", $this->ul->getUser()));
}

public function findAllByCurrentUser() {
return $this->q("select a from App:Attempt a
join a.session s
join s.user u
where u = :u")
->setParameter("u", $this->ul->getUser())
->getResult();
}

public function getNewByCurrentUser() {
$u=$this->ul->getUser()->setER($this->uR);
$att=(new Attempt())
->setSession($this->sR->findOneByCurrentUserOrGetNew())
->setSettings($u->getCurrentProfile()->getInstance());
$em=$this->em();
$em->persist($att);
$em->flush();
return $att;
}

public function hasPreviousExample($att) {
return !!$this->exR->findLastByAttempt($att);
}

public function getData($att) {
$exR=$this->exR;
if (!$ex=$exR->findLastUnansweredByAttempt($att)) return false;
$ex->setER($exR);
$att->setEr($this);

return [
"ex"=>[
"num"=>$ex->getNumber(),
"str"=>"$ex",
],
"errors"=>$att->getErrorsCount(),
"exRem"=>$att->getRemainedExamplesCount(),
"limTime"=>$att->getLimitTime()->getTimestamp(),
];
}

public function getRemainedExamplesCount($att) {
$c=$att->getSettings()->getExamplesCount() - $this->getSolvedExamplesCount($att);
return $c > 0 ? $c : 0;
}

public function getRemainedTime($att) {
$t=$att->getLimitTime()->getTimestamp() - time();
return $t > 0 ? $t : 0;
}

public function getAllData($att) {
$d=$att->setER($this)->getData();

foreach (strToArr("title number finishTime solvedExamplesCount answeredExamplesCount errorsCount rating") as $k) {
$d[$k]=$att->__call($k);
}

return $d;
}

public function findAllByCurrentUserAndLimit($s, $l) {
return $this->q("select a from App:Attempt a
join a.session s
join s.user u
where u = :u
order by a.addTime desc")
->setParameters(["u"=>$this->ul->getUser()])
->setFirstResult($s)
->setMaxResults($l)
->getResult();
}

public function getSolvedTime($att) {
return $this->dts($this->getFinishTime($att)->getTimestamp() - $att->getAddTime()->getTimestamp());
}

public function getAverSolveTime($att) {
$c=$this->getSolvedExamplesCount($att);
return $this->dts(
$c ? round($this->getSolvedTime($att)->getTimestamp() / $c) : 0
);
}

public function getRongExamplesCount($att) {
return $this->getErrorsCount($att) + $att->getExamplesCount() - $this->getSolvedExamplesCount($att);
}
}