<?php
//Config Database, enviroment
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\ResultSetMapping;
use Silex\Provider\DoctrineServiceProvider;
use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Symfony\Component\Yaml\Parser;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\Security\Core\Security;
use Silex\Route;
use Silex\Application\SecurityTrait;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Routing\RouteCollection;


$app = new Application();
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());


$app->register(new TwigServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\SecurityServiceProvider());
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\SwiftmailerServiceProvider());

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options'    => array(
			'driver'    => 'pdo_sqlsrv',
            'host'      => '.\CENTRUM',
            'dbname'    => 'DB_businesslogic',
            'user'      => 'sqlwebservice',
            'password'  => 'Ricoh2017$',
            'charset'   => 'UTF-8',
            'driverOptions' => array(1002 => "SET NAMES 'UTF8'")
       )
));


$app->register(new Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider, array(
    "orm.em.options" => array(
         "mappings" => array(
				array(
				   "type"      => "yml",
				   "namespace" => "Entity",
				   "path"      => realpath(__DIR__."/app/config/doctrine"),
				  ),
            ),
			'mysql' => array(
				'connection' => 'mysql',
				'mappings' => array(), 
            )
         ),
));

$app['security.encoder.digest'] = $app->share(function ($app) {
    return new MessageDigestPasswordEncoder('md5', false, 1);
});

$app->register(new Silex\Provider\HttpCacheServiceProvider(), array(
    'http_cache.cache_dir' =>  __DIR__.'/cache/',
    'http_cache.esi'       => null,
));
?>