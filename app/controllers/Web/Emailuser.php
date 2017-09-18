<?php
namespace Web;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Emailuser
{
    public function index(Request $request, Application $app)
    {
        $email = $request->get('email');
        $subject = $request->get('subject');
        $mensaje = $request->get('body');
                    
        $template = $app['twig']->render(
                    
                        'email.html.twig',
                            array(
                                'mensaje' => $mensaje
                            )
                        );
                    
        $app['mail']->addAddress($email, '');
        //$app['mail']->addReplyTo('', '');
        $app['mail']->isHTML(true);
        $app['mail']->Subject = $subject;
        $app['mail']->Body    = $template;
        $app['mail']->AltBody = 'Copyright 2017 PUCP';

        if (!$app['mail']->send()) {
            return 0;//$app['mail']->ErrorInfo; //json_encode( array('error'=>  ) );
        } else {
            return 1;//json_encode( array('success' => 1  ) );
        }
    }
    public function logger($subject, $content, $file, $app)
    {
        $app['mail']->addAddress('cesar.huasupoma@pucp.pe', '');
        //$app['mail']->addBCC('diego.pachas@outlook.com','');
        $app['mail']->isHTML(true);
        $app['mail']->Subject = $subject;
        $app['mail']->Body    = $content;
        $app['mail']->AltBody = 'Copyright 2017 PUCP';
        
        if (!empty($file)) {
            $win = "sync\app\controllers\Web";
            $unix = "sync/app/controllers/Web";
            $ruta = str_replace($win, '', __DIR__).'uploads';
            $file = 'log-'.$file.'-'.date('d-m-Y').'.txt';
            $app['mail']->addAttachment($ruta.'/'.$file);
        }
        

        if (!$app['mail']->send()) {
            return '';
        } else {
            return '';
        }
    }
}
