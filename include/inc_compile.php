<?php
/**
* 加载文件（php文件，而不是html）
* 通过加载的路径和文件名，对加载的文件进行
* 编译，最后加载的是编译后的文件；加载的文件
* 除非该模板文件有更新，否则就没有必要重新编译，
* 有没有重新编译就判断模板文件与加载文件的创建
* 时间，如果模板文件比较新（即时间戳较大）则重
* 新编译
*/
namespace yoncms\tool;

class inc_compile extends pubCompile{

  private function oData(){
    //* 获取元素数据
    $rData = $this->returnData();
    if( !is_array( $rData ) ){
      $arg = substr( $rData, 4 );
      $inData = null;
    }else{
      $arg = substr( $rData[0], 4 );
      $inData = $rData[1];
    }
    //* arg是模板文件
    //* inData是assign传进来的数据，rData是配置数据
    return array( $arg, $inData, $rData[2] );
  }

  //* 编译PHP文件并返回include+文件路径+文件名
  public function parser( ){
    $args = $this->oData();
    //* 模板文件名
    $tp = $args[0];
    //* 专门用于公共模板文件，即后缀是.htm的文件
    if( substr( $tp, -5 ) == 'erhtm' ){
      $tpl = TPL . $tp;
      $cpl = ROOT . 'phps/' . $tp;
    }else{
      $tpl = TPL . $tp . '.tpl';
      //* php编译文件路径
      $cpl = changeUrl( $tpl );
      //* php编译文件都不添加模块、类、方法，也不添加参数
      //* 生成php编译文件的文件名
      $fn = substr( md5( $tpl ), -20 );
      //* 得到编译后的文件路径和文件名
      $cpl .= $fn;
    }
    //* 如果要加载的文件不存在或者比模板文件的创建
    //* 时间旧（时间戳小），则编译要加载的文件
    if( !file_exists( $cpl ) || file_exists( $cpl ) && filemtime( $tpl ) >= filemtime( $cpl ) )
      CP()->parser( $tp, $args[1], $args[2] );
    //* 返回的是编译文件的路径和文件名
    return ' include \'' . $cpl . '\'';
  }
}















