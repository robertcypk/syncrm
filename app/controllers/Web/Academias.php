<?php
namespace Web;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Silex\ControllerProviderInterface;

class Academias{
	function lista(Request $request,Application $app){
		$array =[];
		$academas = $app['orm.em']->getRepository('Entity\Academias')->findAll(array(),array('nombre'=>'ASC'));
		if( !empty($academas) ){
			
			foreach($academas as $academa){
				$array[] = [
					'nombre' => $academa->getNombre()
				];
			}
			return json_encode( $array );
		}else{
			return json_encode( array() );
		}
	}
}
?>