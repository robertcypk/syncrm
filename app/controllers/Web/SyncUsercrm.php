<?php

namespace Web;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Silex\ControllerProviderInterface;
use Web\BuscarLead;
use Web\Soap;
use Web\Person;
use Web\Buscarleadfind;
use Web\Createlead;
use Web\Updatelead;
use Web\Createcontact;
use Web\Checkcontact;
class SyncUsercrm{
	var $wsTerritorio = 'https://cang-test.crm.us2.oraclecloud.com:443/salesTerrMgmtTerritories/TerritoryPublicService?WSDL';
	var $wsCatalago = 'https://cang-test.crm.us2.oraclecloud.com:443/ocSalesCatalogBrowse/SalesCatalogRuntimeService?WSDL';
	var $wsContacto = 'https://cang-test.crm.us2.oraclecloud.com:443/crmCommonSalesParties/ContactService?WSDL';
	var $wsPerson = 'https://cang-test.crm.us2.oraclecloud.com:443/foundationParties/PersonService?WSDL';
	var $wsResource = 'https://cang-test.crm.us2.oraclecloud.com:443/foundationResources/ResourceService?WSDL';
	var $wsLead = 'https://cang-test.crm.us2.oraclecloud.com:443/mklLeads/SalesLeadService?WSDL';
	var $wsPrograma = 'https://cang-test.crm.us2.oraclecloud.com:443/mktExtensibility/MarketingCustomObjectService?WSDL';
	var $yearFi = 2;
	var $operation = '';
	public function sync(Request $request,Application $app){
       
	    $usuarios = $app['orm.em']->getConnection()->prepare("SELECT DISTINCT * FROM usuario_crm WHERE tipo=1 AND crm=0 AND try!=4 ORDER BY id ASC");
        $usuarios->execute();
        $usuarios = $usuarios->fetchAll();
		if(count($usuarios)>0){
            
			/*
			$info = DB::table('sailor_vendedor')->count();
			if($info>0){
				if($info==1){
					DB::table('sailor_vendedor')->insert(array('programa_id'=>2, 'vendedor_id'=>2));
					return 'El proceso tiene que esperar se esta procesando otro';
				}
				if($info==2){
					DB::table('sailor_vendedor')->insert(array('programa_id'=>3, 'vendedor_id'=>3));
					return 'El proceso tiene que esperar se esta procesando otro';
				}
				if($info==3){
					DB::table('sailor_vendedor')->delete();
					DB::table('sailor_vendedor')->insert(array('programa_id'=>1, 'vendedor_id'=>1));
				}

			}else{
				DB::table('sailor_vendedor')->insert(array('programa_id'=>1, 'vendedor_id'=>1));
			}
            */
            //$app["orm.em"]->getRepository("Entity\Po")->findBy( array('estado'=>3),array('fechaentrega'=>'DESC'),60 );
            
			foreach($usuarios as $usuario){
				
				$campos = $app['orm.em']->getRepository('Entity\Campo_crm')->findBy( array('user_id'=>$usuario['id']) );
				
				if(count($campos)==0){
					$mensaje = 'No existen campos del usuario';
					/*
                    DB::table('usuario_crm')
						->where('user_id','=',$usuario->id)
						->update(array('crm'=>'1','error'=>$mensaje));
                    */
					$qb = $app['orm.em']->createQueryBuilder();
                    $q = $qb->update('Entity\Usuario_crm', 'u')
                                ->set('u.crm', $qb->expr()->literal(1) )
                                 ->set('u.error', $qb->expr()->literal($mensaje) )
                                ->where('u.id = ?1')
                                ->setParameter(1, $usuario['id'])
                                ->getQuery();
                    $p = $q->execute();
					continue;
				}

                //Cargar campos y valores del formulario
				$dataForm = new \stdClass();
				foreach($campos as $campo){
					$dataForm->{$campo->getCampo()} = $campo->getValor();
				}
				
				$contacto = new Checkcontact();
				$contacto = $contacto->checkContactf($dataForm,1, $usuario['id'],$app,$this->wsContacto);
			
				//registro de error
				if(!isset($contacto)){
					$mensaje = 'Error en la verificación del usuario';
					$qb = $app['orm.em']->createQueryBuilder();
                    $q = $qb->update('Entity\Usuario_crm', 'u')
                            ->set('u.try', $qb->expr()->literal( ($usuario['try']+1) ) )
                            ->set('u.error', $qb->expr()->literal($mensaje) )
                            ->where('u.id = ?1')
                            ->setParameter(1, $usuario['id'])
                            ->getQuery();
                    $p = $q->execute();
					continue;
				}
                
				//existe usuario?
				if($contacto){
					echo $this->updateContact($contacto, $dataForm, $usuario,$app);
				}else{
                    $createcontact = new Createcontact();
					$create_contact = $createcontact->createContact($dataForm, $usuario,$app);
					echo $create_contact;
				}
			}

			//DB::table('sailor_vendedor')->delete();
		}
		return 'Se realizó exitosamente la carga de usuarios';
    }
    
