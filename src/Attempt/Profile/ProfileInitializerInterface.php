<?php

namespace App\Attempt\Profile;

use App\Entity\Profile;

interface ProfileInitializerInterface
{
    public function initializeNewProfile(): Profile;
}
