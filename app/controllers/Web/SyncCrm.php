<?php
namespace Web;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Silex\ControllerProviderInterface;
use Web\Getprograma;
use Web\GetTerritorios;
use Web\GetCatalogos;
class SyncCrm{
	
	var $wsResource = 'https://cang-test.crm.us2.oraclecloud.com:443/foundationResources/ResourceService?WSDL';
	
	public function index(Request $request,Application $app){
		$try = $request->get('try');
		return $this->syncCRM($try,$app);
	}
	
	public function syncCRM($try,$app){
		try{
			/* carga data */
			/* programas, territorio, catalogo*/
			$getprograma = new Getprograma();
			$getTerritorios = new GetTerritorios();
			$getCatalogos = new GetCatalogos();
			$programas = $getprograma->programas();
		
			if(isset($programas)){
				
					$territorios = $getTerritorios->getTerritorios();
					
					if(isset($territorios)){
						$catalogos = $getCatalogos->getCatalogos($territorios);
						if(isset($catalogos)){
							$arrayFinal2 = array();
							//truncate
							try{
								$tp =$app['orm.em']->getConnection()->prepare('truncate table programas');
								$tp->execute();
								$app['orm.em']->flush();
								$tp =$app['orm.em']->getConnection()->prepare('truncate table charla');
								$tp->execute();
								$app['orm.em']->flush();
								$tp =$app['orm.em']->getConnection()->prepare('truncate table resource');
								$tp->execute();
								$app['orm.em']->flush();
							} catch (\Exception $e) {
								return json_encode( array('error'=>$e) );
							}
							//
							foreach($catalogos as $catalogo){
								foreach($programas as $prog){
									if($prog["CTRNumProd_c"] ==$catalogo["ProductGroupId"]){
							 			$catalogo["programa"] = $prog;
							 			$arrayFinal2[] = $catalogo;
							 		}
								}
								$catalogo["programa"] = array_filter($programas, function($elem) use ($catalogo){
									return $elem["CTRNumProd_c"] == $catalogo["ProductGroupId"];
								});
							}
							
							foreach($arrayFinal2 as $catalogo){
								$catalogo["ProductGroupId"] = isset($catalogo["ProductGroupId"])?$catalogo["ProductGroupId"]:0;
								$catalogo["ProductGroupName"] = isset($catalogo["ProductGroupName"])?$catalogo["ProductGroupName"]:'';
								$catalogo["programa"]['CTREnviodeMailalumno_c'] = isset($catalogo["programa"]['CTREnviodeMailalumno_c'])
																				?$catalogo["programa"]['CTREnviodeMailalumno_c']:'';
								$catalogo["programa"]['CTRPrioridad_c'] = isset($catalogo["programa"]['CTRPrioridad_c'])
																				?$catalogo["programa"]['CTRPrioridad_c']:'';
								$catalogo["programa"]['CTRTipoDMA_c'] = isset($catalogo["programa"]['CTRTipoDMA_c'])
																				?$catalogo["programa"]['CTRTipoDMA_c']:'';
								$catalogo["programa"]['CTRValor_c'] = isset($catalogo["programa"]['CTRValor_c'])
																				?$catalogo["programa"]['CTRValor_c']:'';
								$catalogo["programa"]['CTR_TipoProgramaFinal_c'] = isset($catalogo["programa"]['CTR_TipoProgramaFinal_c'])
																				?$catalogo["programa"]['CTR_TipoProgramaFinal_c']:'';
								$catalogo["programa"]['CTRVirtualoPresencial_c'] = isset($catalogo["programa"]['CTRVirtualoPresencial_c'])
																				?$catalogo["programa"]['CTRVirtualoPresencial_c']:'';
								$catalogo["programa"]['CTRModalidad_c'] = isset($catalogo["programa"]['CTRModalidad_c'])
																				?$catalogo["programa"]['CTRModalidad_c']:'';
								$catalogo["programa"]['CTRSededeDictado_c'] = isset($catalogo["programa"]['CTRSededeDictado_c'])
																				?$catalogo["programa"]['CTRSededeDictado_c']:'';
								$catalogo["CTRGrado_c"] = isset($catalogo["CTRGrado_c"])?$catalogo["CTRGrado_c"]:'';
								$catalogo["CTREstructura_c"] = isset($catalogo["CTREstructura_c"])?$catalogo["CTREstructura_c"]:'';
								$catalogo["CTRNombrePrograma_c"] = isset($catalogo["CTRNombrePrograma_c"])?$catalogo["CTRNombrePrograma_c"]:'';

									$pgr = new \Entity\Programas;
									$pgr->setId($catalogo["programa"]['Id']);
									$pgr->setCTRNumProdc($catalogo["ProductGroupId"]);
									$pgr->setCTRProgramaAcademicoCalculadoc($catalogo["ProductGroupName"]);
									$pgr->setCTREnviodeMailalumnoc($catalogo["programa"]['CTREnviodeMailalumno_c']);
									$pgr->setCTRPrioridadc($catalogo["programa"]['CTRPrioridad_c']);
									$pgr->setCTRTipoDMAc($catalogo["programa"]['CTRTipoDMA_c']);
									$pgr->setCTRValorc($catalogo["programa"]['CTRValor_c']);
									$pgr->setCTRTipoProgramaFinalc($catalogo["programa"]['CTR_TipoProgramaFinal_c']);
									$pgr->setCTRVirtualoPresencialc($catalogo["programa"]['CTRVirtualoPresencial_c']);
									$pgr->setCTRModalidadc($catalogo["programa"]['CTRModalidad_c']);
									$pgr->setCTRSededeDictadoc($catalogo["programa"]['CTRSededeDictado_c']);
									$pgr->setCTRGradoc($catalogo["CTRGrado_c"]);
									$pgr->setCTREstructurac($catalogo["CTREstructura_c"]);
									$pgr->setCTRNombreProgramac($catalogo["CTRNombrePrograma_c"]);
									$app['orm.em']->persist($pgr);
									$app['orm.em']->flush();
									/**/
									
									if(isset($catalogo["programa"]["CharlasCollection_c"])){
									
										if(isset($catalogo["programa"]["CharlasCollection_c"]["CTRFechadeCharla_c"]) )
											$catalogo["programa"]["CharlasCollection_c"] = array($catalogo["programa"]["CharlasCollection_c"]);
										
											foreach($catalogo["programa"]["CharlasCollection_c"] as $charla){
												$charlab  = new \Entity\Charla;
												$charlab->setId( $catalogo["programa"]['Id'] );
												$charlab->setFecha( $charla["CTRFechadeCharla_c"] );
												$app['orm.em']->persist($charlab);
												$app['orm.em']->flush();
											}
									}
								/**********************************/
								if(isset($catalogo["TerritoryResource"])){
									if(isset($catalogo["TerritoryResource"]["ResourceId"]))
										$catalogo["TerritoryResource"] = array($catalogo["TerritoryResource"]);
									
									$vendedores = array();
									foreach($catalogo["TerritoryResource"] as $resource){
										
										if(isset($vendedores[$resource["ResourceId"]])){
											$vendedor = $vendedores[$resource["ResourceId"]];
										}else{
											//stack get informa vendedores
											$vendedor = $this->getInformaVendedores($resource["ResourceId"]);
											$vendedores[$resource["ResourceId"]] = $vendedor;
										}
										
										
										if(isset($vendedor)){
											$vendedor = $vendedor["Value"];
											$resource["CTRRuleta_c"] = isset($resource["CTRRuleta_c"])?$resource["CTRRuleta_c"]:"false";
											$resource["CTROrdenRuleta_c"] = isset($resource["CTROrdenRuleta_c"])?$resource["CTROrdenRuleta_c"]:0;
											
											$rsrc = new \Entity\Resource;
											$rsrc->setId($catalogo["programa"]['Id']);
											$rsrc->setResourceId($resource["ResourceId"]);
											$rsrc->setResourceName($resource["ResourceName"]);
											$rsrc->setCTRRuletac($resource["CTRRuleta_c"]);
											$rsrc->setUsername($vendedor["Username"]);
											$rsrc->setCTROrdenRuletac($resource["CTROrdenRuleta_c"]);
											$rsrc->setEmailAddress($vendedor["EmailAddress"]);
											$app['orm.em']->persist($rsrc);
										    $app['orm.em']->flush();
										}
									}
								}
								/**********************************/
							}
						}else{
							//DB::rollback();
							$try += 1;
							if($try<3)
								$this->syncCRM($try,$app);
						}
						
					}else{
						//DB::rollback();
						$try += 1;
						if($try<3)
							$this->syncCRM($try,$app);
					}
			}else{
					// DB::rollback();
					$try += 1;
					if($try<3)
					$this->syncCRM($try,$app);
				}
			return json_encode( array('success'=>'Exito, sincronización finalizada!') );
			/* */
		}catch(\Exception $e){
			//DB::rollback();
			$try += 1;
			if($try<3)
				$this->syncCRM($try,$app);
		}
		return json_encode( array('success'=>'Exito, sincronización finalizada!') );
	}
	public function getInformaVendedores($partyid){
		$soap = new Soap();
		$client = $soap->getClientMime($this->wsResource);
		$soapaction = "http://xmlns.oracle.com/apps/cdm/foundation/resources/resourceService/applicationModule/findResource";
		$request = $this->getInformaVendedoresRequest($partyid);
		$response = $client->send($request, $soapaction, '');
		if(isset($response['result'])){
			return $response['result'];
		}
	}
	
