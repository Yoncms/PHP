<?php

namespace yoncms\tool;
//参数规则

class var_compile extends pubCompile{

  public function parser( ){   //有.的是数组，.后面是数组的键
    $v = $this->returnData();
    if( !strpos( $v, '.' ) )
      return $this->noDot( $v );
    return $this->hasDot( $v );
  }

  private function noDot( $arg ){
    if( substr( $arg, 0, 1 ) == '$' )
      return $arg;
    return '$'.$arg;
  }

  private function hasDot( $arg ){
    $arr = explode( '.', $arg );
    $elm = $this->noDot( array_shift( $arr ) );
    foreach( $arr as $v ){
      //是数字和变量时不加引号
      if( is_numeric( $v ) || strstr( $v, '$' ) )
        $elm .= '[' . $v . ']';
      else
        $elm .= '[\''.$v.'\']';
    }
    return $elm;
  }
}