    public function updateContact($contacto, $dataForm, $usuario,$app){
		$buscarlead = new BuscarLead();
		
		//buscar lead del usuario
		$leads = $buscarlead->buscarLead($contacto, 1);
		if(isset($leads)){

			//tiene prioridad?
			if(!isset($dataForm->prioridad)){
				$dataForm->prioridad = 'U';
			}
			switch ($dataForm->prioridad) {
				case 'U':
					$leads = $this->determinarUltimo($leads);
					break;
				case 'P':
					$leads = $this->determinarPrimero($leads);
					break;
				default:
					$leads = $this->determinarUltimo($leads);
					break;
			}

			//tiene vendedores este programa?
            $vendedores = $app['orm.em']->getRepository('Entity\Resource')->findBy( array('Id'=>$dataForm->programa),array('CTROrdenRuleta_c'=> 'ASC') );
			if(count($vendedores)==0){
				$error = 'El programa no tiene vendedores activos al actualizar';
				$qb = $app['orm.em']->createQueryBuilder();
                $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal( ($usuario['try']+1) ) )
                        ->set('u.error', $qb->expr()->literal( $error ) )
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
                $p = $q->execute();    
				return $error;
			}

			//xml buscar lead
			foreach($leads as $le){
				
				$find = false;
				$vend = null;
				foreach($vendedores as $vendedor){
  					if($le['OwnerId']== $vendedor->ResourceId){
  						$vend = $vendedor;
  						$find = true;
  					}
  				}

  				if($find){
  					if(!isset($dataForm->tipodma)){
  						$dataForm->atendido = $vend->ResourceId;
  						$dataForm->ownerpartyid = $dataForm->atendido;
  						$lead = $this->executeUpdateContact($contacto, $dataForm, $usuario,$app);
  						if(isset($lead['OwnerId'])){
  							$this->validateUpdateContact($vendedores,$lead, $le, $usuario, $dataForm, $vend,$app);
  						}

  						return 'Se registro correctamente';
				  	}

				  	if($dataForm->tipodma=="N"){
				  		$dataForm->atendido = $vend->ResourceId;
  						$dataForm->ownerpartyid = $dataForm->atendido;
  						$lead = $this->executeUpdateContact($contacto, $dataForm, $usuario,$app);
  						if(isset($lead['OwnerId'])){
  							$this->validateUpdateContact($vendedores,$lead, $le, $usuario, $dataForm, $vend,$app);
  						}
  						return 'Se registro correctamente';
				  	}

				  	$val = '';
				  	if(!isset($dataForm->tipodma)) $val = 'year';
  					if($dataForm->tipodma=="") $val = 'year';
  					if($dataForm->tipodma=="A") $val = 'year';
  					if($dataForm->tipodma=="M") $val = 'month';
  					if($dataForm->tipodma=="D") $val = 'day';

  					$dataForm->valorc = isset($dataForm->valorc)?$dataForm->valorc:'';
  					$dataForm->valorc==""?$this->yearFi:$dataForm->valorc;
  					$today = time();
  					$before = strtotime('-'.$dataForm->valorc.' '.$val, $today);
  					if($le["fechafinal"]>=$before){
  						$dataForm->atendido = $vend->ResourceId;
  						$dataForm->ownerpartyid = $dataForm->atendido;
  						$lead = $this->executeUpdateContact($contacto, $dataForm, $usuario,$app);
  						if(isset($lead['OwnerId'])){
  							$this->validateUpdateContact($vendedores,$lead, $le, $usuario, $dataForm, $vend,$app);
  						}
  						return 'Se registro correctamente';
  					}
  				}
  			}
		}

		$count = 1;
		
		if($dataForm->atendido=="NSNR"){
			$vendedores = $app['orm.em']->getRepository('Entity\Resource')->findBy( array('Id'=>$dataForm->programa,'CTRRuleta_c'=>'true'),array('CTROrdenRuleta_c', 'ASC') );
			if(count($vendedores)==0){
				$error = 'El programa no tiene vendedores para la ruleta';
				//DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('try'=>($usuario->try+1), 'error'=>$error));
				$qb = $app['orm.em']->createQueryBuilder();
                $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal( ($usuario['try']+1) ) )
						->set('u.error', $qb->expr()->literal( $error ) )
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
                $p = $q->execute();
				return $error;
			}
			
			//
			//$nextVendedor = DB::table('sailor')->where('producto','=',$dataForm->programa)->where('posicion','!=','0')->first();
			
			$nextVendedor = $app['orm.em']->getConnection()
			->prepare("SELECT DISTINCT * FROM sailor WHERE producto='{$dataFrom->programa}' AND posicion!=0");
        	$nextVendedor->execute();
        	$nextVendedor = $nextVendedor->fetchAll();
			
			// next vendedor si existe
			if(isset($nextVendedor)){
				
				$nextVendedor = $nextVendedor[0]['posicion'];
			}else{
				$nextVendedor = 0;
			}
			
			$vend = null; 
			
			if($nextVendedor==0){
  				$vend = $vendedores[0];
  			}else{
  				foreach($vendedores as $vendedor){
  					if($count == $nextVendedor){
  						$vend = $vendedor;
  						break;
  					}
  					$count++;
  				}
  			}
			
