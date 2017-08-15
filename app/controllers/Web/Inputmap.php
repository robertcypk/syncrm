<?php
namespace Web;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Silex\ControllerProviderInterface;

class Inputmap{
	function map(Request $request,Application $app){
		$array =[];
		$input_name = $request->get('input');
		$lang = $request->get('lang');
		if( !empty($input_name) ){
		$in = $app['orm.em']->getRepository('Entity\Inputmap')->findOneBy(array('input'=>$input_name));
			if( !empty($in) ){
				if( $lang == 'en' ){
					return $in->getEn();
				}else{
					return $in->getEs();
				}
			}else{
				return '';
			}
		}else{
			return '';
		}
	}
}
?>