	public function getInformaVendedoresRequest($partyid){
		$request_xml ='
			<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:typ="http://xmlns.oracle.com/apps/cdm/foundation/resources/resourceService/applicationModule/types/" xmlns:typ1="http://xmlns.oracle.com/adf/svc/types/">
				<soapenv:Header/>
				<soapenv:Body>
					<typ:findResource>
						<typ:findCriteria>
							<typ1:fetchStart>0</typ1:fetchStart>
							<typ1:fetchSize>-1</typ1:fetchSize>
							<typ1:filter>
	                        	<typ1:group>
	                            	<typ1:conjunction>And</typ1:conjunction>
	                             	<typ1:item>
	                             		<typ1:conjunction>And</typ1:conjunction>
										<typ1:upperCaseCompare>false</typ1:upperCaseCompare>
										<typ1:attribute>PartyId</typ1:attribute>
										<typ1:operator>IN</typ1:operator>
										<typ1:value>'.$partyid.'</typ1:value>
									</typ1:item>
								</typ1:group>
	                        </typ1:filter>
	                        <typ1:findAttribute>Username</typ1:findAttribute>
	                        <typ1:findAttribute>EmailAddress</typ1:findAttribute>
						</typ:findCriteria>
						<typ:findControl>
							<typ1:retrieveAllTranslations>false</typ1:retrieveAllTranslations>
						</typ:findControl>
					</typ:findResource>
				</soapenv:Body>
			</soapenv:Envelope>';

		return $request_xml;
	}
}
?>