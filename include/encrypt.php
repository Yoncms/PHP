<?php
/*
* 本类用于数据加密
*
*/

namespace yoncms\tool;

use yoncms\model\model;

class encrypt extends model{

  private $arg = 0;

  private $newStr=null;

  private $table = 'member';

  public function execState(){
    $data = $this->returnData();
    $this->arg = $uid = current( $data );
    $arg = $this->Decrypt( );
    $id = $arg[0];
    //*
    //* 注册时发送到邮箱的是用户的id，所以邮箱链接发回来
    //* 的是id根据id，因此从数据库提取数据根据的是id
    //*
    $res = $this->findUser( $id );

    $md = md5( $res['md'] );
    $tm = $arg[1] + base64_decode( rCode('mTime') );
    if( key( $data ) == $md ){
      //* 超过10天失效，无法激活
      if( $tm + 10 * 24 * 3600 < TIME )
        return eCode( 'linkDie' );
      //* 已经激活过的，无法再激活
      if( $res['state'] != 0 )
        return eCode( 'alreadyActive' );
      return $this->Activing( $id );
    }else
      return ;
  }

  //* 激活帐号
  private function Activing( $id ){
    $crr = array(
      'Cols'=>array( 'level' => 1, 'state'=> 1 ),
      'where' => array( 'id'=>$id )
    );
    $aff = $this->alter( $crr );
    //* 激活成功
    if( $aff ){
      eCode( 'activeSuc' );
      $arr = array(
        'Cols' => 'user,level',
        'where' => array( 'id'=>$id )
      );
      $res = $this->Find( $arr );
      $res['id'] = $id;
      return $res;
    }else{//* 激活失败
      return eCode( 'activeFail' );
    }
  }

  //* 去掉时间和id的字母
  private function Decrypt( ){
    //* 去掉字符串里的字母
    $reg = '/[a-f]+/';
    $str = preg_replace( $reg,'', $this->arg );
    //* 去掉后3位随机添加的数字
    $str = substr( $str, 0, -3 );
    //* 最后2位是id的位数，位数小于10前面加0
    $len = (int)substr( $str, -2 );
    $tm = substr( $str, 8, -$len-2 );
    $id = ( (int)substr( $str, -$len-2, -2 ) - 3292 ) / 13;
    return array( $id, $tm );
  }

  //* 从数据库查询数据，以便进行比较
  private function findUser( $id ){
    $arr = array(
      'Cols' => 'concat(user,36,psw,18,salt)as md,state',
      'where' => array( 'id'=>$id )
    );
    $this->setTab( $this->table );
    $res = $this->Find( $arr );
    return $res;
  }

  //* 生成一个加密过的时间字符串
  public function Encrypt( $data ){
    //* user来自于用户注册时填入，psw和salt为主动生成
    $cs = md5( $data['user'].'36'.$data['psw'].'18'.$data['salt'] );
    $tm = time() - base64_decode( rCode( 'mTime' ) );
    $id = $data['id'];
    $id = $id * 13 + 3292;
    $len = strlen( (string)$id );
    $id .= $len < 10 ? '0'.$len : $len;
    $tm = substr( YONCMS, -8 ) . (string)$tm . $id;
    $this->newStr( $tm );
    return $cs . '/' . $this->newStr;
  }

  //* 往数字里添加字母，会员注册时使用
  private function newStr( $tm ){
    $arr = range( 'a', 'f' );
    $i=0;
    $len = strlen( $tm );
    $newStr = '';
    for( ; $i<$len; $i++ ){
      //* a～f随机顺序的字符串
      shuffle( $arr );
      //* a～f的字符串
      $str = implode( '', $arr );
      $tmi = $tm[$i];
      //* 从打乱的字符串里，随机截取0～3之间个字符
      $addStr = substr( $str, rand(0,10), rand(0,4) );
      $stm = $addStr . $tmi;
      $newStr .= $stm;
    }
    //* 前八位和后三位都无效
    $newStr .= rand(100,999);
    $newStr .= substr( $str, rand(1,10), rand(0,3) );
    if( strlen( $newStr ) != 32 ){
      $this->newStr( $tm );
    }else
      $this->newStr =  $newStr;
  }

  //* 通过user查询state和email
  private function stateFind($pt){
    if( !isset( $pt['user'] ) )
      return;
    $arr = array(
      //* 提取id是为了发送邮件时，用户信息是用户的id
      'Cols' => 'id,state,email,concat(user,36,psw,18,salt)as md',
      'where'=>array( 'user'=>$pt['user'] )
    );
    $this->setTab( $this->table );
    return $this->Find( $arr );
  }


  //* 判断是否需要激活
  private function already(){
    $pt = $_POST;
    $res = $this->stateFind($pt);
    //* 如果激活过就无需再次激活
    if( $res['state'] != 0 )
      return;
    $email = $res['email'];
    $mail = $pt['email'];
    //* 如果邮箱地址不匹配，停止
    if( $email != $mail )
      return;
    //发送邮件
    $this->sendMail($pt);
  }

  //* 通过YONCMS常量，获取随机数或字符串
  public function yoncms($bl=false){
    if( !$bl )
      //数字
      return (int)substr( YONCMS, -6 );
    //字符串
    return substr( YONCMS, 7, -6 );
  }
  //* 生成加密后的密码
  public function pWord( $psw, $salt='' ){
    $salt = !!$salt ? $salt : $this->yoncms( true );
    return md5( $psw . 'Yoncms' . $salt );
  }
}





