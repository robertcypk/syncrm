<?php

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/app/views',
    'twig.options' => array('cache' => null)
));

//__DIR__.'/../var/cache/twig'

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->getExtension('Twig_Extension_Core')->setTimezone('America/Lima');
	
	$twig->addFunction(new \Twig_SimpleFunction('npais',function($cod='') use ($app){
		return Web\Paises::listapaises($cod,$app);
        
    }));

    $twig->addFunction(new \Twig_SimpleFunction('procedenciatxt',function($txt='') use ($app){
        return Web\Index::procedenciatxt($txt,$app);
        
    }));
    return $twig;
}));

//Configuracion de depuracion
$app['debug'] = true;
//firma
$app['copyright'] = '© 2017. All rights reserved.';

$admins = array('ROLE_ADMIN','ROLE_ALLOWED_TO_SWITCH','ROLE_SUPER_ADMIN');
$ventas = array('ROLE_VENTAS');
$soporte = array('ROLE_SUPPORT','ROLE_IMAGENTI');
$usuarios = array('ROLE_USER','ROLE_PLANNER');
$clientes = array('ROLE_CLIENT','ROLE_COMPRAS');
$perfiles = array('ROLE_ADMIN','ROLE_CLIENT','ROLE_USER','ROLE_SUPPORT','ROLE_PLANNER','ROLE_IMAGENTI','ROLE_VENTAS','ROLE_COMPRAS');

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'default' => array(
            'pattern' => '/^.*$',
            'anonymous' => true,
            'form' => array('login_path' => '/panel', 'check_path' => 'login_check'),
            'logout' => array('logout_path' => '/logout'),
            'remember_me' => array(
                'key'                => uniqid(),
                'always_remember_me' => true,
            ),
            'users' => $app->share(function() use ($app) {
                return new Panel\UserProvider($app['db']);
            }),
        ),
    ),
    'security.encoders'  => array(
      'Entity\Usuarios'=> array(
        'algorithm' => 'sha1',
        'iterations' => 4,
        'encode_as_base64' => false
      )
    ),
    'security.role_hierarchy' => array(
      'ROLE_USER' => array(),
      'ROLE_CLIENT' => array(),
      'ROLE_SUPPORT' => array(),
      'ROLE_ADMIN' => array(),
      'ROLE_SUPER_ADMIN' => $admins,
    ),
    'security.access_rules' => array(
        )
));
$app->register(new Silex\Provider\RememberMeServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Web\PhpMailerServiceProvider());
$app->boot();
