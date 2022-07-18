<?php

namespace yoncms\tool;
//参数规则

class str_compile extends pubCompile{

  //非数字的字符串
  public function parser( ){

    $str = $this->returnData();

    $str = substr( $str, 4 );

    return '\''.$str .'\'';
  }

}










