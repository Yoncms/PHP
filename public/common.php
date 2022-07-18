<?php
//
//++++++++++++++++++++++++++++++++++++
// | php的命名空间和类名最好不要重名 |
//++++++++++++++++++++++++++++++++++++
//
namespace yoncms\publics;

class common{
  private static $ins = array();

  private $data = null;

  protected function __construct(){}

  public static function inNew( $param=null  ){
    $gcc = get_called_class();
    if( !isset( self::$ins[$gcc] ) || !self::$ins[$gcc] instanceof $gcc )
      //对象不存在则创建对象
      self::$ins[$gcc] = new $gcc();
    //给属性赋值
    self::$ins[$gcc]->data = $param;
    return self::$ins[$gcc];
  }
  public function returnData(){
    return $this->data;
  }
}













