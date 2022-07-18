<?php

use yoncms\tool\getLang;

//加载非class文件简写
if( !function_exists( 'inHr' ) ){
  function inHr( $url=null, $fg=1 ){
    if( !$url )
      return;
    $ur = $fg == 1 ? ROOT : HOST;
    return include $ur . $url;
  }
}

if( !function_exists( 'enUrl' ) ){
  function enUrl( $str ){
    return rawurlencode( urlencode( $str ) );
  }
}
if( !function_exists( 'deUrl' ) ){
  function deUrl( $str ){
   return rawurldecode( urldecode( $str ) );
  }
}
//echo地址函数，参数2为1是服务器地址，0是本地地址
if( !function_exists( 'eUrl' ) ){
  function eUrl(  $url=null, $fg=1 ){
    if( !$url ) return;
    $ur = $fg == 1 ? ROOT : HOST;
    echo $ur . $url;
  }
}

//返回地址，，参数2为1是服务器地址，0是本地地址
if( !function_exists( 'rUrl' ) ){
  function rUrl( $url=null, $fg=1 ){
    if( !$url ) return;
    $ur = $fg == 1 ? ROOT : HOST;
    return $ur . $url;
  }
}

if( !function_exists( 'setConst' ) ){
  function setConst( $arr = null ){
      if( empty( $arr ) ) return;
      foreach( $arr as $k=>$v )
        define( $k, $v );
      return;
  }
}

//加载JS和CSS文件
if( !function_exists( 'Jcs' ) ){
  function Jcs(){
    /**
    * 参数的形式可以是：'a,_a,b,_b,...'，
    * 也可以是：'a','_a','b','_b',...
    */
    $arr = func_get_args();
    if( count( $arr )==1 && strstr( $arr[0], ',' ) ){
      $arr = explode( ',', $arr[0] );
    }
    foreach( $arr as $v ){
      $v = trim( $v );
      if( substr( $v, 0, 1 ) == '_' ){
        $js = HTC . 'js/' . substr( $v, 1 );
        echo "<script src='" . $js . ".js'></script>\r\n";
      }else{
        $str = "<link rel='stylesheet' type='text/css' ";
        $str .= "href='" . HTC . 'css/' . $v . ".css' />\r\n";
        echo $str;
      }
    }
    return;
  }
}

//提取数组（可以是多维数组）的键组成新数组（是一维）
if( !function_exists( 'arrToOne' )  ){
  function arrToOne( $crr ){
    static $arr = array();
    foreach( $crr as $v ){
      if( !is_array( $v ) )
        $arr[] = $v;
      else
        arrToOne( $v );
    }
    return $arr;
  }
}

if( !function_exists( 'isAssoc' ) ){
  //判断数组是否为关联数组
  function isAssoc( $arr ) {
    return array_keys( $arr ) !== range(0, count( $arr ) - 1);
  }
}

if( !function_exists( 'isOneArr' ) ){
  //* 判断数组是一维还是二维数组
  function isOneArr( $arr ){
    //* 计算数组的单元数不同计算方法，判断是否相等
    //* count( $arr )值计算一维的单元数，count( $arr, 1 )进行递归计算
    if( count( $arr ) == count( $arr, 1 ) )
      return true;
    return false;
  }
}

if( !function_exists( 'strDecode' ) ){
  function strDecode( $str ){
    //* 如果不是数字进行解码
    if( !is_numeric( $str ) )
      return base64_decode( $str );
    //* 如果是数字直接输出
    return $str;
  }
}

//* 提取Lang数据（解码或不解码）
if( !function_exists( 'rCode' ) ){
  function rCode( $key=null, $fg=0 ){
    if( !$key ) return ;
    if( $fg == 0 )
      //* 不需要解码
      return getLang::inNew( )->$key;
    //* 需要解码
    return getLang::inNew( )->deCode( $key );
  }
}

//* 打印Lang数据（解码或不解码）
if( !function_exists( 'eCode' ) ){
  function eCode( $key=null, $fg=0 ){
    if( !$key ) return ;
    if( $fg == 0 ){
      //* 不需要解码
      echo getLang::inNew( )->$key;
      return;
    }
    //* 需要解码
    echo getLang::inNew( )->deCode( $key );
    return;
  }
}

if( !function_exists( 'deCode' ) ){
  //* 数组数据加密、解码，数字不进行处理
  function deCode( $arr ){
    //* 不是数组分两种情况
    if( !is_array( $arr ) )
      return strDecode( $arr );
    $temp = null;
    //* 是数组进行循环
    foreach( $arr as $k=>$v ){
      //* 循环后的元素如果不是数组
      if( !is_array( $v ) ){
        $v = strDecode( $v );
      }elseif( is_array( $v ) )
        $temp[$k] = $v;
    }
    return $temp;
  }
}

