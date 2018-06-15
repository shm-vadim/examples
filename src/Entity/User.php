<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use App\DT;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User extends BaseUser
{
use DTTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Session", mappedBy="user", orphanRemoval=true)
     */
    private $sessions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Profile", mappedBy="author", orphanRemoval=true)
     */
    private $profiles;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Profile", inversedBy="users")
     */
    private $profile;

    /**
     * @ORM\Column(type="smallint")
     */
    private $money=0;

    /**
     * @ORM\Column(type="integer")
     */
    private $allMoney=0;

    /**
     * @ORM\Column(type="datetime")
     */
    private $addTime;

    /**
     * @ORM\Column(type="datetime")
     */
    private $limitTime;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Code", mappedBy="user")
     */
    private $codes;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $ips;

    public function __construct()
    {
$this->roles=[];
        $this->sessions = new ArrayCollection();
        $this->profiles = new ArrayCollection();
$l=TEST_DAYS;
$this->limitTime=(new \DateTime())->add(new \DateInterval("P{$l}D"));
$this->addTime=new \DateTime;
$this->codes = new ArrayCollection();
$this->money=DEFAULT_MONEY;
$this->ips=[];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAddTime(): ?\DateTimeInterface
    {
        return $this->dt($this->addTime);
    }

    public function setAddTime(\DateTimeInterface $addTime): self
    {
        $this->addTime = $addTime;

        return $this;
    }

    /**
     * @return Collection|Session[]
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): self
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions[] = $session;
            $session->setUser($this);
        }

        return $this;
    }

    public function removeSession(Session $session): self
    {
        if ($this->sessions->contains($session)) {
            $this->sessions->removeElement($session);
            // set the owning side to null (unless already changed)
            if ($session->getUser() === $this) {
                $session->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Profile[]
     */
    public function getProfiles(): Collection
    {
        return $this->profiles;
    }

    public function addProfile(Profile $profile): self
    {
        if (!$this->profiles->contains($profile)) {
            $this->profiles[] = $profile;
            $profile->setAuthor($this);
        }

        return $this;
    }

    public function removeProfile(Profile $profile): self
    {
        if ($this->profiles->contains($profile)) {
            $this->profiles->removeElement($profile);
            // set the owning side to null (unless already changed)
            if ($profile->getAuthor() === $this) {
                $profile->setAuthor(null);
            }
        }

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function getAllMoney(): ?int
    {
        return $this->allMoney;
    }

    public function getMoney(): ?int
    {
        return $this->money;
    }

    public function setMoney(int $money): self
    {
        $this->money = $money;

        return $this;
    }

    public function getLimitTime(): ?\DateTimeInterface
    {
        return $this->dt($this->limitTime);
    }

    public function setLimitTime(\DateTimeInterface $limitTime): self
    {
        $this->limitTime = $limitTime;

        return $this;
    }

public function getRemainedTime() {
$d=$this->getLimitTime()->getTimestamp() - time();
return DT::createFromTimestamp($d > 0 ? $d  : 0);
}

/**
 * @return Collection|Code[]
 */
public function getCodes(): Collection
{
    return $this->codes;
}

public function addCode(Code $code): self
{
    if (!$this->codes->contains($code)) {
        $this->codes[] = $code;
        $code->setUser($this);
    }

    return $this;
}

public function removeCode(Code $code): self
{
    if ($this->codes->contains($code)) {
        $this->codes->removeElement($code);
        // set the owning side to null (unless already changed)
        if ($code->getUser() === $this) {
            $code->setUser(null);
        }
    }

    return $this;
}

public function addMoney(int $m) {
$this->allMoney+=$m;
return $this->setMoney($this->getMoney() + $m);
}

public function getIps(): ?array
{
    return is_array($this->ips) ? $this->ips : [];
}

public function setIps(array $ips): self
{
    $this->ips = $ips;

    return $this;
}

public function addIp($ip) {
if (!in_array($ip, $this->getIps())) $this->ips[]=$ip;
return $this;
}
}
