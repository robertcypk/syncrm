<?php
namespace Web;

use Silex\Application;
use Silex\ServiceProviderInterface;
use \Web\PHPMailer;
use \Web\SMTP;

class PhpMailerServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
    	$app['mail'] = $app->share(function () use ($app) {
    		//Create a new PHPMailer instance
            $mail = new PHPMailer;
            $mail->isSMTP();
            //Enable SMTP debugging
            // 0 = off (for production use)
            // 1 = client messages
            // 2 = client and server messages
			$mail->SMTPDebug = 0;
            $mail->Debugoutput = 'html';
            /*
			$mail->Host = 'secure.emailsrvr.com';//emailsrvr 'vvmm-hbgv.accessdomain.com';
			$mail->Username = 'soporte@stansa.com.pe';//$app['phpmailer.userMail'];
            $mail->Password = 'Ricoh2016';
			$mail->Port = 25;
			*/
			
			$mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
			$mail->Username = 'centrum.informes@pucp.pe';
			$mail->Password = 'c.informa';
            $mail->SMTPSecure = 'ssl';
			$mail->Port = 465;
			
            
			$mail->CharSet = "UTF-8";
            //$mail->setFrom( 'soporte@stansa.com.pe' , 'CENTRUM - Catolica');
			$mail->setFrom( 'centrum.informes@pucp.pe' , 'CENTRUM - Catolica');

    		return $mail;
        });
    }
    public function boot(Application $app)
    {
    }
}
?>