//* 通过元素的键，计算它在关联数组的位置
if( !function_exists( 'arr_int' ) ){
  function arr_int( $arr, $key ){
    $n = 0;
    foreach( $arr as $k=>$v ){
      if( $k == $key )
        return $n;
      $n++;
    }
  }
}

//* 获取config信息
if( !function_exists( 'config' ) ){
  function config( $name ){
    $file = ROOT .'public/config'.$name.'.php';
    if( file_exists( $file ) ){
      $arr = include $file;
      return $arr;
    }
    return;
  }
}

//* 生成编译、缓存文件的地址
if( !function_exists( 'changeUrl' ) ){
  function changeUrl( $url, $fg=false ){
    //* 斜杠最后一次出现的位置
    $url = substr( $url, 0, strrpos( $url, '/' ) + 1 );
    if( !$fg )
      //* 把templates转成phps（php文件）
      return str_replace( 'templates', 'phps', $url );
    else
      //* 把templates转成view（缓存文件）
      return str_replace( 'templates', 'view', $url );
  }
}

//* 用于转换载入文件的地址
if( !function_exists( 'toCpl' ) ){
  function toCpl( $tpls, $fg=false ){
    if( substr( $tpls, -5 ) == 'erhtm' )
      return str_replace( 'templates', 'phps', $tpls );
    $param = $tpls;
    //* 如果是生成view编译文件，则添加data和参数
    if( !!$fg ){
      //* 获取模块、类、方法，并拼接成字符串
      $mcm = implode( '', getUri()[0] );
      $mcm .= implode( '', getUri()[1] );
      //* 把模块等字符串添加到文件后面，以便跟Run里
      //* 的命名相同，php编译文件的后面不加参数
      $param = $tpls . $mcm;
    }
    //把目录从templates转到phps或view的对应目录
    $cpl = changeUrl( $tpls, $fg );
    //* 生成cpl的文件名
    $fn = substr( md5( $param ), -20 );
    //* 目录后面加上文件名
    $cpl .= $fn;
    //* 返回要加载的php文件地址
    return $cpl;
  }
}

//* 因为有多个地方需要获取相同的URI相关信息，
//* 因此专门用一个函数来获取，方便升级、维护
//*
//* 地址的四种写法：
//* 1、/index.php/mod/class/method
//* 2、/mod/class/method
//* 3、/index.php?mod=mod&class=class&method=method
//* 4、/?mod/class/method
//*;
//* 默认get传参的形式：/index.php?mod=???&class=???&method=???
if( !function_exists( 'getUri' ) ){
  //* 获取URI里的相关信息：模块、类、方法
  function getUri(){
    //* 地址栏格式错误
    if( count( $_GET ) == 1 && current( $_GET ) )
      Exit( eCode( 'noPage' ) );
    $get = isset( $_GET['mod'] ) ? $_GET['mod'] : null;
    //* data是基础数据，必须有（指定模块、类、方法）
    $data = '';
    if( strstr( R_URI, '.php?' ) )
      $data = array_values( $_GET );
    //* .php/module/class/method的形式
    else if( strstr( R_URI, '.php/' ) )
      $data = str_replace( 'php/' , '', strstr( R_URI , 'php/' ) );
    //* /?module/class/method的形式
    elseif( strstr( R_URI, '/?' ) )
      $data = key( $_GET );
    //* /module/class/method
    else
      $data = strstr( R_URI, $get );
    return getUriFoo( $data );
  }
}

//* 获取URI里的相关信息函数的方法
if( !function_exists( 'getUriFoo' ) ){
  //* 处理地址栏数据，生成数组
	function getUriFoo( $data ){
		if( !is_array( $data ) ){
      //* 如果最后有斜杠，先去掉斜杠
			if( substr( $data, -1 ) == '/' )
				$data = substr( $data, 0, - 1 );
			$data = explode( '/', $data );
		}
		//* 如果有参数，把参数取出
    $arr = array();
    //* 前三个必须是模块、类名、方法，否则无法运行；
    //* 后面的是参数（可能有也可能没有）
    if( isset( $data[3] ) ){
			$arr = array_splice( $data, 3 );
			//* 到这里arr是参数，但是可能没有参数
			if( isset( $arr[0] ) ){
				//* 参数不是偶数个，即不成对
				if( count( $arr ) % 2 != 0 )
					eCode( 'argErr' );  //return Error();
			}
		}
    //* data包含3个元素，参数arr不定元素，都是数组
    return array( $data, $arr );
  }
}

