<?php
//* url地址转换成模块、类、方法
namespace yoncms\init;

use yoncms\publics\common;

//* 运行控制器文件
class Run extends Common{
  //* 从地址栏里提取get数据
  private $get = null;

  //* 从地址栏里提取模块、控制器、方法
  private $data = null;

  //* 从地址栏里提取传递来参数，【可能没有】
  private $param = null;

  //* 指向view的文件的路径
  private $dir = null;

  //* 指向view的文件的名字
  private $fname = null;

  //* 存储模板名、编译后的php文件名、编译后的缓存文件名
  //* private $fileName = array();

  //* 传给view文件的数据，【可能没有】
  private $content = array();

  //* 数据表名
  private $tab = null;

  //* 编译文件的更新时间，默认实时更新
  private $expire = 0;

  //* 设置数据表名
  public function setTab( $tab=null ){
    return $this->tab = $tab;
  }

  //* 文件更新间隔时间
  public function expire( ){
    $time = config( 'Expire' );
    if( $time != 0 )
      $this->expire = $time * 60;
    return $this->expire;
  }

  public function startData(){
    $data = $this->classFile();
    if( !isset( $data[2] ) )
      return eCode( 'moMothed' );   //* return Error();
    $foo = $data[2];
    //* 判断类是否存在，不存在就不必创建对象了
    if( !class_exists( $data[0] ) )
      return eCode( 'noMod' );   //* return Error();
    else
      //* $obj = $data[0]::inNew();
      //* 可以直接写成上面一句的代码
      $obj = inNew( $data[0] );

      //* 向obj对象添加expire属性，即缓存的有效时间
      $obj->expire = $this->expire();

      //* 向obj对象添加data属性，即模块、类、方法
      //* 以方便后面的获取
      $obj->data = $this->data;

    $obj->setFile( $data[1], $foo );
    return;
  }

  //生成类名、方法名、指向view文件的路径和名字
  private function classFile(){
    $this->getPath();
    $data = $this->data;
    if( $data[0] == ''|| !isset( $data[2] ) )
      return eCode( 'errRequest' );   //return Error();
    $mod = $data[0]; //模块
    $ctrl = $data[1]; //控制器
    $foo = $data[2]; //方法
    $ctrl =  '\\yoncms\\' . $mod . '\\' . $ctrl;
    return array( $ctrl, $mod, $foo );
  }

  //设置view/fetch指向文件的路径和名字
  private function setFile( $dir, $name=null ){
    if( $dir )
      $this->dir = $dir;
    if( $name )
      $this->fname = $name;
    //判断类里是否存在$foo方法，没有就不必执行了
    if( !method_exists( $this, $name ) )
      return eCode( 'noMethod' );   //return Error();
    else
      $this->$name();
    return;
  }
  //获取地址栏里的信息
  private function getPath(){

    $pathUri = getUri();
    $this->data = $pathUri[0];
    $this->param = $this->getParam( $pathUri[1] );
  }

  // 带有参数的时候，参数部分的处理（转成数组）
  private function getParam( $arr ){
    $arrs = array();
    foreach( $arr as $k=>$v ){
      //前一个单元是键，后一个单元是值
      if( $k % 2 != 0 )
        $arrs[ $arr[ $k - 1 ] ] = $v;
    }
    return $arrs;
  }

  private function comTime( $fname=null, $n=2 ){
    // $n只能是1或2
    if( $n!=1 && $n!=2 )
      return;
    if( substr( $fname, -5 ) == 'erhtm' )
      $n = 1;
    $FN = $this->fileName( $fname );

    // $n=2是判断缓存文件，$n=1是判断php文件
    $compile = $FN[ $n ];

    $expire = $this->expire;
    $timer = file_exists( $compile ) ? filemtime( $compile ) : 0;
    //* timer如果等于0就是文件不存在
    $expireTime = $timer + $expire;

    if( $timer === 0 || $expire === 0 || $expireTime <= TIME )
      return false;
    //* 返回true就是无需编译
    return true;

  }
  //* 排除templates目录下的公共模板文件，函数返回的是字符串
  private function noTpl( $name=null ){
    //* templates目录
    $f = scandir( TPL );
    $arr = array();

    foreach( $f as $v ){
      if( !is_dir( $v ) ){
        if( substr( $v, -4 ) == '.tpl' )
          $arr[] = substr( $v, 0, -4 );
      }
    }
    $dir = $this->dir . '/';
    //* 如果参数是空的，就是默认的控制器+模块
    if( $name === null )
      return $dir . $this->fname;
    //* 如果在templates目录下（如header和footer等），
    //* 或者本身就带目录的，就不要加路径
    if( in_array( $name, $arr ) || strstr( $name, '/' )||substr( $name, -5 ) == 'erhtm' )
      return $name;
    return $dir . $name;
  }

