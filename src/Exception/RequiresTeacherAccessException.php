<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class RequiresTeacherAccessException extends AccessDeniedHttpException
{
}