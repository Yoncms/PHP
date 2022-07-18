<?php
//
//-+-----------------------------------------
// | 自定义自动加载函数的注册；
// | 注册的函数是类里创建的函数
//-+-----------------------------------------
//
namespace yoncms\init;

return new class{
  //配置数组
  private $data = null;
  //生成的函数数组
  private $arr = null;
  public function __construct(){
    //加载配置数据
    $file = ROOT . 'public/configLoad.php';
    if( file_exists( $file ) )
      $this->data = include_once $file;
    //创建函数
    $this->createFunc();
    //注册函数
    $this->register();
  }
  //创建要注册的函数
  private function createFunc( ){
    //没有数据表示不需要创建函数
    if( empty( $this->data ) ) return;
    foreach( $this->data as $dir ){
      $dir = $dir ? $dir . '/' : '';
      $this->arr[] = function( $class )use( $dir ){
        //有目录时，目录后面加斜杠，否则为空
        //因为在命名空间里类名会是加上命名空间的；
        //所以在加载时要去掉命名空间，用basename就可以
        $file = ROOT . $dir . basename( $class ) . '.php';
        if( file_exists( $file ) ){
          include_once( $file );
          return;
        }
      };
    }
    return ;
  }
  //注册函数
  private function register(){
    $arr = $this->arr;
    foreach( $arr as $function )
      spl_autoload_register( $function );
    return ;
  }
};

