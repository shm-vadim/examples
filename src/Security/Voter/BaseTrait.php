<?php

namespace App\Security\Voter;

trait BaseTrait {
private function checkRight($p, $o, $t) {
$m=getMethodName(strtolower($p), "can");
return method_exists($this, $m) ? $this->$m($o, $t) : false;
}
}