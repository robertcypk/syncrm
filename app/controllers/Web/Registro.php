<?php

namespace Web;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Web\WorkflowConect;
class Registro{
	public function form(Request $request,Application $app){
		$array_post = $_POST;
		$data = [];
		

		if( !empty($array_post) ){
				
				$user = new \Entity\Usuario_crm;
				$user->setTipo(1);
				$user->setCrm(0);
				$user->setEmail(0);
				$user->setTry(0);
				$user->setDupli(0);
				$user->setEdupli(0);
				$user->setRegistro(time());
				$user->setError(0);
				$user->setCola(0);
				$app['orm.em']->persist($user);
				$app['orm.em']->flush();
				
				
				foreach($array_post as $k => $d){
					
					$campo = new \Entity\Campo_crm;	
					$campo->setUserId( $user->getId() );
					$campo->setCampo( $k );
					
					if( $k == 'ctrgradoacademicoc' ){
						$campo->setValor( mb_convert_encoding($d,'UTF-8','auto') ); //iconv("UTF-8", "ISO-8859-1", $d));
					}else{
						switch ( $k ) {
							case 'firstname':
								$campo->setValor(strtoupper(mb_convert_encoding($d,'UTF-8','auto')));
								break;
							case 'lastname':
								$campo->setValor(strtoupper(mb_convert_encoding($d,'UTF-8','auto')));
								break;
							case 'persondeoctrapellidomaternoc':
								$campo->setValor(strtoupper(mb_convert_encoding($d,'UTF-8','auto')));
								break;
							default:
								$campo->setValor( mb_convert_encoding($d,'UTF-8','auto') );
								break;
						}
						$campo->setValor( strtoupper( mb_convert_encoding($d,'UTF-8','auto') ) ); //iconv("UTF-8", "ISO-8859-1", $d));
					}
					
					$campo->setUid('preestablecido');
					$campo->setPag('preestablecido');
					$app['orm.em']->persist($campo);
					$app['orm.em']->flush();
					
					if( $k == 'nombrepro'){
						$nombrepro = $app['orm.em']->getRepository('Entity\Programas')->findOneBy( array('CTRNumProd_c'=>$array_post['programa']) );
						if( !empty($nombrepro) ):
						$qb = $app['orm.em']->createQueryBuilder();
								$q = $qb->update('Entity\Campo_crm', 'u')
											->set('u.valor', $qb->expr()->literal( strtoupper($nombrepro->getCTRProgramaAcademicoCalculadoC()) ) )
											->where(
												$qb->expr()->andX(
												   $qb->expr()->eq('u.user_id', '?1'),
												   $qb->expr()->eq('u.campo', '?2')
											   )
											)
											->setParameter(1, $user->getId())
											->setParameter(2,'nombrepro')
											->getQuery();
								$p = $q->execute();
								$app['orm.em']->flush();
						endif;
					}
				}
				
				if( empty($array_post['nombrepro']) ){
					$nombrepro = $app['orm.em']->getRepository('Entity\Programas')->findOneBy( array('CTRNumProd_c'=>$array_post['programa']) );
					if( !empty($nombrepro) ):
								$campo = new \Entity\Campo_crm;	
								$campo->setUserId( $user->getId() );
								$campo->setCampo( 'nombrepro' );
								$campo->setValor( strtoupper($nombrepro->getCTRProgramaAcademicoCalculadoC()) );
								$campo->setUid('preestablecido');
								$campo->setPag('preestablecido');
								$app['orm.em']->persist($campo);
								$app['orm.em']->flush();
								
								$campo = new \Entity\Campo_crm;	
								$campo->setUserId( $user->getId() );
								$campo->setCampo( 'location' );
								$campo->setValor( strtoupper($nombrepro->getCTRSededeDictadoc()) );
								$campo->setUid('preestablecido');
								$campo->setPag('preestablecido');
								$app['orm.em']->persist($campo);
								$app['orm.em']->flush();
							
							if( $array_post['ctrorigendelregistroc'] == 'RIADMISION'){
								$campo = new \Entity\Campo_crm;	
								$campo->setUserId( $user->getId() );
								$campo->setCampo( 'plan' );
								$campo->setValor( strtoupper($nombrepro->getCTRVirtualoPresencialc()) );
								$campo->setUid('preestablecido');
								$campo->setPag('preestablecido');
								$app['orm.em']->persist($campo);
								$app['orm.em']->flush();
							}
					endif;
				}
			
				//update usuario_crm
				$qb = $app['orm.em']->createQueryBuilder();
				$q = $qb->update('Entity\Usuario_crm', 'c')
						->set('c.cola', $qb->expr()->literal('0') )
						->set('c.procesando', $qb->expr()->literal('0') )
						->where( $qb->expr()->eq('c.id', '?1') )
						->setParameter(1, $user->getId())->getQuery();
				$p = $q->execute();
				$app['orm.em']->flush();
				
			
			$lng = $request->get('lng');
			if($lng == 'es'){
            	$msg = 'Gracias por enviarnos sus datos, dentro de breve recibira un email con la confirmaciòn de su registro.';
			}else{
				$msg = 'Thanks for contacting us, you will soon receive an answer';
			}
			
			
			
			return json_encode( array('success' => 1 ,'msg'=>$msg) );       
		}else{
			return json_encode( array('success' => 0 ,'msg'=>$msg) );
		}
    }
    public function upload(Request $request,Application $app){
		$array_post = $_POST;
		$array_post_file = $_FILES;
		$report = [];
		$data = [];
		//frm_dataform
		$uid = $request->get('uid');
	
		if( !empty($array_post_file) ){

			if( !empty($array_post_file) ){
				$files=[];
				$i = 0;
			
				foreach( $array_post_file as $key => $apf ){
				$report[]['file'] = $this->upload_files($request,$key,$uid);
				}
			}
			
			return '';       
		}else{
			return 0;
		}
    }
	private  function upload_files($request,$key,$extra){
		$win = "sync\app\controllers\Web";
		$unix = "sync/app/controllers/Web";
		$ruta = str_replace($win,'',__DIR__).'uploads';
		
		$file = $request->files->get( $key );
		$file->move($ruta,$extra.'-'.str_replace( array($file->getClientOriginalExtension(),'.'),array('',''),$file->getClientOriginalName()).'.'.$file->getClientOriginalExtension());
		//$file->move($ruta,$file->getClientOriginalName());	
		return $extra.'-'.str_replace( array($file->getClientOriginalExtension(),'.'),array('',''),$file->getClientOriginalName()).'.'.$file->getClientOriginalExtension();
	}

}
?>