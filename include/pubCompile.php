<?php

namespace yoncms\tool;

use yoncms\publics\common;

class pubCompile extends common{

  public function parser(){}

  public function compile( pubCompile $obj ){
    return $obj->parser();
  }


}




