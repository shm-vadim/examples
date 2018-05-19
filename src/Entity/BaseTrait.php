<?php

namespace App\Entity;

Use App\DT;

trait BaseTrait {
use \App\BaseTrait;

private $er;

public function setER($er) {
$this->er=$er;
return $this;
}

public function __call($v, $p=[]) {
if ($gs=$this->getSetter($v, $p)) return $gs["ret"];
$m=entityGetter($v);
return $this->er->$m($this);
}

private function getSetter($method, $p) {
foreach (['get', 'set'] as $action) {
if (preg_match("#^$action(.+)$#", $method, $arr)) {
$v=lcfirst($arr[1]);
if (!$g=$action == 'get') $this->$v=$p[0];
return ["ret"=>$g ? $this->$v : $this];
}
}

return false;
}

}