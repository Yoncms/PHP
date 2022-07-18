<?php
/**
* 发送邮件的类
*/
namespace yoncms\model;

class sendMail{
	private $M = null;
  private $from = null;
	public function __construct(){

    inHr( 'PHPMail/class.phpmailer.php' );

    $this->M = new \PHPMailer();
		$this->M->SetLanguage('zh_cn');
		$this->M->IsSMTP();
		$this->M->Host = "smtp.163.com";
		$this->M->SMTPDebug = 0;
		$this->M->SMTPAuth = true;
		$this->M->Username = $this->getConfig('Email' );
		$this->M->Password = base64_decode( $this->getConfig('emailPsw') );
		$this->M->FromName =  $this->getConfig('manager');
		$this->M->CharSet = "utf-8";


//    //$mail->Port = 465;// 服务器端口25或者465具体要看邮箱服务器支持
//
//    $mail->setFrom('xxxx@163.com', 'Mailer');//发件人
//    $mail->addAddress('aaaa@126.com', 'Joe');// 收件人

    //$mail->addAddress('ellen@example.com');// 可添加多个收件人

    //回复的时候回复给哪个邮箱 建议和发件人一致
//    $mail->addReplyTo('xxxx@163.com', 'info');

    //$mail->addCC('cc@example.com'); //抄送

    //$mail->addBCC('bcc@example.com');//密送

    // 是否以HTML文档格式发送，发送后客户端可直接显示对应HTML内容
//    $mail->isHTML(true);
//    $mail->Subject = '这里是邮件标题' . time();
//    $mail->Body    = '<h1>这里是邮件内容</h1>' . date('Y-m-d H:i:s');
//    $mail->AltBody = '如果邮件客户端不支持HTML则显示此内容';
//
//    $mail->send();
//    echo '邮件发送成功';


	}
  private function getConfig( $key ){
    $data = config( 'Mail' );
    return $data[$key];
  }

	public function startSend( $to, $subj, $cont, $user ){
    try{
      $this->M->SetFrom($this->M->Username, $this->M->FromName);
      $this->M->Subject = $subj;
      $this->M->MsgHTML($cont);
      $this->M->AddAddress($to,'<'.rCode('user').'：' . $user .  '> Email：');
      return $this->M->Send();
    }catch( Exception $e){
      echo eCode( 'mailSendFail' );
      return false;
    }

	}
}












