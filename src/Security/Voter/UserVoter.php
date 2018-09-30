<?php

namespace App\Security\Voter;

use App\Service\AuthChecker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\User;
use App\Service\UserLoader;

class UserVoter extends Voter
{
    use BaseTrait;
    private $ul;
    private $authChecker;

    public function __construct(UserLoader $ul, AuthChecker $authChecker)
    {
        $this->ul = $ul;
        $this->authChecker = $authChecker;
    }

    protected function supports($attribute, $subject)
    {
        return             ($subject instanceof User or null === $subject) && $this->hasHandler($attribute);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->checkRight($attribute, $subject ?? $this->ul->getUser(), $token);
    }

    private function isAccountPaid()
    {
        $u = $this->subject;

        return !$this->ul->isGuest();
    }

    private function hasPrivAppointProfiles()
    {
        return $this->authChecker->isGranted('ROLE_USER', $this->subject);
    }
}
