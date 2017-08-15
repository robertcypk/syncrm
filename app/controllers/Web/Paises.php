<?php
namespace Web;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Silex\ControllerProviderInterface;

class Paises{
	function lista(Request $request,Application $app){
		$array =[];
		$paises = $app['orm.em']->getRepository('Entity\Paises')->findAll(array(),array('descripcion'=>'ASC'));
		if( !empty($paises) ){
			
			foreach($paises as $pais){
				$array[] = [
					'codigo' => $pais->getCodigo(),
					'descripcion' => $pais->getDescripcion()
				];
			}
			return json_encode( $array );
		}else{
			return json_encode( array() );
		}
	}
	function listapaises($cod,$app){
		$array =[];
		if( $cod != ''){
			$paises = $app['orm.em']->getRepository('Entity\Paises')->findOneBy( array('codigo'=>$cod) );
			if( !empty($paises) ){
				
				$array = $paises->getDescripcion();
				
				return $array;
			}else{
				return '-';
			}
		}else{
			return '-';
		}
		
	}
}
?>