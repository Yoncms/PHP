<?php
//
//-+--------------------------------------------
// | 系统的初始化文件
//-+--------------------------------------------
//

namespace yoncms\init;

class init{
  private static $inc = null;
  protected final function __construct(){
    define( 'YONCMS', uniqid( '', true ) );
		$this->inits();
    $this->setDir();
    $this->findConst();
    $this->mLoad();
    $this->setConst();
  }
  public static function inNew(){
    if( !self::$inc instanceof self )
      self::$inc = new self();
    return self::$inc;
  }

  private function inits(){
    if( !session_id() )
      session_start();
    //文件编码
    header( 'content-type:text/html;charset=utf-8' );
    date_default_timezone_set('PRC');
    $this->Debug();
  }
  private function setDir(){
    $this->setPath();
    $this->setHost();
  }
  //本地根目录
  private function setPath(){
    $dirname = dirname( __FILE__ );
    $dir = realpath( $dirname . '/../');
    define( 'ROOT', str_replace( '\\', '/', $dir ) . '/' );
    return;
  }
  //* 判断当前是http或https协议
  private function http(){
    if(isset($_SERVER["HTTP_X_CLIENT_SCHEME"]))
      $scheme = $_SERVER["HTTP_X_CLIENT_SCHEME"];
    elseif(isset($_SERVER["REQUEST_SCHEME"]))
      $scheme = $_SERVER["REQUEST_SCHEME"];
    else
      $scheme = "http";
    return $scheme . '://';
  }
  //服务器根目录
  private function setHost(){
    $http = $this->http();
    /**
    * DOCUMENT_ROOT取出的地址，盘符是小写的，有的服务器
    * str_replace时是区分大小写的；当然也可以使用str_ireplace
    * 函数进行替换，它是不区分大小写的
    */
    $root = $_SERVER['DOCUMENT_ROOT'];
    /**
    * 取服务器的地址时，不要用SERVER_NAME而用HTTP_HOST；
    * 因为当服务器没有域名只有ip时，或者端口不是默认的
    * 80时，SERVER_NAME就会出错
    */
    define( 'HOST', str_ireplace( $root, $http . $_SERVER['HTTP_HOST'], ROOT ) );

    //这里相当于/2004nginx/，如果系统直接安装在
    //服务器的根目录下，就相当于/
    define( 'ROOT_DIR', str_ireplace( array('\\', $root), array('/', ''), ROOT ) );
    return;
  }
  //设置常量
  private function setConst(){
    $file = ROOT . 'public/configConst.php';
    if( file_exists( $file ) )
      $arr = include_once( $file );
    //key与val组成新的数组，可以的元素当键，val的当值
    setConst( $arr );
    return false;
  }
  //权限和系统配置
  private function findConst(){
    $errFile = ROOT_DIR . 'static/err.htm';
    //非法访问
    defined( 'YONCMS' ) ||
      Exit( header( "refresh:0;url=".$errFile  ) );
    return false;
  }
  //开发模式或发布模式
  private function Debug(){
    //参数为1时，会报错，为0时，如果有错误直接报500
    ini_set('display_errors', 1);
    if( defined( 'DEBUG' ) )
      error_reporting( 2047 );//开发模式，报错
    else
      error_reporting( 0 );//正式上线模式，不报错
    return false;
  }
  //加载文件
  private function mLoad(){
    $file = ROOT.'public/configFunc.php';
    $arr = include_once $file;
    $this->loadFoo( $arr );
    return;
  }
  private function loadFoo( $arr=null ){
    if( !$arr ) return;
    foreach( $arr as $k=>$v ){
      foreach( $v as $vv ){
        $file = ROOT . $k . '/' . $vv . '.php';
        if( file_exists( $file ) )
          include_once( $file );
      }
    }
   return;
  }
}

return init::inNew();








