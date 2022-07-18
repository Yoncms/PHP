<?php

namespace yoncms\tool;

class arg_compile extends pubCompile{

  //* 调用函数的规则
  public function parser( ){
    $str = substr( $this->returnData(), 4 );
    $arr = explode( ',', $str );
    $str = '(';
    foreach( $arr as $v ){
      if( substr( $v, 0, 1 ) == '$' )
        $str .= PC( 'var', $v )->parser() . ',';//参数是变量
      else
        $str .= is_numeric( $v ) ? $v : '\''.$v.'\',';
    }
    return rtrim( $str, ',' ).')';
  }
}