  			if(!isset($vend)){
  				$vend = $vendedores[0];
  				$count = 1;
  			}
			
		}else{

			$vend = $app['orm.em']->getRepository('Entity\Resource')
								  ->findOneBy( 
										array('Id'=>$dataForm->programa,
											  'ResourceId'=>$dataForm->atendido
											  ) 
											);
			if(!isset($vend)){
				$error = 'El programa no tiene el vendedor indicado';
				$qb = $app['orm.em']->createQueryBuilder();
                $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal( ($usuario['try']+1) ) )
						->set('u.error', $qb->expr()->literal( $error ) )
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
                $p = $q->execute();
				return $error;
			}
			
		}

		//assignar datos
		$dataForm->atendido = $vend->ResourceId;
  		$dataForm->ownerpartyid = $dataForm->atendido;
		$vendedores_2 = $app['orm.em']->getRepository('Entity\Resource')
									  ->findBy( array('Id'=>$dataForm->programa),array('CTROrdenRuleta_c', 'ASC') );
  		$lead = $this->executeUpdateContact($contacto, $dataForm, $usuario,$app);
		
  		if(isset($lead['OwnerId'])){
			
  			if($lead['OwnerId'] != $vend->ResourceId){
				
	  			$vend = null; //vuelve a inicializar
	  			foreach($vendedores_2 as $vendedor){ //recorre el registro de vendedores otra vez
	  				if($lead['OwnerId'] == $vendedor->getResourceId() ){
	  					$vend = $vendedor;
	  				}
	  			}
				
	  			if(!isset($vend)){ //no existen vendedor?
	  				$error = 'Se creo el contacto y el lead pero no se va poder enviar correo xq la vendedora no esta en los usuarios';
					$qb = $app['orm.em']->createQueryBuilder();
					$q = $qb->update('Entity\Usuario_crm', 'u')
							->set('u.try', $qb->expr()->literal( ($usuario['try']+1) ) )
							->set('u.error', $qb->expr()->literal( $error ) )
							->where('u.id = ?1')
							->setParameter(1, $usuario['id'])
							->getQuery();
					$p = $q->execute();
				}
				
			}
			
			// existe vendedor de programa?
			if(isset($vend)){
				// existen vendedores de programa en recursos?
				if(isset($vendedores)){
					
					//DB::table('sailor')->where('producto','=',$dataForm->programa)->delete();
					
					$delete_product = $app["orm.em"]->getRepository('Entity\Sailor')->findOneBy( 
					array('producto'=> $dataForm->programa) );
					$app["orm.em"]->remove($delete_product);
					$app["orm.em"]->flush();

					$count_2 = 1;
					$vend_2 = null;
					//tabla resource
					foreach($vendedores as $vendedor){
						if($vend->ResourceId == $vendedor->getResourceId() ){
							$vend_2 = $vendedor;
						}
					}
					
					
					if(!isset($vend_2)){
						$error = 'Se creo el contacto y el lead pero no se va poder enviar correo xq la vendedora no esta en la ruleta';
						$qb = $app['orm.em']->createQueryBuilder();
						$q = $qb->update('Entity\Usuario_crm', 'u')
								->set('u.try', $qb->expr()->literal( ($usuario['try']+1) ) )
								->set('u.error', $qb->expr()->literal( $error ) )
								->where('u.id = ?1')
								->setParameter(1, $usuario['id'])
								->getQuery();
						$p = $q->execute();
					}else{ // tabla sailor
						
						// loop vendedores encontrados
						foreach($vendedores as $vendedor){
							
							$pos = 0;
							if($count == $count_2){
								$pos = $count+1;
								if($pos > count($vendedores)){
									$pos = 1;
								}
							}
							
							//tabla sailor
							$sailor_in = new \Entity\Sailor;
							$sailor_in->setId($count_2);
							$sailor_in->setPosicion( $pos ); 
							$sailor_in->setEstado( 1 );
							$sailor_in->setProducto( $dataFrom->programa );
							$sailor_in->setVendedor( $vendedores->getResourceId() );

							$count_2++;
						}
						
						$qb = $app['orm.em']->createQueryBuilder();
						$q = $qb->update('Entity\Usuario_crm', 'u')
								->set('u.crm', $qb->expr()->literal( '1' ) )
								->where('u.id = ?1')
								->setParameter(1, $usuario['id'])
								->getQuery();
						$p = $q->execute();
						
						$campo_crm = new \Entity\Campo_crm;
						$campo_crm->setUserid( $usuario['id'] );
						$campo_crm->setCampo( 'email_final' );
						$campo_crm->setValor( $vend->EmailAddress );
						$campo_crm->setUid('preestablecido');
						$campo_crm->setPag('preestablecido');
						$app['orm.em']->persist($campo_crm);
						$app['orm.em']->flush();
						
						//
					}
				}


			}
			//------------------------------
		}

		return 'Se registro correctamente';
	}
    
    /* Execute Update Contact */
    public function executeUpdateContact($contacto, $dataForm, $usuario,$app){
		$userUpdated = $this->updateContactMassive($contacto, $dataForm, 1);
  		if(!isset($userUpdated)){
			$error = 'Error al actualizar un contacto';
			//DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('try'=>($usuario->try+1), 'error'=>$error));
				$qb = $app['orm.em']->createQueryBuilder();
                $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal( ($usuario['try']+1) ) )
                        ->set('u.error', $qb->expr()->literal( $error ) )
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
                $p = $q->execute();    
			return $error;
		}

		if($dataForm->origen==1){ // admision
            $merged = $this->mergeContacto($contacto,$dataForm, 1);
            if(!isset($merged)){
            	$error = 'Error al realizar el merge del contacto';
				//DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('try'=>($usuario->try+1), 'error'=>$error));
				$qb = $app['orm.em']->createQueryBuilder();
                $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal( ($usuario['try']+1) ) )
                        ->set('u.error', $qb->expr()->literal( $error ) )
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
                $p = $q->execute();    
				return $error;
            }
        }

        $this->updatePerson($contacto, $dataForm);
        if(!isset($dataForm->productId)){ //CTRNumProd_c
        	$error = 'Error el programa no tiene product id';
				//DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('try'=>($usuario->try+1), 'error'=>$error));
				$qb = $app['orm.em']->createQueryBuilder();
                $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal( ($usuario['try']+1) ) )
                        ->set('u.error', $qb->expr()->literal( $error ) )
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
                $p = $q->execute();
				return $error;
        }
		$buscarleadfind = new Buscarleadfind();
        $anterior = $buscarleadfind->buscarLeadFind($contacto,$dataForm, 1);
        if(!isset($anterior)){
			$createlead = new Createlead();
            $lead = $createlead->createLead($contacto,$dataForm,1);
            if(!isset($lead)){
				$error = 'Error al crear el lead de un contacto ya creado';
				//DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('try'=>($usuario->try+1), 'error'=>$error));
				$qb = $app['orm.em']->createQueryBuilder();
                $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal( ($usuario['try']+1) ) )
                        ->set('u.error', $qb->expr()->literal( $error ) )
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
                $p = $q->execute();
				return $error;
			}
			return $lead;
		}else{
			$updatelead = new Updatelead();
			$lead = $updatelead->updateLead($anterior,$dataForm, 1);
			if(!isset($lead)){
				$error = 'Error al crear el lead de un contacto ya creado';
				//DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('try'=>($usuario->try+1), 'error'=>$error));
				$qb = $app['orm.em']->createQueryBuilder();
                $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal( ($usuario['try']+1) ) )
                        ->set('u.error', $qb->expr()->literal( $error ) )
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
                $p = $q->execute();
				return $error;
			}
			return $lead;
        }
    }

	/* Validate Update Contact */
	public function validateUpdateContact($vendedores,$lead, $le, $usuario, $dataForm, $vend,$app){
		$qb = $app['orm.em']->createQueryBuilder();

    	if($lead['OwnerId'] != $le['OwnerId']){
  			$vend = null;
  			foreach($vendedores as $vendedor){
  				if($lead['OwnerId'] == $vendedor->ResourceId){
  					$vend = $vendedor;
  				}
  			}
  			if(!isset($vend)){
  				$error = 'Se creo el contacto y el lead pero no se va poder enviar correo';
				//DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('try'=>($usuario->try+1), 'error'=>$error));
				
                $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal( ($usuario['try']+1) ) )
                        ->set('u.error', $qb->expr()->literal( $error ) )
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
                $p = $q->execute();
			}
		}

		if(isset($vend)){

			/* SAILOR LOG 
			DB::table('sailor_log')->insert(array(
				'producto'=>$dataForm->programa
				,'vendedor'=>$vend->ResourceId
				,'fecha_reg'=>time()
			));
			*/

			//DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('crm'=>'1'));
			$q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.crm', $qb->expr()->literal( 1 ) )
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
            $p = $q->execute();

			//DB::table('campo_crm')->insert(array('user_id'=>$usuario->id, 'campo'=>'email_final', 'valor'=>$vend->EmailAddress));
			$campo_crm = new \Entity\Campo_crm;
			$campo_crm->setUserid( $usuario['id'] );
			$campo_crm->setCampo( 'email_final' );
			$campo_crm->setValor( $vend->EmailAddress );
			$campo_crm->setUid('preestablecido');
			$campo_crm->setPag('preestablecido');
			$app['orm.em']->persist($campo_crm);
			$app['orm.em']->flush();	
		}
    }

	/* Merge Contacto */
	public function mergeContacto($contacto, $dataForm, $try){
		$client = Soap::getClient($this->wsContacto);
		$soapaction = "http://xmlns.oracle.com/apps/crmCommon/salesParties/contactService/mergeContact";
		$request = $this->mergeContactoRequest($contacto, $dataForm);
		if($request===true)
			return true;
		$response = $client->send($request, $soapaction, '');
		if(isset($response['result'])){
			return $response["result"];
		}

		$try += 1;
		if($try<3)
			return $this->mergeContacto($contacto, $dataForm, $try);
	}

	public function mergeContactoRequest($contacto, $user){

		$request_xml = '';
        if(isset($user->ctrgradoacademicoc) || isset($user->ctrinstitucionacademicac)
        	|| isset($user->ctrotrasuniversidadesinstc) || isset($user->ctrespecialidadc)
        		|| isset($user->ctranomesquefinalizoestsupc) || isset($user->ctrnivelacademicoc)){
            $request_xml .= '
        		<con:PersonDEO_InformacionAcademicaCollection_c>
                    '.(isset($user->ctrgradoacademicoc)?'<per:CTRGradoacademico_c>'.$user->ctrgradoacademicoc.'</per:CTRGradoacademico_c>':'').'
                    '.(isset($user->ctrinstitucionacademicac)?'<per:CTRInstitucionAcademica_c>'.$user->ctrinstitucionacademicac.'</per:CTRInstitucionAcademica_c>':'').'
                    '.(isset($user->ctrotrasuniversidadesinstc)?'<per:CTROtrasUniversidadesInst_c>'.$user->ctrotrasuniversidadesinstc.'</per:CTROtrasUniversidadesInst_c>':'').'
                    '.(isset($user->ctrespecialidadc)?'<per:CTREspecialidad_c>'.$user->ctrespecialidadc.'</per:CTREspecialidad_c>':'').'
                    '.(isset($user->ctranomesquefinalizoestsupc)?'<per:CTRAnoMesquefinalizoEstSup_c>'.$user->ctranomesquefinalizoestsupc.'</per:CTRAnoMesquefinalizoEstSup_c>':'').'
                    '.(isset($user->ctrnivelacademicoc)?'<per:CTRNivelacademico_c>'.$user->ctrnivelacademicoc.'</per:CTRNivelacademico_c>':'').'
                </con:PersonDEO_InformacionAcademicaCollection_c>';
        }

        if(isset($user->ctrinstituciondeidiomasc) || isset($user->ctrnivelalcanzadoc)
        	|| isset($user->ctraniomesfinalizoidiomac) || isset($user->ctrciudadc)){
            $request_xml .= '
        		<con:PersonDEO_IdiomaCollection_c>
                    '.(isset($user->ctrinstituciondeidiomasc)?'<per:CTRInstituciondeidiomas_c>'.$user->ctrinstituciondeidiomasc.'</per:CTRInstituciondeidiomas_c>':'').'
                    '.(isset($user->ctrnivelalcanzadoc)?'<per:CTRNivelalcanzado_c>'.$user->ctrnivelalcanzadoc.'</per:CTRNivelalcanzado_c>':'').'
                    '.(isset($user->ctraniomesfinalizoidiomac)?'<per:CTRAnioMesfinalizoIdioma_c>'.$user->ctraniomesfinalizoidiomac.'</per:CTRAnioMesfinalizoIdioma_c>':'').'
                    '.(isset($user->ctrciudadc)?'<per:CTRCiudad_c>'.$user->CTRCiudad_c.'</per:CTRCiudad_c>':'').'
                </con:PersonDEO_IdiomaCollection_c>';
        }

        if((isset($user->addresselementattribute2) || isset($user->addresselementattribute3)
        	|| isset($user->addressline1) || isset($user->country) || isset($user->city))
        	 && isset($contacto["PrimaryAddress"])==false){
            $request_xml .= '
                <con:PrimaryAddress>
                	<com:PartyId>'.$contacto['PartyId'].'</com:PartyId>
                	'.(isset($user->addresselementattribute2)?'<com:AddressElementAttribute2>'.$user->addresselementattribute2.'</com:AddressElementAttribute2>':'').'
                    '.(isset($user->addresselementattribute3)?'<com:AddressElementAttribute3>'.$user->addresselementattribute3.'</com:AddressElementAttribute3>':'').'
                    '.(isset($user->addressline1)?'<com:AddressLine1>'.$user->addressline1.'</com:AddressLine1>':'').'
                    '.(isset($user->country)?'<com:Country>'.$user->country.'</com:Country>':'').'
                    '.(isset($user->city)?'<com:City>'.$user->city.'</com:City>':'').'
                </con:PrimaryAddress>';
        }

        if(empty($request_xml)){
        	return true;
        }

		$request_xml ='
			<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
				xmlns:typ="http://xmlns.oracle.com/apps/crmCommon/salesParties/contactService/types/"
				xmlns:con="http://xmlns.oracle.com/apps/crmCommon/salesParties/contactService/"
				xmlns:com="http://xmlns.oracle.com/apps/crmCommon/salesParties/commonService/"
				xmlns:not="http://xmlns.oracle.com/apps/crmCommon/notes/noteService"
				xmlns:not1="http://xmlns.oracle.com/apps/crmCommon/notes/flex/noteDff/"
				xmlns:per="http://xmlns.oracle.com/apps/cdm/foundation/parties/personService/">
            	<soapenv:Body>
                	<typ:mergeContact>
                    	<typ:contact>
                            <con:PartyId>'.$contacto['PartyId'].'</con:PartyId>
                            '.$request_xml.'
                        </typ:contact>
                    </typ:mergeContact>
                </soapenv:Body>
            </soapenv:Envelope>';

        return $request_xml;
	}

	/* Update person */
	public function updatePerson($contacto, $dataForm){
			$person = new Person();
			$emails = $person->getPerson($contacto, 1);
			if(isset($emails)){
				if(isset($emails['Email'])){
					$client = Soap::getClient($this->wsPerson);
					$soapaction = "http://xmlns.oracle.com/apps/cdm/foundation/parties/personService/applicationModule/mergePerson";
					$request = $this->updatePersonRequest($emails['Email'], $dataForm, $contacto);
					if(!empty($request)){
						$response = $client->send($request, $soapaction, '');
					}
				}
			}
	}

	public function updatePersonRequest($emails, $user, $contacto){
		$email1 = true;
        $email2 = true;
        $arraEmail = array();
        if(isset($emails['EmailAddress']))
        	$arraEmail[] = $emails;
		else
			$arraEmail = $emails;
		foreach($arraEmail as $email){
        	if(isset($user->emailaddress2)){
        		if($user->emailaddress2 == $email["EmailAddress"])
        			$email1 = false;
        	}else{
        		$email1 = false;
        	}

        	if(isset($user->persondeoctrcorreopucpc)){
        		if($user->persondeoctrcorreopucpc == $email["EmailAddress"])
        			$email2 = false;
        	}else{
        		$email2 = false;
        	}
        }

        $request_xml_1 = '';
        if($email1){
			$request_xml_1 ='
				<per:Email>
					<con:OwnerTableName>HZ_PARTIES</con:OwnerTableName>
					<con:OwnerTableId>'.$contacto['PartyId'].'</con:OwnerTableId>
					<con:PrimaryFlag>false</con:PrimaryFlag>
					<con:ContactPointPurpose>BUSINESS</con:ContactPointPurpose>
					<con:EmailAddress>'.$user->emailaddress2.'</con:EmailAddress>
					<con:PrimaryByPurpose>N</con:PrimaryByPurpose>
					<con:CreatedByModule>HZ_WS</con:CreatedByModule>
				</per:Email>';
		}

		$request_xml_2 = '';
		if($email2){
			$request_xml_2 ='
				<per:Email>
					<con:OwnerTableName>HZ_PARTIES</con:OwnerTableName>
					<con:OwnerTableId>'.$contacto['PartyId'].'</con:OwnerTableId>
					<con:PrimaryFlag>false</con:PrimaryFlag>
					<con:ContactPointPurpose>PUCP</con:ContactPointPurpose>
					<con:EmailAddress>'.$user->persondeoctrcorreopucpc.'</con:EmailAddress>
					<con:PrimaryByPurpose>N</con:PrimaryByPurpose>
					<con:CreatedByModule>HZ_WS</con:CreatedByModule>
				</per:Email>';
		}

		if($email1 || $email2){
			$request_xml ='
				<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
					xmlns:typ="http://xmlns.oracle.com/apps/cdm/foundation/parties/personService/applicationModule/types/"
					xmlns:per="http://xmlns.oracle.com/apps/cdm/foundation/parties/personService/"
					xmlns:par="http://xmlns.oracle.com/apps/cdm/foundation/parties/partyService/"
					xmlns:sour="http://xmlns.oracle.com/apps/cdm/foundation/parties/flex/sourceSystemRef/"
					xmlns:con="http://xmlns.oracle.com/apps/cdm/foundation/parties/contactPointService/"
					xmlns:con1="http://xmlns.oracle.com/apps/cdm/foundation/parties/flex/contactPoint/"
					xmlns:par1="http://xmlns.oracle.com/apps/cdm/foundation/parties/flex/partySite/"
					xmlns:per1="http://xmlns.oracle.com/apps/cdm/foundation/parties/flex/person/"
					xmlns:rel="http://xmlns.oracle.com/apps/cdm/foundation/parties/relationshipService/"
					xmlns:org="http://xmlns.oracle.com/apps/cdm/foundation/parties/flex/orgContact/"
					xmlns:rel1="http://xmlns.oracle.com/apps/cdm/foundation/parties/flex/relationship/">
					<soapenv:Header/>
					<soapenv:Body>
						<typ:mergePerson>
						    <typ:personParty>
						        <per:PartyId>'.$contacto['PartyId'].'</per:PartyId>
						        '.$request_xml_1.'
								'.$request_xml_2.'
							</typ:personParty>
						</typ:mergePerson>
					</soapenv:Body>
				</soapenv:Envelope>';

				return $request_xml;

		}

		return '';
	}
	/* Update Contact Masive */
	public function updateContactMassive($contacto, $dataForm, $try){
		$soap = new Soap();
		$client = $soap->getClient($this->wsContacto);
		$soapaction = "http://xmlns.oracle.com/apps/crmCommon/salesParties/contactService/updateContact";
		$request = $this->updateContactRequest($contacto, $dataForm);
		$response = $client->send($request, $soapaction, '');
		if(isset($response['result'])){
			return $response["result"];
		}
		$try += 1;
		if($try<3)
			return $this->updateContactMassive($contacto, $dataForm, $try);
	}

	public function updateContactRequest($contacto, $dataForm){
		$request_xml ='
			<soapenv:Envelope
                xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                xmlns:ns1="http://xmlns.oracle.com/apps/crmCommon/salesParties/contactService/"
                xmlns:ns2="http://xmlns.oracle.com/apps/crmCommon/salesParties/commonService/"
                xmlns:ns3="http://xmlns.oracle.com/apps/cdm/foundation/parties/personService/"
                xmlns:ns4="http://xmlns.oracle.com/apps/crmCommon/salesParties/contactService/types/"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                <soapenv:Body>
                	<ns4:updateContact>
                    	<ns4:contact>
                        	<ns1:PartyId>'.$contacto["PartyId"].'</ns1:PartyId>
                            '.(isset($dataForm->salutoryintroduction)?'<ns1:SalutoryIntroduction>'.$dataForm->salutoryintroduction.'</ns1:SalutoryIntroduction>':'').'
                            '.(isset($dataForm->firstName)?'<ns1:FirstName>'.$dataForm->firstName.'</ns1:FirstName>':'').'
                            '.(isset($dataForm->lastName)?'<ns1:LastName>'.$dataForm->lastName.'</ns1:LastName>':'').'
                            '.(isset($dataForm->persondeoctrapellidomaternoc)?'<ns1:Persondeo_CTRApellidoMaterno_c>'.$dataForm->persondeoctrapellidomaternoc.'</ns1:Persondeo_CTRApellidoMaterno_c>':'').'
                            <ns1:Persondeo_ctrtipodedocumento_c>'.$dataForm->persondeoctrtipodedocumentoc.'</ns1:Persondeo_ctrtipodedocumento_c>
                            <ns1:Persondeo_ctrnrodedocumento_c>'.$dataForm->persondeoctrnrodedocumentoc.'</ns1:Persondeo_ctrnrodedocumento_c>
                            '.(isset($dataForm->homephonenumber)?'<ns1:HomePhoneNumber>'.$dataForm->homephonenumber.'</ns1:HomePhoneNumber>':'').'
                            '.(isset($dataForm->mobilenumber)?'<ns1:MobileNumber>'.$dataForm->mobilenumber.'</ns1:MobileNumber>':'');

        if((isset($dataForm->addresselementattribute2) || isset($dataForm->addresselementattribute3)
        	|| isset($dataForm->addressline1) || isset($dataForm->country) || isset($dataForm->City))
        	&& isset($contacto["PrimaryAddress"])){
            $request_xml .= '
                            <ns1:PrimaryAddress>
                                '.(isset($dataForm->addresselementattribute2)?'<ns2:AddressElementAttribute2>'.$dataForm->addresselementattribute2.'</ns2:AddressElementAttribute2>':'').'
                                '.(isset($dataForm->addresselementattribute3)?'<ns2:AddressElementAttribute3>'.$dataForm->addresselementattribute3.'</ns2:AddressElementAttribute3>':'').'
                                '.(isset($dataForm->addressline1)?'<ns2:Addressline1>'.$dataForm->addressline1.'</ns2:Addressline1>':'').'
                                '.(isset($dataForm->country)?'<ns2:Country>'.$dataForm->country.'</ns2:Country>':'').'
                                '.(isset($dataForm->city)?'<ns2:City>'.$dataForm->city.'</ns2:City>':'').'
                            </ns1:PrimaryAddress>';
        }
         $request_xml .= '
                        	<ns1:DateOfBirth>'.$dataForm->dateofbirth.'</ns1:DateOfBirth>
                            '.(isset($dataForm->persondeoctrpaisdenacimientoc)?'<ns1:Persondeo_CTRPaisdenacimiento_c>'.$dataForm->persondeoctrpaisdenacimientoc.'</ns1:Persondeo_CTRPaisdenacimiento_c>':'').'
                            '.(isset($dataForm->persondeoctrciudaddenacimientoc)?'<ns1:Persondeo_CTRCiudaddeNacimiento_c>'.$dataForm->persondeoctrciudaddenacimientoc.'</ns1:Persondeo_CTRCiudaddeNacimiento_c>':'').'
                            '.(isset($dataForm->persondeoctrnacionalidadc)?'<ns1:Persondeo_ctrnacionalidad_c>'.$dataForm->persondeoctrnacionalidadc.'</ns1:Persondeo_ctrnacionalidad_c>':'').'
                            '.(isset($dataForm->emailaddress)?'<ns1:EmailAddress>'.$dataForm->EmailAddress.'</ns1:EmailAddress>':'').'
                            '.(isset($dataForm->persondeoctrcorreopucpc)?'<ns1:Persondeo_CTRCorreoPUCP_c>'.$dataForm->persondeoctrcorreopucpc.'</ns1:Persondeo_CTRCorreoPUCP_c>':'').'
                            '.(isset($dataForm->persondeoctrcuentaskypec)?'<ns1:Persondeo_CTRCuentaSkype_c>'.$dataForm->persondeoctrcuentaskypec.'</ns1:Persondeo_CTRCuentaSkype_c>':'').'
                            '.(isset($dataForm->persondeoctrexalumno_c)?'<ns1:Persondeo_CTRExalumno_c>'.$dataForm->persondeoctrexalumnoc.'</ns1:Persondeo_CTRExalumno_c>':'').'
                            '.(isset($dataForm->persondeoctrcodigopucpc)?' <ns1:Persondeo_CTRCodigoPUCP_c>'.$dataForm->persondeoctrcodigopucpc.'</ns1:Persondeo_CTRCodigoPUCP_c>':'').'
                            '.(isset($dataForm->persondeoctrcompaniac)?'<ns1:Persondeo_CTRCompania_c>'.$dataForm->persondeoctrcompaniac.'</ns1:Persondeo_CTRCompania_c>':'').'
                            '.(isset($dataForm->jobtitle)?'<ns1:JobTitle>'.$dataForm->jobtitle.'</ns1:JobTitle>':'').'
                            '.(isset($dataForm->workphonenumber)?'<ns1:WorkPhoneNumber>'.$dataForm->workphonenumber.'</ns1:WorkPhoneNumber>':'').'
                            '.(isset($dataForm->workphoneextension)?'<ns1:WorkPhoneExtension>'.$dataForm->workphoneextension.'</ns1:WorkPhoneExtension>':'').'
                            '.(isset($dataForm->faxnumber)?'<ns1:FaxNumber>'.$dataForm->faxnumber.'</ns1:FaxNumber>':'').'
                            '.(isset($dataForm->persondeoctraniosdeexperienciac)?'<ns1:Persondeo_CTRAniosdeexperiencia_c>'.$dataForm->persondeoctraniosdeexperienciac.'</ns1:Persondeo_CTRAniosdeexperiencia_c>':'').'
                            '.(isset($dataForm->persondeoctrobservacionc)?' <ns1:Persondeo_CTRObservacion_c>'.$dataForm->persondeoctrobservacionc.'</ns1:Persondeo_CTRObservacion_c>':'').'
                            '.(isset($dataForm->persondeoctrprocedenciac)?' <ns1:Persondeo_CTRProcedencia_c>'.$dataForm->persondeoctrprocedenciac.'</ns1:Persondeo_CTRProcedencia_c>':'').'
                            '.(isset($dataForm->persondeoctrautorizodatospersonfinesmc)?' <ns1:Persondeo_CTRAutorizoDatosPersonFinesM_c>'.$dataForm->persondeoctrautorizodatospersonfinesmc.'</ns1:Persondeo_CTRAutorizoDatosPersonFinesM_c>':'').'
                            '.(isset($dataForm->persondeoctrautorizoenvinfprogacac)?' <ns1:Persondeo_CTRAutorizoEnvInfProgAca_c>'.$dataForm->persondeoctrautorizoenvinfprogacac.'</ns1:Persondeo_CTRAutorizoEnvInfProgAca_c>':'').'
                            '.(isset($dataForm->persondeoctrsalariomedioanualc)?' <ns1:Persondeo_CTRSalarioMedioAnual_c>'.$dataForm->persondeoctrsalariomedioanualc.'</ns1:Persondeo_CTRSalarioMedioAnual_c>':'').'
                            '.(isset($dataForm->currencycode)?' <ns1:CurrencyCode>'.$dataForm->currencycode.'</ns1:CurrencyCode>':'').'
                        </ns4:contact>
                    </ns4:updateContact>
                </soapenv:Body>
            </soapenv:Envelope>';

        return $request_xml;
	}
    /* Determinar Ultimo */
    public function determinarUltimo($leads){
		$arrayFinal = array();
		foreach($leads as $lead){ // recorre los registros mostrados del crm
			// = 
			if ( isset($lead["CTRActualizado_c"]) ) :
				$fecha1 =	strtotime(str_replace('/','-',substr($lead["CTRActualizado_c"], 0,10)));
				else:
				$fecha1 = false;
			endif;
			
			$fecha2 = strtotime($lead["LastUpdateDate"]);
			$fecha1 = $fecha1==false? $fecha2:$fecha1;
			$lead["fechafinal"] = $fecha1;
			$arrayFinal[]= $lead;
		}

		if(count($arrayFinal)>1){
			for($i=0;$i<count($arrayFinal)-1;$i++){ // recorre segun la cantidad de registo
				for($j=0;$j<count($arrayFinal)-1;$j++){
					if($arrayFinal[$j]["fechafinal"]<$arrayFinal[$j+1]["fechafinal"]){
						$temp = $arrayFinal[$j];
						$arrayFinal[$j] = $arrayFinal[$j+1];
						$arrayFinal[$j+1] = $temp;
					}
				}

			}
		}
		return $arrayFinal;
	}

    /* Determinar Primero */
	public function determinarPrimero($leads){
		$arrayFinal = array();
		foreach($leads as $lead){
			$fecha1 = isset($lead["CTRCreado_c"])?strtotime(str_replace('/','-',substr($lead["CTRCreado_c"], 0,10))):false;
			$fecha2 = strtotime($lead["CreationDate"]);
			$fecha1 = $fecha1==false? $fecha2:$fecha1;
			$lead["fechafinal"] = $fecha1;
			$arrayFinal[]= $lead;
		}

		if(count($arrayFinal)>1){
			for($i=0;$i<count($arrayFinal)-1;$i++){
				for($j=0;$j<count($arrayFinal)-1;$j++){
					if($arrayFinal[$j]["fechafinal"]>$arrayFinal[$j+1]["fechafinal"]){
						$temp = $arrayFinal[$j];
						$arrayFinal[$j] = $arrayFinal[$j+1];
						$arrayFinal[$j+1] = $temp;
					}
				}

			}
		}

		return $arrayFinal;
	}

    /***/
}
?>