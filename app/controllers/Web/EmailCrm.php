<?php
namespace Web;
class EmailCrm{
	public function syncEmailUserCRM($app,$finalmail,$usuarioid){
		/*
		$usuarios = DB::table('usuario_crm')
				->where('tipo','=','1')
				->where('crm','=','1')
				->where('email','=','0')
				->where('try','!=','4')
				->orderBy('id','ASC')
				->get();
		*/
		
		$usuarios = $app['orm.em']->getConnection()->prepare("SELECT DISTINCT * FROM usuario_crm WHERE tipo=1 and crm in (1,5) and email=0 AND try!=4 and id='".$usuarioid."' ORDER BY id ASC");
        $usuarios->execute();
        $usuarios = $usuarios->fetchAll();
		
		$correos = [];
		
		if(count($usuarios)>0){
			foreach($usuarios as $usuario){
				
				//$campos = DB::table('campo_crm')->where('user_id','=',$usuario->id)->get();
				$campos = $app['orm.em']->getRepository('Entity\Campo_crm')->findBy( array('user_id'=>$usuario['id']) );
				if(count($campos)==0){
					$mensaje = 'No existen campos del usuario no se puede enviar email';
					
					//DB::table('usuario_crm')->where('user_id','=',$usuario->id)->update(array('email'=>'1','error'=>$mensaje));
						
					$qb = $app['orm.em']->createQueryBuilder();
                    $q = $qb->update('Entity\Usuario_crm', 'u')
                                ->set('u.email', $qb->expr()->literal('1') )
                                ->set('u.error', $qb->expr()->literal($mensaje) )
                                ->where('u.id = ?1')
                                ->setParameter(1, $usuario['id'])
                                ->getQuery();
                    $p = $q->execute();	
					continue;
				}
				$dataForm = new \stdClass();
				foreach($campos as $campo){
					$dataForm->{$campo->getCampo()} = $campo->getValor();
				}

				//$vendedor = array($dataForm->email_final);
				/*
				$dataFi = array('dataForm'=>$dataForm);*/
				$paterno = isset($dataForm->lastname)?$dataForm->lastname:'';
				$materno = isset($dataForm->persondeoctrapellidomaternoc)?$dataForm->persondeoctrapellidomaternoc:'';
				$nombre = isset($dataForm->firstname)?$dataForm->firstname:'';
				$dni =  isset($dataForm->persondeoctrnrodedocumentoc)?$dataForm->persondeoctrnrodedocumentoc:'';
				
				$subject = isset($dataForm->origen)?$dataForm->origen:'';
				$subject = $subject.' - '.$paterno.' '.$materno.', '.$nombre.' - '.$dni;
				
				//$correos_add = DB::table('correo')->where('co_codigo_programa','=',$dataForm->programa)->where('co_estado','=','1')->get();
				
				$correos_add = $app['orm.em']->getConnection()->prepare("select p.CTREnviodeMailalumno_c as enviar from programas p where p.CTRNumProd_c='".$dataForm->programa."'");
				$correos_add->execute();
				$correos_add = $correos_add->fetchAll();
	
	
				if(count($correos_add)>0){
					foreach($correos_add as $correo){
						
						$correos[] = [ 'enviar' => $correo['enviar'] ];
					}
				}
				
				foreach($correos as $email_usuario){
					
					
					
				
					/**/
				switch ($dataForm->ctrorigendelregistroc) {
					case 'RIADMISION':
							$mensaje = $app['twig']->render('mail03.html.twig',
							array(
								'dataform' => $dataForm,
								'fecha' => date('Y-m-d')
								)
							);
					break;
					case 'RIEVENTO':
						$mensaje = $app['twig']->render('mail01.html.twig',
							array(
								'dataform' => $dataForm,
								'fecha' => date('Y-m-d')
								)
							);
					break;
					case 'RICHARLA':
					default:
						$mensaje = $app['twig']->render('mail02.html.twig',
							array(
								'dataform' => $dataForm,
								'fecha' => date('Y-m-d')
								)
							);
					break;

				}
					
					if($email_usuario['enviar'] == 'false'){
								//$error = 'Programa no se encuentra activo';
								$qb = $app['orm.em']->createQueryBuilder();
								$q = $qb->update('Entity\Usuario_crm', 'u')
											->set('u.email', $qb->expr()->literal( '0' ) )
											->set('u.error', $qb->expr()->literal( '' ))
											->where('u.id = ?1')
											->setParameter(1, $usuario['id'])
											->getQuery();
								$p = $q->execute();
								$app['orm.em']->flush();
					}
					/**/
					
					$app['mail']->addAddress($dataForm->email_final,'' );
				
					if($email_usuario['enviar'] == 'true'){
						$app['mail']->addBCC($dataForm->persondeoctrcorreopucpc,'');
					}
					$app['mail']->addCC('centrum.informes@pucp.pe', '');
					$app['mail']->isHTML(true);
					$app['mail']->Subject = $subject;
					$app['mail']->Body    = $mensaje;
					$app['mail']->AltBody = 'Copyright 2017 PUCP';

					if(!$app['mail']->send()){
						return '0'; //json_encode( array('error'=>  ) );
						} else {
								
								$qb = $app['orm.em']->createQueryBuilder();
								$q = $qb->update('Entity\Usuario_crm', 'u')
											->set('u.email', $qb->expr()->literal( '1' ) )
											->set('u.error', $qb->expr()->literal( '' ))
											->where('u.id = ?1')
											->setParameter(1, $usuario['id'])
											->getQuery();
								$p = $q->execute();
								$app['orm.em']->flush();
								
						return '1';//json_encode( array('success' => 1  ) );
					}
					
				}

				//DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('email'=>'1'));
				
			}
		
		}
		return '0';
	}
}
?>