<?php
//
//-+-----------------------------------------
// | 类调用时的简写
//-+-----------------------------------------
//
//* use yoncms\tool\getLang;

use yoncms\tool\compile;

if( !function_exists( 'inNew' ) ){
  function inNew( $class, $arg=null ){
    $arr = array(
        'admin', 'adminer', 'zujuan', 'exams', 'mUnlink'
    );
    $Cs = substr( $class, strrpos( $class, '\\' )+1 );
    //* 如果是管理模块，用户必须先登录，获取操作权限
    if( in_array( $Cs, $arr ) && RND == 0 )
        Exit( rCode('noPage' ) );
    return $class::inNew( $arg );
  }
}
//* 操作数据库1
if( !function_exists( 'OP' ) ){
  function OP( $tname=null ){
    $obj = inNew( '\\yoncms\\mysql\\operate', $tname );
    if( !$obj )
      exit( bakSql( rCode( 'mysql_empty' ) ) );
    return $obj;
  }
}
//* 操作数据库2
if( !function_exists( 'OM' ) ){
  function OM( $tname=null ){
    $obj = inNew( '\\yoncms\\mysql\\operateMysql', $tname );
    if( !$obj )
      exit( bakSql( rCode( 'mysql_empty' ) ) );
    return $obj;
  }
}
//* 数据加密
if( !function_exists( 'ET' ) ){
  function ET( $arg=null ){
    $obj = inNew( '\\yoncms\\tool\\encrypt', $arg );
    if( $obj )
      return $obj;
  }
}

//compile编译
if( !function_exists( 'CP' ) ) {
  function CP( $arr = null ){
    return inNew( 'yoncms\tool\compile', $arr );
  }
}
//公共编译类
if( !function_exists( 'PC' ) ) {
  function PC( $fix, $arr = null ){
    $obj = 'yoncms\\tool\\'.$fix.'_compile';
    return inNew( $obj, $arr );
  }
}

if( !function_exists( 'MM' ) ){
  function MM( $tab=null ){
    return inNew( 'yoncms\\model\\model', $tab );
  }
}

//* 会员的模块如果没有登录无法进入
if( !function_exists( 'MB' ) ){
  function MB(){
    return inNew( 'yoncms\\model\\member', 'member' );
  }
}

//* 管理的模块如果没有登录无法进入
if( !function_exists( 'MA' ) ){
  function MA(){
    return inNew( 'yoncms\\model\\adminer' );
  }
}


//* 加密发送激活链接的数据
if( !function_exists( 'Encrypt' ) ){
  function Encrypt( $arg=null ){
    return ET()->Encrypt( $arg );
  }
}
//* 生成加密后的member表的psw字段，参数是密码明文
if( !function_exists( 'Psw' ) ){
  function Psw( $arg, $salt='' ){
    return ET()->pWord( $arg, $salt );
  }
}
//*导航条里对应的div，设置内容
if( !function_exists( 'navlist' ) ){
  function navlist( $arr ){
		foreach( $arr as $k=>$v ){
			if( !is_array( $v ) )
				echo '<a class="navlista" href="'. $v . '"> &nbsp;'. $k . '</a> ';
			else{
			  $str = ' &nbsp;' . $k . ': ';
				foreach( $v as $kk=>$vv ){
					if( $kk > 10 ) $kk -= 10;
					$str .= '<a class="navlista" href="'. $vv . '">'. $kk . rCode('grade') . '</a> ';
			  }
				echo $str . '</p>';
			}
		}
  }
}
