<?php
//
// ++++++++++++++++++++++++++++++++++++++++++
// + 本文件是编译文件，就是把模板文件编译成，
// + PHP文件，而跟缓存文件的创建没有任何关系；
// + 包括获取模板文件需要编译的内容，并对他们
// + 进行编译生成php文件
// ++++++++++++++++++++++++++++++++++++++++++
//
namespace yoncms\tool;

use yoncms\publics\common;

class compile extends common{

  //* 为了去掉模板文件的重复编译
  private $flag = array();

  //* 公共规则
  private $init = array();

  //* 要编译文件的规则
  private $config = array();

  //* 判断是否目录存在，如果不存在则创建，如果存在写入数据
  private function mDir( $file, $content ){
    //* 把替换好的内容写入php文件
    if( !strstr( substr( $file, 2 ), ':' ) ){
      //* 如果php文件不存在，先进性创建
      makedir( $file );
      //* 内容写入文件
      file_put_contents( $file, $content );
    }
    return ;
  }

  //* 自动设置config，获取需要替换的内容
  private function getConfig( $content ){
    //* 系统默认的编译规则
    $this->init = config( 'comp' );
    //* 匹配需要替换的内容
    $reg = '/\{([a-zA-Z0-9_$\/\., ]+)?\}/';

    $Arr = array();
    //* 结果是两个数组，前一个是符合正则的全部内容，
    //* 后一个是正则小括号里匹配的内容
    if( preg_match_all( $reg, $content, $res ) ){

      //* 把两者合并成一个数组，键是加了花括号的
      $Arr = array_combine( $res[0], $res[1] );
      //* 删除掉初始化数据里（默认规则）已有的项目
      foreach( $Arr as $k=>$v ){
        //* 如果初始化数据里有的项目，就不需要添加到config
        if( isset( $this->init[$k] ) )
          unset( $Arr[$k] );
      }
      //* 把剩下的添加到规则
      $this->config = $Arr;
    }
    return ;
  }

  //* 数据替换
  private function replace( $content ){
    //*
    //* 替换字符串，参数是要替换的内容；
    //* 就是把{*****}，替换成相应的形式
    //*
    $config = $this->config + $this->init;
    //* 待编辑的数据
    $old = array();
    //* 编辑后的数据
    $new = array();
    foreach( $config as $k=>$v ){
      //* 添加到数组里，然后一次性替换
      $old[] = $k;
      //* 如果变量值是通过传进来的，就直接替换成该值
      if( isset( $this->inData[$k] ) ){
        $new[] = $this->inData[$k];
      //* 带有指定字符串的直接不用替换
      }elseif( isset( $this->init[$k] ) ){
        $new[] = $this->init[$k];
      }else{
        $new[] = $this->comPublic( $k );
      }
    }
    return str_replace( $old, $new, $content );
  }

  public function comPublic( $arg ){
    //*
    //* 参数是待编译的数据以及替换的规则
    //*
    $vars = substr( $arg, 1, 4 );
    //* 如果项目里没有_，就是变量，通过var类进行处理
    $obj = !strstr( $arg, '_' ) ? 'var' : substr( $arg, 1, 3 );
    if( isset( $this->config[$arg] ) )
      $arg = $this->config[$arg];
    //* 如果是加载文件，被加载的文件也要进行编译
    if( $vars == 'inc_' ){
      //* 如果需要，必须传递变量值和编译规则
      $arg = array( $arg, $this->inData, $this->config );
    }
    if( class_exists( 'yoncms\\tool\\'.$obj.'_compile' ) ){
      $obj = PC( $obj, $arg );
      $parent = new pubCompile();
      return $parent->compile( $obj );
    }else{
      return $arg;
    }
  }

  private function getContent( $tpl, $arr ){
    $this->inData = array();
    if( !empty( $arr ) ){
      foreach( $arr as $k=>$v ){
        if( !is_array( $v ) ){
          if( !strstr( $k, '{' ) ){
            $this->inData['{'.$k.'}'] = $v;
          }else{
            //* 加载文件时使用
            $this->inData[$k] = $v;
          }
        }
      }
    }
    $data = '';
    if( file_exists( $tpl ) )
      $data = file_get_contents( $tpl );
    $this->getConfig( $data );
    return $data;
  }

  //* 这个方法可以防止程序的死循环，也
  //* 避免了重复使用的文件被反复编译
  private function unRepeat( $tpl ){
    $file = null;
    //* 去掉重复的模板文件，避免重复编译
    if( !in_array( $tpl, $this->flag ) ){
      //* 所有被编译的文件都必须写入flag数组
      //* 以确定被编译过，避免重复进行编译，
      //* 程序进入死循环
      $this->flag[] = $tpl;
      //* 带有erhtm的是公共模板文件
      if( substr( $tpl, -5 ) != 'erhtm' )
        $file = TPL . $tpl . '.tpl';
      else
        $file = TPL . $tpl;
    }
    return $file;
  }
  private function setConfig( $file, $arr=array(), $conf=array()){
    //* 获取编译数据，加入到config数组
    $data = $this->getContent( $file, $arr );

    //* 这是用于模板里inc加载文件，把inc文件里的规则添加到config
    if( $conf )
      $this->config += $conf;

    return $this->replace( $data );
  }
  //* 有加载文件include或require才会有conf规则参数
  public function parser( $tpls, $arr=array(), $conf=array() ){
    //* tpls为模板文件
    //* 避免同一个模板文件被重复编译
    $file = $this->unRepeat( $tpls );
    //* 如果文件不存在，无须编译
    if( !$file )
      return;
    //* 获取php编译文件的路径和文件名
    //* 生成编译文件的路径和文件名
    $cpl = toCpl( $file );
    //* 模板文件编译生成php文件
    $this->mDir( $cpl, $this->setConfig( $file, $arr, $conf ) );

  }
}


