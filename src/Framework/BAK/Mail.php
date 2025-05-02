<?php
//
//declare(strict_types=1);
//
//namespace Framework;
//
//use Framework\Config;
//use PHPMailer\PHPMailer\PHPMailer;
////date_default_timezone_set('Etc/UTC');
//date_default_timezone_set('Etc/UTC');
//use Framework\Flash;
//class Mail
//{
//    public static function send($to, $subject, $text, $html): void
//    {   $mail = new PHPMailer();
//        //$mail->SMTPDebug = 3;
//        //$mail->SMTPDebug = 2;
//        $mail->isSMTP();
//        $mail->Host = Config::SMTP_HOST;
//        $mail->Port = Config::SMTP_PORT;
//        //$mail->SMTPAuth = false;
//		$mail->SMTPAuth = true;
//        //$mail->SMTPSecure = false;
//		$mail->SMTPSecure = true;
//        //$mail->SMTPKeepAlive = false;
//
//		$mail->SMTPSecure = 'tls';   // Enable encryption, 'ssl'  nb added after
//		$mail->SMTPKeepAlive = true;
//        //after//$mail->SMTPAutoTLS = false;  //or true
//        $mail->Username = Config::SMTP_USER;
//        $mail->Password = Config::SMTP_PASSWORD;
//
//        $mail->CharSet = 'UTF-8';
//
//
//
//
//        $mail->isHTML(true);
//        $mail->setFrom('postmaster@anc.hopto.org');
//        $mail->addAddress($to);
//        $mail->Subject = $subject;
//        $mail->Body = $html;
//        $mail->AltBody = $text;
//        /*$mail->AddBCC($bcc_email);*/
//     $mail->SingleTo   = true;
//        //$mail->send();
//        //sleep(5);
//
//          if (!$mail->send()) {
//        echo 'Mailer Error: ' . $mail->ErrorInfo;
//		 echo "Message hasn't been sent.";
//    echo 'Mailer Error: ' . $mail->ErrorInfo . "n";
//        } else {
//
//			//  echo "Message has been sent  n";
//			 //   Flash::addMessage('PHPMailer message sent:  Success!');
//        $mail->ClearAllRecipients();
//              $mail->SmtpClose();
//
//
//     //  Flash::addMessage('You are now registered and verification email with login link will arrive soon.  Please also check spam or junk mail folders.');
//
//        //$this->redirect('/');
//        }
//
//
//
//    }
//
//
//
//}
//
//
//
//

