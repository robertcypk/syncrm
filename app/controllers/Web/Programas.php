<?php
namespace Web;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Web\WorkflowConect;
class Programas{
	public function filter(Request $request,Application $app){
		$tipo = $request->get('tipo');
		$nombrepro = $app['orm.em']->getRepository('Entity\Programas')
								   ->findBy( array('CTR_TipoProgramaFinal_c'=>$tipo) );
		$array=array();
		if( !empty($nombrepro) ){
			foreach($nombrepro as $nombrepro){
				$array[] = [
					'programa' => $nombrepro->getCTRProgramaAcademicoCalculadoC(),
					'codigo' => $nombrepro->getCTRNumProdC()
				];
			}				
		}
		return json_encode( $array );
	}
}
?>