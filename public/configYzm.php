<?php

// *************************
// *****生成验证码
// *************************

header("Content-Type: image/png");

$yzma=substr( md5( mt_rand() ), 10 , 5 );

//// 验证码区域的大小
$im = @imagecreate(55, 22);

//// 背景的颜色
$background_color = imagecolorallocate($im, 100, 100, 100);

//// 验证码的颜色
$text_color = imagecolorallocate($im, 233, 233, 233);

//// 5验证码的大小、5左右、4上下
imagestring($im, 5, 5, 4,  $yzma, $text_color);

///// 绘背景干扰点
for($i=0; $i<160; $i++){

    ///// 干扰点颜色
    $color2 = ImageColorAllocate($im, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));

    //// 干扰点
    ImageSetPixel($im, mt_rand(0,55), mt_rand(0,22), $color2);
}

session_start();

$_SESSION['yzma'] = $yzma;

Imagepng($im);

ImageDestroy($im);