//* 创建目录
if( !function_exists( 'makeDir' ) ){
  function makeDir( $file ){
    $arr = explode( '/', $file );
    $fn = array_pop( $arr );
    $dir = '';
    foreach( $arr as $v ){
      $dir .= $v .'/';
      if( is_dir( $dir ) )
        continue;
      mkdir( $dir );
      $fn = $dir . '/index.htm';
      //* 处于安全考虑如果没有空的桥文件，则创建
      if( !file_exists( $fn ) )
        fopen( $fn, 'w' );
    }
  }
}

//* 截取关联数组的指定元素
if( !function_exists( 'removeElem' ) ){
  function removeElem( &$arr, $key, $fg='####' ){
    $myArr = $arr[$key];
    unset( $arr[$key] );
    if( !!$fg )
      return explode( $fg, $myArr );
    return $myArr;
  }
}

//* 设置cookie
if( !function_exists( 'cookie' ) ){
  function cookie( $key, $val, $path='/' ){
		$_COOKIE[$key] = $val;
		setcookie( $key, $val, 0, $path );
  }
}

//* 删除cookie
if( !function_exists( 'uncookie' ) ){
  function uncookie( $key, $path='/'){
		if( !is_array( $key ) ){
			unset( $_COOKIE[$key] );
			setcookie( $key, '', time()-10, $path );
		}else{
			foreach( $key as $v ){
				unset( $_COOKIE[$v] );
				setcookie( $v, '', time()-10, $path );
			}
		}
  }
}
//* 生成6位随机整数或6位随机字符串（字母包含a～f），
//* 参数是false时，获取整数
if( !function_exists( 'Yoncms' ) ){
  function Yoncms( $arg=false ){
    return ET()->yoncms( $arg );
  }
}

//加载错误页面
if( !function_exists( 'Error' ) ){
  function Error(){
    include_once( VIW . 'static/err.htm' );
    return;
  }
}

if( !function_exists( 'qMark' ) ){
  //非关联数组的sql语句拼接，有几个元素就是几个？
  function qMark( $arr, $mark='' ){
    $mark = !$mark ? '?,' : $mark;
    return rtrim( str_repeat( $mark, count( $arr ) ), ',' );
  }
}

if( !function_exists( 'arrCols' ) ){
  //* 获取二维数组的某列元素，组成一维数组
  //* 相当于array_column()
  function arrCols( $arr, $col ){
    $crr = array_map(
      function ( $v )use( $col ){
        return $v[$col];
      }, $arr
    );
    return array_filter( $crr );
  }
}

if( !function_exists( 'shape' ) ){
  //* 根据题目类型，获取新数组，参数shape是类型
  function shape( $arr, $shape=0 ){
    $crr = array_map(
      function ( $v )use( $shape ){
        if( $v['shape'] == $shape )
          return $v;
      }, $arr
    );
    return array_filter( $crr );
  }
}

if( !function_exists( 'noKeys' ) ){
  //非关联数据的sql语句拼接，带占位符，一般用于多条件where
  function noKeys( $arr, $key, $fg='' ){
    if( !$arr )
      return;
    $fg = orAnd( $fg );
    $str = '';
    $len = count( $arr );
    for( $i=0; $i<$len; $i++ ){
      $fg = $i == $len-1 ? '' : $fg;
      $str .= $key . '=? '. $fg . ' ';
    }
    return $str;
  }
}
if( !function_exists( 'wKeys' ) ){
  //关联数组拼接成带占位符的sql语句
  function wKeys( $arr=null, $fg='' ){ //print_r( $arr );
    if( !$arr ) return;
    $str = '';
    $fg = orAnd( $fg );
    $sum = count( $arr );
    foreach( $arr as $k=>$v ){
      $fh = hasMark( $v, array('>','<') ) ? ' ? ' : '=? ';
      $fg = $k != $sum - 1 ? $fg : '';
      $str .= ' ' . $v . $fh . $fg;
    }
    return $str;
  }
}
//or或and转成&&或||
if( !function_exists( 'orAnd' ) ){
  function orAnd( $fg=null ){
    if( $fg == 'and')
      return '&&';
    elseif( $fg == 'or' )
      return '||';
    else
      return;
  }
}
//* 把where条件转车数组
if( !function_exists( 'whereArr' ) ){
  function whereArr( $arr=null, $fg='' ){
    if( !$arr ) return;
    $sub = '';
    $crr = array();
    $frr = array();
    foreach( $arr as $k=>$v ){
      if( !is_numeric( $k ) ){
        //* $v==0是参数值真正为0时，避免被误认为是不为真
				if( ( !is_array( $v ) && !!$v ) || $v == 0 ){
          $frr[] = $k;
          $crr[] = $v;
				//* 如果使用where或where...in，根据多个字段而且每个字段有多个值查找的时候
        }elseif( is_array( $v ) ){
          $ff = '';
          if( isset( $v['fg'] ) ){
            $ff = $v['fg'];
            unset( $v['fg'] );
          }
          $ff = $ff ? $ff : $fg;
          $sub .= noKeys( $v, $k, $ff );
          $crr = array_merge( $crr, $v );
        }
      }
    }
    $str = wKeys( $frr, $fg );
    $gg = orAnd( $fg );
    $str = !$str ? $sub : ( !$sub ? $str : $str . $gg . $sub );
		
    return array( $str, $crr );
  }
}
//* where的方法，如果数组里有fg元素，把fg单独取出
if( !function_exists( 'whereFoo' ) ){
  //sql语句的where子句
  function whereFoo( $arr=null ){
    if( !$arr )return ;
    $str = '';
    $fg = '';
    if( isset( $arr['fg'] ) ){
      $fg = $arr['fg'];
      unset( $arr['fg'] );
    }
    return whereArr( $arr, $fg );
  }
}