  public function getMcm(){
    $mcm = implode( '', $this->data );
    $param = '';
    if( $this->operParam() ){
      $param = $this->operParam();
      if( isset( $param['p'] ) )
        $mcm .= $param['p'];
    }
    return $mcm;
  }

  private function fileName( $name=null ){
    //* tpls是模板文件名和相对地址
    $tpls = $this->noTpl( $name );
    if( substr( $name, -5 ) == 'erhtm' ){
      $tpls = $name;
      $phpCom = ROOT . 'phps/' . $name;
      $viewCom = '';
    }else{
      //* tpl是模板文件的完整地址
      $tpl = TPL . $tpls . '.tpl';
      //* phpCom是不含php编译文件名字的路径
      $phpCom = changeUrl( $tpl );

      $phpCom .= substr( md5( $tpl ), -20 );

      $tpl .= implode( '', $this->data );
      //* 如果有参数，缓存文件还要带参数
      $param = $this->operParam();
      //* 带参数页的文件名，为了区别，在后面添加带参数
      $param = !!$param ? implode( '', $param ) : '';
      //* 获取view编译文件的路径
      $viewCom = changeUrl( $tpl, true );
      //* 为了区分不同参数的页面，把参数加入到view文件名
      $viewCom .= substr( md5( $tpl.$param ), -20 );
    }
    //* tpls模板文件，PHPS文件：php，VIEW文件：view
    return array( $tpls, $phpCom, $viewCom );
  }

  //* 向外提供是否重新编译缓存文件的接口
  public function viewCompile( $fname=null ){
    return $this->comTime( $fname );
  }

  //* 判断是否重新编译php文件
  public function phpCompile( $fname=null ){
    return $this->comTime( $fname, 1 );
  }

  //* 读取缓存数据并写入缓存文件
  private function obContent( $file, $viewFile ){
    //* 开启缓存
    ob_start();
    if( $this->content ){
      foreach( $this->content as $k=>$v )
        ${$k} = $v;
    }
    //* 加载php文件
    include $file;
    if( !$viewFile ) return;
    $content = ob_get_contents();
    ob_end_clean();
    ob_end_flush();
    echo $content;
    file_put_contents( $viewFile, $content );
    return;
  }

  //* 参数是编译后的php文件地址加文件名
  private function comView( $fname ){
    $file = $this->fileName( $fname );
    //* vFile是缓存文件
    $viewFile = $file[2];
    if( $this->comTime( $fname ) ){
      include $viewFile;
      return $viewFile;
    }
    //* 创建缓存文件
    makeDir( $viewFile );
    //* 把数据写入缓存文件
    $this->obContent( $file[1], $viewFile );
    return;
  }

  //* 生成编译后要写入的文件的名称
  private function md_file( $tpls=null ){
    $file = $this->fileName( $tpls );
    //* php编译文件
    $php = $file[1];
    //* 如果编译文件不存在或存在但是更新时间超过指定则编译
    if( !$this->comTime( $php ) ){
      //* 传入的数据如果不是数组直接替换成变量值
      $content = $this->content;
      $arr = array();
      foreach( $content as $k=>$v ){
        if( !is_array( $v ) ){
          $arr[$k] = $v;
          unset( $content[$k] );
        }
      }
      //* 非数组$arr直接传入到inData
      CP( $content )->parser( $file[0], $arr );
    }
    if( file_exists( $php ) )
      return $php;
    return null;
  }
  //* 编译文件头和底部
  private function headFoot( $main=null ){
    if( $this->content ){
      foreach( $this->content as $k=>$v )
        ${$k} = $v;
    }
    //* 文件头部，包括导航条
    $head = $this->md_file( 'headerhtm' );
    if( $head )
      include_once( $head );
    //* if( $this->md_file( $main ) )
    $this->comView( $main );
    //* 文件底部，包括版权
    $foot = $this->md_file( 'footerhtm' );
    if( $foot )
      include_once( $foot );
  }
  //* 只加载veiw文件，不加载header和footer
  public function fetch( $name = null ){
    //* 如果没有参数指向与方法同名的文件，否则就是自己指定的
    //* 如果view文件需要变量，则content属性不为null
    if( $this->content ){
      foreach( $this->content as $k=>$v )
        ${$k} = $v;
    }
    $this->md_file( $name );

    $this->comView( $name );

    return ;
  }

  //* 兼具加载header和footer
  public function View( $main=null ){

    $this->md_file( $main );
    //* 如果是view，则包括了header和footer
    $this->headFoot( $main );

    return;
  }

  //* 如果url里传递了参数，进行接收和处理
  public function operParam(){
    //* 先执行setArr方法，保证param被赋值
    $this->getPath();
    return $this->param ?? array();
  }

  //* 给view文件传递变量，参数1是变量名，2是变量值
  public function assign( $key, $val ){
    $this->content[ $key ] = $val;
    return ;
  }

}










