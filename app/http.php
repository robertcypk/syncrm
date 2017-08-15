<?php
//Controllers
date_default_timezone_set('America/Lima');

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
/*
$app->error(function (\Exception $e, $code) use ($app) {
    switch ($code) {
        case 404:
            $message = 'La página solicitada no pudo ser encontrada.';
            break;
        default:
            $message = 'Lo sentimos, pero algo salió terriblemente mal.';
    }

    return new Response($message);
});
*/
$app->mount('/',new Web\Index());
$app->post('/mailuser','Web\Emailuser::index')->bind('mailuser');
$app->get('/SyncCrm/{try}','Web\SyncCrm::index')->value('try','1')->bind('synccrm');
$app->post('/createcontact','Web\Createcontact::createContact')->bind('createcontact');
$app->post('/ExecuteUpdateContact','Web\ExecuteUpdateContact::init');
$app->get('/programas/{tipo}','Web\Programas::filter')->value('tipo','')->bind('tipoprogramafilter');
$app->get('/inputmap/{input}/{lang}','Web\Inputmap::map')->value('input','0')->value('lang','es')->bind('inputmap');

$app->run();