if( !function_exists( 'hasMark' ) ){
  //判断字符串里是否存在>或<号
  function hasMark( $str=null, $arr=null ){
    if( !$str ) return;
    if( !$arr ) return;
    foreach( $arr as $v ){
      if( strstr( $str, $v ) )
        return true;
    }
    return false;
  }
}

//* 数据库操作结果成功与否，输出显示
if( !function_exists( 'SucFail' ) ){
  function SucFail( $oper, $aff=false ){
    $str = '<h3>' . $oper;
    $str .= !!$aff ? rCode( 'Succ' ) : rCode( 'Fail' );
    $str .= rCode( 'backTime' ) . '</h3>';
    Exit( $str );
  }
}

//备份sql请求
if( !function_exists( 'bakSql' ) ){
  function bakSql( $sql=null ){
//    if( !$sql ) return;
    $file = rUrl( 'logs/sql.bak' );
    $fopen = fopen( $file, 'ab+' );
    fwrite( $fopen, $sql );
    fclose( $fopen );
    if( filesize( $file ) >= 3*1024000 )
      rename( $file, rUrl( 'logs/'.time().'.bak' ) );
    return ;
  }
}

if( !function_exists( 'Barg' ) ){
  //参数绑定的通用方法，包括limit在nginx下都可以
  function Barg( $stmt, $arr=null ){
    //arr如果是null，表示不需要绑定参数，也就是sql没有占位符
    if( !empty( $arr ) ){
      if( !is_array( $arr ) )
        BargFoo( $stmt, 1, $arr );
      else{
        foreach( $arr as $k=>$v ){
          $kv = $k + 1;
          //参数1是占位符所在的位置，参数2是对应占位符的值
          //参数3：只要1和2，1是整形，2是字符串
          BargFoo( $stmt, $kv, $v );
        }
      }
    }
    bakSql( json_encode( $arr ) );
    $stmt->execute();
    if( $stmt->errorCode() != '00000' )
      exit( bakSql( 'Error::'.$stmt->errorInfo()[2] ) );
  }
}
//* 绑定数据的方法
if( !function_exists( 'BargFoo' ) ){
  function BargFoo( $stmt, $kv, $v ){
    if( is_numeric( $v ) )
      $stmt->bindValue( $kv, $v, 1 );
    else
      $stmt->bindValue( $kv, $v, 2 );
  }
}

//* get传参是给ID简单加密
if( !function_exists( 'setId' ) ){
  function setId( $id, $tm=2 ){
		if( !$id ) return;
		$n = $id * RND + 7;
		//* 通过cookie记住id，以便删除时使用，有效期只有2个小时
		setcookie( 'zhang', $n, time()+3600*$tm, '/' );
		return substr( md5( $n ), -16 );
  }
}
//* 解密
if( !function_exists( 'getId' ) ){
  function getId( $id ){
		if( RND === 0 || !isset( $_COOKIE['zhang'] ) ) 
			exit('No Access');
		//* 上面设置好的cookie，在这里可以使用
		$x = $_COOKIE['zhang'];
		//* 赋值完后，删除cookie缓存
		setcookie( 'zhang', '', time() - 10, '/' );
		$n = ($x - 7) / RND;
		if( $id == substr( md5( $x ), -16 ) )
			return $n;
  }
}

if( !function_exists( 'countRows' ) ){
  //计算文本里的内容有几行
  function countRows( $file ){
    $f = file( $file );
    return count( $f );
    fclose( $f );
  }
}
