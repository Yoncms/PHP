<?php
//
//-+----------------------------------------
// | init文件里定义常量的参数，键是常量名
// | 值是常量值；
// | 如果需要更多定义常量，可以在这里添加
//-+----------------------------------------
//

return array(

  /**
  * $path是文件目录的形式，如：E:\www
  * $host是服务器地址的形式，如：http://localhost
  */

  //加载js和css文件时要用服务器地址
  'HTC'     =>      HOST . 'static/',

  //其他加载文件要用本地地址

  'VIW'     =>      ROOT . 'view/',

  'TPL'     =>      ROOT . 'templates/',

  'CMP'     =>      ROOT . 'phps/',

  'TIME'    =>      $_SERVER["REQUEST_TIME"],

  'R_URI'   =>      $_SERVER['REQUEST_URI'],

  'RND'     =>      $_SESSION['rnd'] ?? 0,

  'LEVEL'     =>      $_SESSION['level'] ?? -1,

);

//;