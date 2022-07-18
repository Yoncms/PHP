<?php

namespace yoncms\tool;

use yoncms\publics\common;

class getLang extends common{
  private function getData(){
    return config( 'Lang' );
  }
  //数据解码
  public function deCode( $key=null ){
    if( !$key ) return;
    $data = $this->getData();
    if( array_key_exists( $key, $data ) )
      //deCode会递归进行解码，除了数字
      return deCode( $data[$key] );
    return;
  }
  //数据解码
  public function __get( $key=null ){
    if( !$key ) return;
    $data = $this->getData();
    if( array_key_exists( $key, $data ) )
      //deCode会递归进行解码，除了数字
      return $data[$key];
    return;
  }
}

