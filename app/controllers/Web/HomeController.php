<?php
 require_once(__DIR__.'/../nusoap/nusoap.php');
 require_once(__DIR__.'/../nusoap/nusoapmime.php');

class HomeController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/
	var $proxyhost = '';
	var $proxyport = '';
	var $proxyusername = 'xxxxxx'; //user pre
	var $proxypassword = 'xxxxxx'; //pass pre
	var $wsTerritorio = 'https://cang-test.crm.us2.oraclecloud.com:443/salesTerrMgmtTerritories/TerritoryPublicService?WSDL';
	var $wsCatalago = 'https://cang-test.crm.us2.oraclecloud.com:443/ocSalesCatalogBrowse/SalesCatalogRuntimeService?WSDL';
	var $wsContacto = 'https://cang-test.crm.us2.oraclecloud.com:443/crmCommonSalesParties/ContactService?WSDL';
	var $wsPerson = 'https://cang-test.crm.us2.oraclecloud.com:443/foundationParties/PersonService?WSDL';
	var $wsResource = 'https://cang-test.crm.us2.oraclecloud.com:443/foundationResources/ResourceService?WSDL';
	var $wsLead = 'https://cang-test.crm.us2.oraclecloud.com:443/mklLeads/SalesLeadService?WSDL';
	var $wsPrograma = 'https://cang-test.crm.us2.oraclecloud.com:443/mktExtensibility/MarketingCustomObjectService?WSDL';
	var $yearFi = 2;

	public function getClient($wsdl){
		$client = new nusoap_client($wsdl, 'wsdl',$this->proxyhost, $this->proxyport, $this->proxyusername, $this->proxypassword);
		$client->setCredentials($this->proxyusername, $this->proxypassword, 'basic');
		$client->soap_defencoding = 'utf-8';
    	$client->decode_utf8 = false;
    	$client->useHTTPPersistentConnection(); // Uses http 1.1 instead of 1.0
    	$client->use_curl = TRUE;
    	return $client;
    }

    public function getClientMime($wsdl){
		$client = new nusoap_client_mime($wsdl, 'wsdl',$this->proxyhost, $this->proxyport, $this->proxyusername, $this->proxypassword);
		$client->setCredentials($this->proxyusername, $this->proxypassword, 'basic');
		$client->soap_defencoding = 'utf-8';
    	$client->decode_utf8 = false;
    	$client->useHTTPPersistentConnection(); // Uses http 1.1 instead of 1.0
    	$client->use_curl = TRUE;
    	return $client;
    }

	public function getProgramas(){
		$client = $this->getClient($this->wsPrograma);
		$today = date('Y-m-d',time());
		$soapaction = "http://xmlns.oracle.com/apps/marketing/custExtn/extnService/findEntity";
		$request = $this->getProgramasRequest($today);
		$response = $client->send($request, $soapaction, '');
		if(isset($response['result'])){
			if(isset($response['result']['Id'])){
				return array($response['result']);
			}else{
				return $response['result'];
			}
		}
	}

	public function getTerritorios(){
		$client = $this->getClient($this->wsTerritorio);
		$today = date('Y-m-d',time());
		$soapaction = "http://xmlns.oracle.com/oracle/apps/sales/territoryMgmt/territories/territoryService/findTerritories";
		$request = $this->getTerritorioRequest($today);
		$response = $client->send($request, $soapaction, '');
		if(isset($response['result'])){
			if(isset($response['result']['Name'])){
				return array($response['result']);
			}else{
				return $response['result'];
			}
		}
	}

	public function getCatalogos($territories){
		$client = $this->getClient($this->wsCatalago);
		$soapaction = "http://xmlns.oracle.com/apps/orderCapture/salesCatalog/browse/salesCatalogService/getCatalogCategories";
		$arrayFinal = array();
		foreach($territories as $territorio){
			if(isset($territorio["TerritoryRule"]["TerritoryRuleBoundary1"]["TerritoryRuleBndryValue1"]["TerrDimIntgId"])){
				$TerrDimIntgId = $territorio["TerritoryRule"]["TerritoryRuleBoundary1"]["TerritoryRuleBndryValue1"]["TerrDimIntgId"];
				$request = $this->getCatalogoRequest($TerrDimIntgId);
				$response = $client->send($request, $soapaction, '');
				if(isset($response["result"])){
					$catalogos = $response["result"];
					if(isset($catalogos["ProdGrpDetailsId"])){
						if($catalogos["ProductGroupDescription"]=="hoja"){
							$catalogos["TerritoryResource"] = $territorio["TerritoryResource"];
							$arrayFinal[] = $catalogos;
						}
					}else{
						foreach($catalogos as $catalogo){
				    		if($catalogo["ProductGroupDescription"]=="hoja"){
								$catalogo["TerritoryResource"] = $territorio["TerritoryResource"];
								$arrayFinal[] = $catalogo;
							}
				    	}
					}
				}
			}
		}
		return $arrayFinal;
	}

	public function getInformaVendedores($ids){
		$client = $this->getClientMime($this->wsResource);
		$soapaction = "http://xmlns.oracle.com/apps/cdm/foundation/resources/resourceService/applicationModule/findResource";
		$request = $this->getInformaVendedoresRequest($ids);
		$response = $client->send($request, $soapaction, '');
		if(isset($response['result'])){
			return $response['result'];
		}
	}

	public function getCatalogoRequest($TerrDimIntgId){
		$request_xml ='
			<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
				<soap:Body>
					<ns1:getCatalogCategories xmlns:ns1="http://xmlns.oracle.com/apps/orderCapture/salesCatalog/browse/salesCatalogService/types/">
					<ns1:usageCode>BASE</ns1:usageCode>
					<ns1:usageModeCode>MOO</ns1:usageModeCode>
					<ns1:runEligibilityFlag>false</ns1:runEligibilityFlag>
					<ns1:ancestorProductGroupId>'.$TerrDimIntgId.'</ns1:ancestorProductGroupId>
					<ns1:minimumDepth>1</ns1:minimumDepth>
					<ns1:maximumDepth>9999</ns1:maximumDepth>
					<ns1:minimumPathId>0</ns1:minimumPathId>
					<ns1:maximumSizeToReturn>50</ns1:maximumSizeToReturn>
					</ns1:getCatalogCategories>
				</soap:Body>
			</soap:Envelope>';
		return $request_xml;
	}

	public function getTerritorioRequest($today){
		$request_xml ='
			<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
     			xmlns:typ="http://xmlns.oracle.com/oracle/apps/sales/territoryMgmt/territories/territoryService/types/"
     			xmlns:typ1="http://xmlns.oracle.com/adf/svc/types/">
 	        	<soapenv:Header/>
 	            <soapenv:Body>
 	                <typ:findTerritories>
 	                	<typ:findCriteria>
 	                		<typ1:fetchStart>0</typ1:fetchStart>
 	                        <typ1:fetchSize>-1</typ1:fetchSize>
 	                        <typ1:filter>
 	                            <typ1:group>
 	                             	<typ1:conjunction>And</typ1:conjunction>
 	                             	<typ1:item>
 	                             		<typ1:conjunction>And</typ1:conjunction>
 										<typ1:upperCaseCompare>false</typ1:upperCaseCompare>
 										<typ1:attribute>ParentTerritoryId</typ1:attribute>
 										<typ1:operator>=</typ1:operator>
 										<typ1:value>300000007639214</typ1:value>
 									</typ1:item>
 									<typ1:item>
 	                             		<typ1:conjunction>And</typ1:conjunction>
 										<typ1:upperCaseCompare>false</typ1:upperCaseCompare>
 										<typ1:attribute>TerritoryLevel</typ1:attribute>
 										<typ1:operator>=</typ1:operator>
 										<typ1:value>2</typ1:value>
 									</typ1:item>
 									<typ1:item>
 										<typ1:conjunction>And</typ1:conjunction>
 										<typ1:upperCaseCompare>false</typ1:upperCaseCompare>
 										<typ1:attribute>StatusCode</typ1:attribute>
 										<typ1:operator>=</typ1:operator>
 										<typ1:value>FINALIZED</typ1:value>
 									</typ1:item>
 									<typ1:item>
 										<typ1:conjunction>And</typ1:conjunction>
 										<typ1:upperCaseCompare>false</typ1:upperCaseCompare>
 										<typ1:attribute>LatestVersionFlag</typ1:attribute>
 										<typ1:operator>=</typ1:operator>
 										<typ1:value>true</typ1:value>
 									</typ1:item>
                    				<typ1:item>
 										<typ1:conjunction>And</typ1:conjunction>
 										<typ1:upperCaseCompare>false</typ1:upperCaseCompare>
 										<typ1:attribute>EffectiveEndDate</typ1:attribute>
 										<typ1:operator>ONORAFTER</typ1:operator>
 										<typ1:value>'.$today.'</typ1:value>
 									</typ1:item>
 								</typ1:group>
 							</typ1:filter>
 							<typ1:findAttribute>Name</typ1:findAttribute>
 							<typ1:findAttribute>OwnerOrgId</typ1:findAttribute>
 							<typ1:findAttribute>TerritoryResource</typ1:findAttribute>
 	                        <typ1:childFindCriteria>
 	                            <typ1:findAttribute>ResourceId</typ1:findAttribute>
 	                            <typ1:findAttribute>ResourceName</typ1:findAttribute>
 	                            <typ1:findAttribute>CTRRuleta_c</typ1:findAttribute>
 	                            <typ1:findAttribute>CTROrdenRuleta_c</typ1:findAttribute>
 	                            <typ1:childAttrName>TerritoryResource</typ1:childAttrName>
 	                        </typ1:childFindCriteria>
 	                        <typ1:findAttribute>TerritoryRule</typ1:findAttribute>
 						    <typ1:childFindCriteria>
 						        <typ1:findAttribute>TerrRuleId</typ1:findAttribute>
 	                            <typ1:findAttribute>TerritoryRuleBoundary1</typ1:findAttribute>
 	                            <typ1:childFindCriteria>
 	                                <typ1:findAttribute>TerritoryRuleBndryValue1</typ1:findAttribute>
 	                                <typ1:childFindCriteria>
 	                                	<typ1:findAttribute>TerrDimIntgId</typ1:findAttribute>
 	                                	<typ1:childAttrName>TerritoryRuleBndryValue1</typ1:childAttrName>
 	                                </typ1:childFindCriteria>
 	                                <typ1:childAttrName>TerritoryRuleBoundary1</typ1:childAttrName>
 	                            </typ1:childFindCriteria>
 	                            <typ1:childAttrName>TerritoryRule</typ1:childAttrName>
 	                        </typ1:childFindCriteria>
 	                    </typ:findCriteria>
 	                    <typ:findControl>
 	                        <typ1:retrieveAllTranslations>false</typ1:retrieveAllTranslations>
 	                    </typ:findControl>
 	                </typ:findTerritories>
 	            </soapenv:Body>
 	        </soapenv:Envelope>';
 	        return $request_xml;
	}

	public function getInformaVendedoresRequest($ids){
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
										'.$ids.'
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

	public function getProgramasRequest($today){
		$request_xml ='
			<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:typ="http://xmlns.oracle.com/apps/marketing/custExtn/extnService/types/" xmlns:typ1="http://xmlns.oracle.com/adf/svc/types/">
		    	<soapenv:Header/>
		    	<soapenv:Body>
			        <typ:findEntity>
			        	<typ:findCriteria>
			            	<typ1:fetchStart>0</typ1:fetchStart>
			                <typ1:fetchSize>-1</typ1:fetchSize>
			                <typ1:filter>
			                    <typ1:group>
			                        <typ1:conjunction>And</typ1:conjunction>
			                        <typ1:item>
										<typ1:conjunction>And</typ1:conjunction>
										<typ1:upperCaseCompare>false</typ1:upperCaseCompare>
										<typ1:attribute>CTREstado_c</typ1:attribute>
										<typ1:operator>=</typ1:operator>
										<typ1:value>Activo</typ1:value>
									</typ1:item>
								</typ1:group>
			                </typ1:filter>
			                <typ1:sortOrder>
			                	<typ1:sortAttribute>
			                        <typ1:name>Id</typ1:name>
			                        <typ1:ascending>true</typ1:ascending>
			                    </typ1:sortAttribute>
			                </typ1:sortOrder>
			                <typ1:findAttribute>Id</typ1:findAttribute>
			                <typ1:findAttribute>CTRNumProd_c</typ1:findAttribute>
			                <typ1:findAttribute>CTRProgramaAcademicoCalculado_c</typ1:findAttribute>
			                <typ1:findAttribute>CTREnviodeMailalumno_c</typ1:findAttribute>
			                <typ1:findAttribute>CTRPrioridad_c</typ1:findAttribute>
			                <typ1:findAttribute>CTRTipoDMA_c</typ1:findAttribute>
			                <typ1:findAttribute>CTRValor_c</typ1:findAttribute>
			                <typ1:findAttribute>CTR_TipoProgramaFinal_c</typ1:findAttribute>
			                <typ1:findAttribute>CTRVirtualoPresencial_c</typ1:findAttribute>
			                <typ1:findAttribute>CTRModalidad_c</typ1:findAttribute>
			                <typ1:findAttribute>CTRSededeDictado_c</typ1:findAttribute>
			                <typ1:findAttribute>CharlasCollection_c</typ1:findAttribute>
			                <typ1:childFindCriteria>
			                	<typ1:filter>
			                    	<typ1:group>
			                        	<typ1:conjunction>And</typ1:conjunction>
			                            <typ1:item>
					                    	<typ1:conjunction>And</typ1:conjunction>
											<typ1:upperCaseCompare>false</typ1:upperCaseCompare>
											<typ1:attribute>CTRFechadeCharla_c</typ1:attribute>
											<typ1:operator>ONORAFTER</typ1:operator>
											<typ1:value>'.$today.'</typ1:value>
										</typ1:item>
			                        </typ1:group>
			                    </typ1:filter>
			                    <typ1:findAttribute>CTRFechadeCharla_c</typ1:findAttribute>
			                    <typ1:childAttrName>CharlasCollection_c</typ1:childAttrName>
			                </typ1:childFindCriteria>
			            </typ:findCriteria>
			            <typ:findControl>
			            	<typ1:retrieveAllTranslations>false</typ1:retrieveAllTranslations>
			            </typ:findControl>
			            <typ:objectName>ProgramasAcademicos_c</typ:objectName>
			        </typ:findEntity>
		        </soapenv:Body>
		        </soapenv:Envelope>';
		return $request_xml;
	}

	public function showWelcome(){
		$territorio = $this->getTerritorios();
		$catalogos = $this->getCatalogos($territorio);
		var_dump($catalogos);
	}

	public function syncCRM($try){
		try{
			$carga = DB::table('cargadata')
				->select(
						'cargadata.id'
						,'cargadata.id_usuario'
						,DB::raw('(SELECT user.us_email FROM usuario user where user.us_codigo = cargadata.id_usuario ) as email')
					)
				->where('cargadata.estado','=',DB::raw("'0'"))
				->orderBy('cargadata.fecha_reg','ASC')
				->first();
			if(isset($carga)){
				DB::beginTransaction();
				$data = array('fecha_inicio'=>time());
				DB::table('cargadata')->where('id','=',$carga->id)->update($data);
				
				//cargar programas
				$programas = $this->getProgramas();
				if(isset($programas)){
					$territorios = $this->getTerritorios();
					if(isset($territorios)){
						$catalogos = $this->getCatalogos($territorios);
						if(isset($catalogos)){
							$arrayFinal2 = array();
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
							DB::table('programas_v2')->truncate();
							DB::table('charla_v2')->truncate();
							DB::table('resource_v2')->truncate();
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
								$data = array(
										'Id'=>$catalogo["programa"]['Id']
										,'CTRNumProd_c'=>$catalogo["ProductGroupId"]
										,'CTRProgramaAcademicoCalculado_c'=>$catalogo["ProductGroupName"]
										,'CTREnviodeMailalumno_c'=>$catalogo["programa"]['CTREnviodeMailalumno_c']
										,'CTRPrioridad_c'=>$catalogo["programa"]['CTRPrioridad_c']
										,'CTRTipoDMA_c'=>$catalogo["programa"]['CTRTipoDMA_c']
										,'CTRValor_c'=>$catalogo["programa"]['CTRValor_c']
										,'CTR_TipoProgramaFinal_c'=>$catalogo["programa"]['CTR_TipoProgramaFinal_c']
										,'CTRVirtualoPresencial_c'=>$catalogo["programa"]['CTRVirtualoPresencial_c']
										,'CTRModalidad_c'=>$catalogo["programa"]['CTRModalidad_c']
										,'CTRSededeDictado_c'=>$catalogo["programa"]['CTRSededeDictado_c']
										,'CTRGrado_c'=>$catalogo["CTRGrado_c"]
										,'CTREstructura_c'=>$catalogo["CTREstructura_c"]
										,'CTRNombrePrograma_c'=>$catalogo["CTRNombrePrograma_c"]
									);
								DB::table('programas_v2')->insert($data);
								if(isset($catalogo["programa"]["CharlasCollection_c"])){
									if(isset($catalogo["programa"]["CharlasCollection_c"]["CTRFechadeCharla_c"]))
										$catalogo["programa"]["CharlasCollection_c"] = array($catalogo["programa"]["CharlasCollection_c"]);
									foreach($catalogo["programa"]["CharlasCollection_c"] as $charla){
										$data = array(
												'Id'=>$catalogo["programa"]['Id']
												,'fecha'=>$charla["CTRFechadeCharla_c"]
											);
										DB::table('charla_v2')->insert($data);
									}
								}
								if(isset($catalogo["TerritoryResource"])){
									if(isset($catalogo["TerritoryResource"]["ResourceId"]))
										$catalogo["TerritoryResource"] = array($catalogo["TerritoryResource"]);
									$vendedores = array();
									foreach($catalogo["TerritoryResource"] as $resource){
										if(isset($vendedores[$resource["ResourceId"]])){
											$vendedor = $vendedores[$resource["ResourceId"]];
										}else{
											$vendedor = $this->getInformaVendedores('<typ1:value>'.$resource["ResourceId"].'</typ1:value>');
											$vendedores[$resource["ResourceId"]] = $vendedor;
										}
										if(isset($vendedor)){
											$vendedor = $vendedor["Value"];
											$resource["CTRRuleta_c"] = isset($resource["CTRRuleta_c"])?$resource["CTRRuleta_c"]:"false";
											$resource["CTROrdenRuleta_c"] = isset($resource["CTROrdenRuleta_c"])?$resource["CTROrdenRuleta_c"]:0;
											$data = array(
												'Id'=>$catalogo["programa"]['Id']
												,'ResourceId'=>$resource["ResourceId"]
												,'ResourceName'=>$resource["ResourceName"]
												,'CTRRuleta_c'=>$resource["CTRRuleta_c"]
												,'Username'=>$vendedor["Username"]
												,'CTROrdenRuleta_c'=>$resource["CTROrdenRuleta_c"]
												,'EmailAddress'=>$vendedor["EmailAddress"]

											);
											DB::table('resource_v2')->insert($data);
										}
									}
								}
							}

							$data = array('fecha_final'=>time(), 'estado'=>'1');
							DB::table('cargadata')->where('id','=',$carga->id)->update($data);
							DB::commit();
						}else{
							DB::rollback();
							$try += 1;
							if($try<3)
								$this->syncCRM($try);
						}

					}else{
						DB::rollback();
						$try += 1;
						if($try<3)
							$this->syncCRM($try);
					}
				}else{
					DB::rollback();
					$try += 1;
					if($try<3)
						$this->syncCRM($try);
				}

				return $carga->email;
			}

		}catch(\Exception $e){
			DB::rollback();
			$try += 1;
			if($try<3)
				$this->syncCRM($try);

		}
	}

	public function emailSync($email_usuario){

		$mensaje = 'Se realizó exitosamente la sincronización por favor dirigirse al'
					.'siguiente enlace para confirmar su sincronización: '.URL::to('/')
					.'/carga';
		$subject = "Confirmación de Sincronización : CRM FUSION";
		$dataFi = array('mensaje'=>$mensaje);
		Mail::queue('emails.informacion',$dataFi, function($message) use ($email_usuario, $subject){
				$message->to($email_usuario)->subject($subject);
		});

		$email_usuario = 'centrum.informes@pucp.pe';
		Mail::queue('emails.informacion',$dataFi, function($message) use ($email_usuario, $subject){
				$message->to($email_usuario)->subject($subject);
		});
	}

	public function syncUserCRM(){
		$usuarios = DB::table('usuario_crm')
				->where('tipo','=','1')
				->where('crm','=','0')
				->where('try','!=','4')
				->orderBy('id','ASC')
				->get();
		if(count($usuarios)>0){
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

			foreach($usuarios as $usuario){
				$campos = DB::table('campo_crm')->where('user_id','=',$usuario->id)->get();
				if(count($campos)==0){
					$mensaje = 'No existen campos del usuario';
					DB::table('usuario_crm')
						->where('user_id','=',$usuario->id)
						->update(array('crm'=>'1','error'=>$mensaje));
					continue;
				}
				$dataForm = new stdClass();
				foreach($campos as $campo){
					$dataForm->{$campo->campo} = $campo->valor;
				}

				$user = $this->checkContact($dataForm,1, $usuario->id);
				if(!isset($user)){
					$mensaje = 'Error en la verificación del usuario';
					DB::table('usuario_crm')
						->where('user_id','=',$usuario->id)
						->update(array('try'=>($usuario->try+1),'error'=>$mensaje));
					continue;
				}

				if($user){
					$this->updateContact($user, $dataForm, $usuario);
				}else{
					$this->createContact($dataForm, $usuario);
				}
			}

			DB::table('sailor_vendedor')->delete();
		}
		return 'Se realizó exitosamente la carga de usuarios';
	}

	public function createContact($dataForm, $usuario){
		if($dataForm->atendido=="NSNR"){
			$vendedores = DB::table('resource')
						->select(
								'ResourceId'
								,'ResourceName'
								,'EmailAddress'
							)
						->where('Id','=',$dataForm->programa)
						->where('CTRRuleta_c','=','true')
						->orderBy('CTROrdenRuleta_c', 'ASC')->get();
			if(count($vendedores)==0){
				$error = 'El programa no tiene vendedores para la ruleta';
				DB::table('usuario_crm')
					->where('id','=',$usuario->id)
					->update(array('try'=>($usuario->try+1), 'error'=>$error));
				return $error;
			}

			$nextVendedor = DB::table('sailor')
							->where('producto','=',$dataForm->programa)
							->where('posicion','!=','0')->first();
			if(isset($nextVendedor)){
				$nextVendedor = $nextVendedor->posicion;
			}else{
				$nextVendedor = 0;
			}

			$count = 1;
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


  		}

  		

  		$dataForm->atendido = $vend->ResourceId;
  		$dataForm->OwnerPartyId = $dataForm->atendido;
		$userInserted = $this->insertContact($dataForm, 1);
		if(!isset($userInserted)){
			$error = 'Error al crear un nuevo contacto';
			DB::table('usuario_crm')
				->where('id','=',$usuario->id)
				->update(array('try'=>($usuario->try+1), 'error'=>$error));
			return $error;
		}

		$person = $this->insertMergePerson($userInserted, $dataForm, 1);
		if(!isset($person)){
			$error = 'Error al actualizar los correos del nuevo contacto';
			DB::table('usuario_crm')
				->where('id','=',$usuario->id)
				>update(array('try'=>($usuario->try+1), 'error'=>$error));
			return $error;
		}

		$person = $this->updatePersonEmail($userInserted, 1);
		if(!isset($person)){
			$error = 'Error al actualizar el correo principal del nuevo contacto';
			DB::table('usuario_crm')
				->where('id','=',$usuario->id)
				->update(array('try'=>($usuario->try+1), 'error'=>$error));
			return $error;
		}

		$lead = $this->createLead($userInserted,$dataForm, 1);
		if(!isset($lead)){
			$error = 'Error al crear el lead del nuevo contacto';
			DB::table('usuario_crm')
				->where('id','=',$usuario->id)
				->update(array('try'=>($usuario->try+1), 'error'=>$error));
			return $error;
		}

		DB::table('sailor_log')->insert(array(
									'producto'=>$dataForm->programa
									,'vendedor'=>$vend->ResourceId
									,'fecha_reg'=>time()
								));

		if(isset($vendedores)){
			DB::table('sailor')->where('producto','=',$dataForm->programa)->delete();
			$count_2 = 1;
			foreach($vendedores as $vendedor){
				$pos = 0;
				if($count == $count_2){
					$pos = $count+1;
					if($pos > count($vendedores)){
						$pos = 1;
					}
				}
				DB::table('sailor')->insert(array(
										'id'=>$count_2
										,'posicion'=>$pos
										,'estado'=>1
										,'producto'=>$dataForm->programa
										,'vendedor'=>$vendedor->ResourceId
									));
				$count_2++;
			}
		}
		DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('crm'=>'1'));

		DB::table('campo_crm')->insert(array('user_id'=>$usuario->id, 'campo'=>'email_final', 'valor'=>$vend->EmailAddress));
	}

	public function createLead($userInserted, $dataForm, $try){
		$client = $this->getClient($this->wsLead);
		$soapaction = "http://xmlns.oracle.com/apps/marketing/leadMgmt/leads/leadService/createSalesLead";
		$request = $this->createLeadRequest($userInserted, $dataForm);
		$response = $client->send($request, $soapaction, '');
		if(isset($response['result'])){
			return $response['result'];
		}

		$try += 1;
		if($try<3)
			return $this->createLead($userInserted,$dataForm, $try);
	}

	public function createLeadRequest($userInserted, $dataForm){
		$request_otroMedio = '';
		if(isset($dataForm->PersonDEO_CTRProcedencia_c)){
			if(!isset($dataForm->OtroMedio))
				$dataForm->OtroMedio = '';
			if($dataForm->PersonDEO_CTRProcedencia_c==8)
				$request_otroMedio = '<lead:CTROtroMedio_c>'.$dataForm->OtroMedio.'</lead:CTROtroMedio_c>';
		}
		$request_charla = '';
		if(isset($dataForm->CTRFechaCharla_c)){
			$request_charla = '<lead:CTRFechaCharla_c>'.$dataForm->CTRFechaCharla_c.'</lead:CTRFechaCharla_c>';
		}

    	$request_xml = '
    		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    			xmlns:typ="http://xmlns.oracle.com/apps/marketing/leadMgmt/leads/leadService/types/"
    			xmlns:lead="http://xmlns.oracle.com/oracle/apps/marketing/leadMgmt/leads/leadService/"
    			xmlns:lead1="http://xmlns.oracle.com/apps/marketing/leadMgmt/leads/leadService/"
    			xmlns:not="http://xmlns.oracle.com/apps/crmCommon/notes/noteService"
    			xmlns:not1="http://xmlns.oracle.com/apps/crmCommon/notes/flex/noteDff/">
				<soapenv:Header/>
				   <soapenv:Body>
				      <typ:createSalesLead>
				        <typ:salesLead>
				            <lead:Name>'.$dataForm->FirstName.' '.$dataForm->LastName.' - '.$dataForm->nombrePro.'</lead:Name>
				            <lead:CustomerId>'.$userInserted['PartyId'].'</lead:CustomerId>
				            <lead:OwnerId>'.$dataForm->atendido.'</lead:OwnerId>
				            '.$request_otroMedio.'
				            '.$request_charla.'
				            <lead:CTR_OrigenDelRegistro_c>'.$dataForm->CTR_OrigenDelRegistro_c.'</lead:CTR_OrigenDelRegistro_c>
				            <lead:CTRProductoAsociado_Id_c>'.$dataForm->productId.'</lead:CTRProductoAsociado_Id_c>
				            <lead:PrimaryProductGroupId>'.$dataForm->productId.'</lead:PrimaryProductGroupId>
				        </typ:salesLead>
				      </typ:createSalesLead>
				   </soapenv:Body>
			</soapenv:Envelope>';
		return $request_xml;
	}

	public function updatePersonEmail($userInserted, $try){

		$emails = $this->getPerson($userInserted, 1);

		if(isset($emails)){
			$client = $this->getClient($this->wsPerson);
			$soapaction = "http://xmlns.oracle.com/apps/cdm/foundation/parties/personService/applicationModule/updatePerson";
			$request = $this->updatePersonEmailRequest($userInserted, $emails);
			$response = $client->send($request, $soapaction, '');
			if(isset($response['result'])){
				return $response['result'];
			}

			$try += 1;
			if($try<3)
				return $this->updatePersonEmail($userInserted, $try);
		}
	}

	public function updatePersonEmailRequest($userInserted, $emails){
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
					<typ:updatePerson>
						<typ:personParty>
							<per:PartyId>'.$userInserted['PartyId'].'</per:PartyId>
						    <per:Email>
						      	<con:ContactPointId>'.$emails["PrimaryEmailContactPTId"].'</con:ContactPointId>
						      	<con:ContactPointType>EMAIL</con:ContactPointType>
						      	<con:ContactPointPurpose>PERSONAL</con:ContactPointPurpose>
						    </per:Email>
						</typ:personParty>
					</typ:updatePerson>
				</soapenv:Body>
			</soapenv:Envelope>';

		return $request_xml;
	}

	public function getPerson($userInserted, $try){
		$client = $this->getClient($this->wsPerson);
		$soapaction = "http://xmlns.oracle.com/apps/cdm/foundation/parties/personService/applicationModule/findPerson";
		$request = $this->getPersonRequest($userInserted);
		$response = $client->send($request, $soapaction, '');
		if(isset($response['result'])){
			return $response['result']['Value'];
		}

		$try += 1;
		if($try<3)
			return $this->getPerson($userInserted, $try);
	}

	public function getPersonRequest($userInserted){
		$request_xml = '
			<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:typ="http://xmlns.oracle.com/apps/cdm/foundation/parties/personService/applicationModule/types/" xmlns:typ1="http://xmlns.oracle.com/adf/svc/types/">
				<soapenv:Header/>
				<soapenv:Body>
					<typ:findPerson>
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
										<typ1:operator>=</typ1:operator>
										<typ1:value>'.$userInserted["PartyId"].'</typ1:value>
									</typ1:item>
								</typ1:group>
							 </typ1:filter>
							 <typ1:findAttribute>PrimaryEmailContactPTId</typ1:findAttribute>
							 <typ1:findAttribute>Email</typ1:findAttribute>
						</typ:findCriteria>
					    <typ:findControl>
					    	<typ1:retrieveAllTranslations>false</typ1:retrieveAllTranslations>
					    </typ:findControl>
					</typ:findPerson>
				</soapenv:Body>
				</soapenv:Envelope>';

		return $request_xml;
	}

	public function insertMergePerson($userInserted, $dataForm, $try){
		if(!isset($dataForm->PersonDEO_CTRCorreoPUCP_c) && !isset($dataForm->EmailAddress2)){
			return true;
		}
		$client = $this->getClient($this->wsPerson);
		$soapaction = "http://xmlns.oracle.com/apps/cdm/foundation/parties/personService/applicationModule/mergePerson";
		$request = $this->insertMergePersonRequest($userInserted, $dataForm);
		$response = $client->send($request, $soapaction, '');
		if(isset($response['result'])){
			return true;
		}

		$try += 1;
		if($try<3)
			return $this->insertMergePerson($userInserted,$dataForm, $try);
	}

	public function insertMergePersonRequest($userInserted, $dataForm){
		$email_pucp = '';
    	if(isset($dataForm->PersonDEO_CTRCorreoPUCP_c)){
    		$email_pucp .= '<per:Email>
					               <con:OwnerTableName>HZ_PARTIES</con:OwnerTableName>
					               <con:OwnerTableId>'.$userInserted['PartyId'].'</con:OwnerTableId>
					               <con:PrimaryFlag>false</con:PrimaryFlag>
					               <con:ContactPointPurpose>PUCP</con:ContactPointPurpose>
					               <con:EmailAddress>'.$dataForm->PersonDEO_CTRCorreoPUCP_c.'</con:EmailAddress>
					               <con:PrimaryByPurpose>N</con:PrimaryByPurpose>
					               <con:CreatedByModule>HZ_WS</con:CreatedByModule>
							    </per:Email>';
		}

		if(isset($dataForm->EmailAddress2)){
    		$email_pucp .= '<per:Email>
					               <con:OwnerTableName>HZ_PARTIES</con:OwnerTableName>
					               <con:OwnerTableId>'.$userInserted['PartyId'].'</con:OwnerTableId>
					               <con:PrimaryFlag>false</con:PrimaryFlag>
					               <con:ContactPointPurpose>BUSINESS</con:ContactPointPurpose>
					               <con:EmailAddress>'.$dataForm->EmailAddress2.'</con:EmailAddress>
					               <con:PrimaryByPurpose>N</con:PrimaryByPurpose>
					               <con:CreatedByModule>HZ_WS</con:CreatedByModule>
							    </per:Email>';
		}

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
					    	<per:PartyId>'.$userInserted['PartyId'].'</per:PartyId>
					        '.$email_pucp.'
						</typ:personParty>
					</typ:mergePerson>
				</soapenv:Body>
			</soapenv:Envelope>';
		return $request_xml;
	}

	public function insertContact($dataForm, $try){
		$client = $this->getClient($this->wsContacto);
		$soapaction = "http://xmlns.oracle.com/apps/crmCommon/salesParties/contactService/createContact";
		$request = $this->insertContactRequest($dataForm);
		$response = $client->send($request, $soapaction, '');
		if(isset($response["result"])){
			return $response["result"]["Value"];
		}

		$try += 1;
		if($try<3)
			return $this->insertContact($dataForm, $try);
	}

	public function insertContactRequest($user){
		$request_xml ='
			<soapenv:Envelope
	    		xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
	    		xmlns:ns1="http://xmlns.oracle.com/apps/crmCommon/salesParties/contactService/"
  				xmlns:ns2="http://xmlns.oracle.com/apps/crmCommon/salesParties/commonService/"
  				xmlns:ns3="http://xmlns.oracle.com/apps/cdm/foundation/parties/personService/"
  				xmlns:ns4="http://xmlns.oracle.com/apps/crmCommon/salesParties/contactService/types/"
  				xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  				<soapenv:Body>
  					<ns4:createContact>
  						<ns4:contact>
  							'.(isset($user->SalutoryIntroduction)?'<ns1:SalutoryIntroduction>'.$user->SalutoryIntroduction.'</ns1:SalutoryIntroduction>':'').'
  							'.(isset($user->FirstName)?'<ns1:FirstName>'.$user->FirstName.'</ns1:FirstName>':'').'
					        '.(isset($user->LastName)?'<ns1:LastName>'.$user->LastName.'</ns1:LastName>':'').'
					        '.(isset($user->PersonDEO_CTRApellidoMaterno_c)?'<ns1:PersonDEO_CTRApellidoMaterno_c>'.$user->PersonDEO_CTRApellidoMaterno_c.'</ns1:PersonDEO_CTRApellidoMaterno_c>':'').'
					        <ns1:PersonDEO_CTRTipodedocumento_c>'.$user->PersonDEO_CTRTipodedocumento_c.'</ns1:PersonDEO_CTRTipodedocumento_c>
						    <ns1:PersonDEO_CTRNrodedocumento_c>'.$user->PersonDEO_CTRNrodedocumento_c.'</ns1:PersonDEO_CTRNrodedocumento_c>
						    '.(isset($user->HomePhoneNumber)?'<ns1:HomePhoneNumber>'.$user->HomePhoneNumber.'</ns1:HomePhoneNumber>':'').'
						    '.(isset($user->MobileNumber)?'<ns1:MobileNumber>'.$user->MobileNumber.'</ns1:MobileNumber>':'');
		if(isset($user->AddressElementAttribute2) || isset($user->AddressElementAttribute3)
			|| isset($user->AddressLine1) || isset($user->Country) || isset($user->City)){
			$request_xml .= '
							<ns1:PrimaryAddress>
						        '.(isset($user->AddressElementAttribute2)?'<ns2:AddressElementAttribute2>'.$user->AddressElementAttribute2.'</ns2:AddressElementAttribute2>':'').'
						        '.(isset($user->AddressElementAttribute3)?'<ns2:AddressElementAttribute3>'.$user->AddressElementAttribute3.'</ns2:AddressElementAttribute3>':'').'
						        '.(isset($user->AddressLine1)?'<ns2:AddressLine1>'.$user->AddressLine1.'</ns2:AddressLine1>':'').'
						        '.(isset($user->Country)?'<ns2:Country>'.$user->Country.'</ns2:Country>':'').'
						        '.(isset($user->City)?'<ns2:City>'.$user->City.'</ns2:City>':'').'
						    </ns1:PrimaryAddress>';
		}

		$request_xml .= '
					    	<ns1:DateOfBirth>'.$user->DateOfBirth.'</ns1:DateOfBirth>
					        '.(isset($user->PersonDEO_CTRPaisdenacimiento_c)?'<ns1:PersonDEO_CTRPaisdenacimiento_c>'.$user->PersonDEO_CTRPaisdenacimiento_c.'</ns1:PersonDEO_CTRPaisdenacimiento_c>':'').'
						    '.(isset($user->PersonDEO_CTRCiudaddeNacimiento_c)?'<ns1:PersonDEO_CTRCiudaddeNacimiento_c>'.$user->PersonDEO_CTRCiudaddeNacimiento_c.'</ns1:PersonDEO_CTRCiudaddeNacimiento_c>':'').'
						    '.(isset($user->PersonDEO_CTRNacionalidad_c)?'<ns1:PersonDEO_CTRNacionalidad_c>'.$user->PersonDEO_CTRNacionalidad_c.'</ns1:PersonDEO_CTRNacionalidad_c>':'').'
						    '.(isset($user->EmailAddress)?'<ns1:EmailAddress>'.$user->EmailAddress.'</ns1:EmailAddress>':'').'
						    '.(isset($user->PersonDEO_CTRCorreoPUCP_c)?'<ns1:PersonDEO_CTRCorreoPUCP_c>'.$user->PersonDEO_CTRCorreoPUCP_c.'</ns1:PersonDEO_CTRCorreoPUCP_c>':'').'
						    '.(isset($user->PersonDEO_CTRCuentaSkype_c)?'<ns1:PersonDEO_CTRCuentaSkype_c>'.$user->PersonDEO_CTRCuentaSkype_c.'</ns1:PersonDEO_CTRCuentaSkype_c>':'');

							
		if(isset($user->CTRGradoacademico_c) || isset($user->CTRInstitucionAcademica_c)
			|| isset($user->CTROtrasUniversidadesInst_c) || isset($user->CTREspecialidad_c)
			|| isset($user->CTRAnoMesquefinalizoEstSup_c) || isset($user->CTRNivelacademico_c)){
			 $request_xml .= '
			 				<ns1:PersonDEO_InformacionAcademicaCollection_c>
			 					'.(isset($user->CTRGradoacademico_c)?'<ns3:CTRGradoacademico_c>'.$user->CTRGradoacademico_c.'</ns3:CTRGradoacademico_c>':'').'
			 					'.(isset($user->CTRInstitucionAcademica_c)?'<ns3:CTRInstitucionAcademica_c>'.$user->CTRInstitucionAcademica_c.'</ns3:CTRInstitucionAcademica_c>':'').'
			 					'.(isset($user->CTROtrasUniversidadesInst_c)?'<ns3:CTROtrasUniversidadesInst_c>'.$user->CTROtrasUniversidadesInst_c.'</ns3:CTROtrasUniversidadesInst_c>':'').'
			 					'.(isset($user->CTREspecialidad_c)?'<ns3:CTREspecialidad_c>'.$user->CTREspecialidad_c.'</ns3:CTREspecialidad_c>':'').'
			 					'.(isset($user->CTRAnoMesquefinalizoEstSup_c)?'<ns3:CTRAnoMesquefinalizoEstSup_c>'.$user->CTRAnoMesquefinalizoEstSup_c.'</ns3:CTRAnoMesquefinalizoEstSup_c>':'').'
			 					'.(isset($user->CTRNivelacademico_c)?'<ns3:CTRNivelacademico_c>'.$user->CTRNivelacademico_c.'</ns3:CTRNivelacademico_c>':'').'
			 				</ns1:PersonDEO_InformacionAcademicaCollection_c>';
		}

		$request_xml .= '
						    '.(isset($user->PersonDEO_CTRExalumno_c)?'<ns1:PersonDEO_CTRExalumno_c>'.$user->PersonDEO_CTRExalumno_c.'</ns1:PersonDEO_CTRExalumno_c>':'').'
						    '.(isset($user->PersonDEO_CTRCodigoPUCP_c)?' <ns1:PersonDEO_CTRCodigoPUCP_c>'.$user->PersonDEO_CTRCodigoPUCP_c.'</ns1:PersonDEO_CTRCodigoPUCP_c>':'');

		if(isset($user->CTRInstituciondeidiomas_c) || isset($user->CTRNivelalcanzado_c)
			|| isset($user->CTRAnioMesfinalizoIdioma_c) || isset($user->CTRCiudad_c)){
			$request_xml .= '
							<ns1:PersonDEO_IdiomaCollection_c>
								'.(isset($user->CTRInstituciondeidiomas_c)?'<ns3:CTRInstituciondeidiomas_c>'.$user->CTRInstituciondeidiomas_c.'</ns3:CTRInstituciondeidiomas_c>':'').'
								'.(isset($user->CTRNivelalcanzado_c)?'<ns3:CTRNivelalcanzado_c>'.$user->CTRNivelalcanzado_c.'</ns3:CTRNivelalcanzado_c>':'').'
								'.(isset($user->CTRAnioMesfinalizoIdioma_c)?'<ns3:CTRAnioMesfinalizoIdioma_c>'.$user->CTRAnioMesfinalizoIdioma_c.'</ns3:CTRAnioMesfinalizoIdioma_c>':'').'
								'.(isset($user->CTRCiudad_c)?'<ns3:CTRCiudad_c>'.$user->CTRCiudad_c.'</ns3:CTRCiudad_c>':'').'
							</ns1:PersonDEO_IdiomaCollection_c>';
		}

		 $request_xml .= '
						    '.(isset($user->PersonDEO_CTRCompania_c)?'<ns1:PersonDEO_CTRCompania_c>'.$user->PersonDEO_CTRCompania_c.'</ns1:PersonDEO_CTRCompania_c>':'').'
						    '.(isset($user->JobTitle)?'<ns1:JobTitle>'.$user->JobTitle.'</ns1:JobTitle>':'').'
						    '.(isset($user->WorkPhoneNumber)?'<ns1:WorkPhoneNumber>'.$user->WorkPhoneNumber.'</ns1:WorkPhoneNumber>':'').'
						    '.(isset($user->WorkPhoneExtension)?'<ns1:WorkPhoneExtension>'.$user->WorkPhoneExtension.'</ns1:WorkPhoneExtension>':'').'
						    '.(isset($user->FaxNumber)?'<ns1:FaxNumber>'.$user->FaxNumber.'</ns1:FaxNumber>':'').'
						    '.(isset($user->PersonDEO_CTRAniosdeexperiencia_c)?'<ns1:PersonDEO_CTRAniosdeexperiencia_c>'.$user->PersonDEO_CTRAniosdeexperiencia_c.'</ns1:PersonDEO_CTRAniosdeexperiencia_c>':'').'
						    '.(isset($user->PersonDEO_CTRObservacion_c)?' <ns1:PersonDEO_CTRObservacion_c>'.$user->PersonDEO_CTRObservacion_c.'</ns1:PersonDEO_CTRObservacion_c>':'').'
						    '.(isset($user->PersonDEO_CTRProcedencia_c)?' <ns1:PersonDEO_CTRProcedencia_c>'.$user->PersonDEO_CTRProcedencia_c.'</ns1:PersonDEO_CTRProcedencia_c>':'').'
						    '.(isset($user->PersonDEO_CTRAutorizoDatosPersonFinesM_c)?' <ns1:PersonDEO_CTRAutorizoDatosPersonFinesM_c>'.$user->PersonDEO_CTRAutorizoDatosPersonFinesM_c.'</ns1:PersonDEO_CTRAutorizoDatosPersonFinesM_c>':'').'
						    '.(isset($user->PersonDEO_CTRAutorizoEnvInfProgAca_c)?' <ns1:PersonDEO_CTRAutorizoEnvInfProgAca_c>'.$user->PersonDEO_CTRAutorizoEnvInfProgAca_c.'</ns1:PersonDEO_CTRAutorizoEnvInfProgAca_c>':'').'
						    '.(isset($user->PersonDEO_CTRSalarioMedioAnual_c)?' <ns1:PersonDEO_CTRSalarioMedioAnual_c>'.$user->PersonDEO_CTRSalarioMedioAnual_c.'</ns1:PersonDEO_CTRSalarioMedioAnual_c>':'').'
						    '.(isset($user->CurrencyCode)?' <ns1:CurrencyCode>'.$user->CurrencyCode.'</ns1:CurrencyCode>':'').'
						    <ns1:OwnerPartyId>'.$user->OwnerPartyId.'</ns1:OwnerPartyId>
						    <ns1:Type>'.$user->Type.'</ns1:Type>
						</ns4:contact>
					</ns4:createContact>
				</soapenv:Body>
			</soapenv:Envelope>';

		return $request_xml;
	}

	public function checkContact($dataForm, $try, $usuario){
		$client = $this->getClient($this->wsContacto);
		$soapaction = "http://xmlns.oracle.com/apps/crmCommon/salesParties/contactService/findContact";
		if(isset($dataForm->PersonDEO_CTRNrodedocumento_c)){
			$request = $this->getContactRequest($dataForm);
			$response = $client->send($request, $soapaction, '');
			if(isset($response['result'])){
				if(isset($response['result']['Value'])){
					if(isset($response['result']['Value']['PartyId'])){
						return $response['result']['Value'];
					}else{
						DB::table('usuario_crm')->where('id','=',$usuario)->update(array('dupli'=>'1'));
						return $response['result']['Value'][count($response['result']['Value'])-1];
					}
				}else{
					return false;
				}

			}
			$try += 1;
			if($try<3)
				return $this->checkContact($dataForm, $try);
		}
	}

	public function getContactRequest($dataForm){
		$request_xml ='
			<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
				xmlns:typ="http://xmlns.oracle.com/apps/crmCommon/salesParties/contactService/types/"
				xmlns:typ1="http://xmlns.oracle.com/adf/svc/types/">
				<soapenv:Header/>
				<soapenv:Body>
					<typ:findContact>
						<typ:findCriteria>
							<typ1:fetchStart>0</typ1:fetchStart>
						    <typ1:fetchSize>-1</typ1:fetchSize>
						    <typ1:filter>
						    	<typ1:group>
						        	<typ1:conjunction>And</typ1:conjunction>
						            <typ1:item>
		                             	<typ1:conjunction>And</typ1:conjunction>
										<typ1:upperCaseCompare>false</typ1:upperCaseCompare>
										<typ1:attribute>PersonDEO_CTRNrodedocumento_c</typ1:attribute>
										<typ1:operator>=</typ1:operator>
										<typ1:value>'.$dataForm->PersonDEO_CTRNrodedocumento_c.'</typ1:value>
									</typ1:item>
						        </typ1:group>
						    </typ1:filter>
						    <typ1:findAttribute>PartyId</typ1:findAttribute>
						    <typ1:findAttribute>PrimaryAddress</typ1:findAttribute>

						</typ:findCriteria>
						<typ:findControl>
							<typ1:retrieveAllTranslations>false</typ1:retrieveAllTranslations>
						</typ:findControl>
					</typ:findContact>
				</soapenv:Body>
			</soapenv:Envelope>';
		return $request_xml;
	}

	public function syncEmailUserCRM(){
		$usuarios = DB::table('usuario_crm')
				->where('tipo','=','1')
				->where('crm','=','1')
				->where('email','=','0')
				->where('try','!=','4')
				->orderBy('id','ASC')
				->get();
		if(count($usuarios)>0){
			foreach($usuarios as $usuario){
				$campos = DB::table('campo_crm')->where('user_id','=',$usuario->id)->get();
				if(count($campos)==0){
					$mensaje = 'No existen campos del usuario no se puede enviar email';
					DB::table('usuario_crm')
						->where('user_id','=',$usuario->id)
						->update(array('email'=>'1','error'=>$mensaje));
					continue;
				}
				$dataForm = new stdClass();
				foreach($campos as $campo){
					$dataForm->{$campo->campo} = $campo->valor;
				}

				$correos = array($dataForm->email_final);
				$dataFi = array('dataForm'=>$dataForm);
				$paterno = isset($dataForm->LastName)?$dataForm->LastName:'';
				$materno = isset($dataForm->PersonDEO_CTRApellidoMaterno_c)?$dataForm->PersonDEO_CTRApellidoMaterno_c:'';
				$nombre = isset($dataForm->FirstName)?$dataForm->FirstName:'';
				$dni =  isset($dataForm->PersonDEO_CTRNrodedocumento_c)?$dataForm->PersonDEO_CTRNrodedocumento_c:'';
				$subject = '';

				switch ($dataForm->origen) {
					case '1':
						$subject = 'PROCESO DE ADMISIÓN - '.$paterno.' '.$materno.', '.$nombre.' - '.$dni;
						break;
					case '2':
						$subject = 'MAS INFORMACIÓN - '.$paterno.' '.$materno.', '.$nombre.' - '.$dni;
						break;
					case '3':
					case '6':
						$subject = 'INSCRIPCIÓN A CHARLA - '.$paterno.' '.$materno.', '.$nombre.' - '.$dni;
						break;
					case '4':
					case '5':
						$subject = 'INSCRIPCIÓN A EVENTOS - '.$paterno.' '.$materno.', '.$nombre.' - '.$dni;
						break;

				}

				$correos_add = DB::table('correo')
					->where('co_codigo_programa','=',$dataForm->programa)
					->where('co_estado','=','1')->get();

				if(count($correos_add)>0){
					foreach($correos_add as $correo){
						$correos[] = $correo->co_correo;
					}
				}

				foreach($correos as $email_usuario){
					Mail::queue('emails.admision',$dataFi, function($message) use ($email_usuario, $subject){
						$message->to($email_usuario)->subject($subject);
						$message->cc('centrum.informes@pucp.pe')->subject($subject);
					});
				}

				DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('email'=>'1'));

			}
		}

		return 'Se envío correctamente los correos';
	}

	public function syncEmailDupliCRM(){
		$usuarios = DB::table('usuario_crm')
				->where('tipo','=','1')
				->where('dupli','=','1')
				->where('edupli','=','0')
				->orderBy('id','ASC')
				->get();
		if(count($usuarios)>0){
			foreach($usuarios as $usuario){
				$campos = DB::table('campo_crm')->where('user_id','=',$usuario->id)->get();
				if(count($campos)==0){
					$mensaje = 'No existen campos del usuario no se puede enviar email';
					DB::table('usuario_crm')
						->where('user_id','=',$usuario->id)
						->update(array('email'=>'1','error'=>$mensaje));
					continue;
				}
				$dataForm = new stdClass();
				foreach($campos as $campo){
					$dataForm->{$campo->campo} = $campo->valor;
				}

				$correos = array('abarbozab@pucp.pe');
				$correos[] = 'cesar.huasupoma@pucp.pe';
				$dataFi = array('dataForm'=>$dataForm);
				$paterno = isset($dataForm->LastName)?$dataForm->LastName:'';
				$materno = isset($dataForm->PersonDEO_CTRApellidoMaterno_c)?$dataForm->PersonDEO_CTRApellidoMaterno_c:'';
				$nombre = isset($dataForm->FirstName)?$dataForm->FirstName:'';
				$dni =  isset($dataForm->PersonDEO_CTRNrodedocumento_c)?$dataForm->PersonDEO_CTRNrodedocumento_c:'';
				$subject = '';

				switch ($dataForm->origen) {
					case '1':
						$subject = 'DUPLICATE PROCESO DE ADMISIÓN - '.$paterno.' '.$materno.', '.$nombre.' - '.$dni;
						break;
					case '2':
						$subject = 'DUPLICATE MAS INFORMACIÓN - '.$paterno.' '.$materno.', '.$nombre.' - '.$dni;
						break;
					case '3':
						$subject = 'DUPLICATE INSCRIPCIÓN A CHARLA - '.$paterno.' '.$materno.', '.$nombre.' - '.$dni;
						break;
					case '4':
						$subject = 'DUPLICATE INSCRIPCIÓN A EVENTOS - '.$paterno.' '.$materno.', '.$nombre.' - '.$dni;
						break;
				}

				foreach($correos as $email_usuario){
					Mail::queue('emails.admision',$dataFi, function($message) use ($email_usuario, $subject){
						$message->to($email_usuario)->subject($subject);
					});
				}

				DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('edupli'=>'1'));

			}
		}

		return 'Se envío correctamente los correos';
	}

	public function syncEmailErrorCRM(){
		$usuarios = DB::table('usuario_crm')
				->where('tipo','=','1')
				->where('crm','=','0')
				->where('try','=','4')
				->where('email','=','0')
				->orderBy('id','ASC')
				->get();
		if(count($usuarios)>0){
			foreach($usuarios as $usuario){
				$campos = DB::table('campo_crm')->where('user_id','=',$usuario->id)->get();
				if(count($campos)==0){
					$mensaje = 'No existen campos del usuario no se puede enviar email';
					DB::table('usuario_crm')
						->where('user_id','=',$usuario->id)
						->update(array('email'=>'1','error'=>$mensaje));
					continue;
				}
				$dataForm = new stdClass();
				foreach($campos as $campo){
					$dataForm->{$campo->campo} = $campo->valor;
				}
				$correos = array('abarbozab@pucp.pe');
				$correos[] = 'cesar.huasupoma@pucp.pe';
				$dataFi = array('dataForm'=>$dataForm);
				$subject = '';

				switch ($dataForm->origen) {
					case '1':
						$subject = 'ERROR PROCESO DE ADMISIÓN - '.$usuario->error;
						break;
					case '2':
						$subject = 'ERROR MAS INFORMACIÓN - '.$usuario->error;
						break;
					case '3':
						$subject = 'ERROR INSCRIPCIÓN A CHARLA - '.$usuario->error;
						break;
					case '4':
						$subject = 'ERROR INSCRIPCIÓN A EVENTOS - '.$usuario->error;
						break;
				}
				foreach($correos as $email_usuario){
					Mail::queue('emails.admision',$dataFi, function($message) use ($email_usuario, $subject){
						$message->to('abarbozab@pucp.pe')->subject($subject);
					});
				}

				DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('email'=>'1'));

			}
		}

		return 'Se envío correctamente los correos';
	}

	public function updateContact($contacto, $dataForm, $usuario){
		$leads = $this->buscarLead($contacto, 1);
		if(isset($leads)){
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

			$vendedores = DB::table('resource')
						->select(
								'ResourceId'
								,'ResourceName'
								,'EmailAddress'
							)
						->where('Id','=',$dataForm->programa)
						->orderBy('CTROrdenRuleta_c', 'ASC')->get();

			if(count($vendedores)==0){
				$error = 'El programa no tiene vendedores activos al actualizar';
				DB::table('usuario_crm')
					->where('id','=',$usuario->id)
					->update(array('try'=>($usuario->try+1), 'error'=>$error));
				return $error;
			}

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
					
  					if(!isset($dataForm->tipoDMA)){
						//3
  						$dataForm->atendido = $vend->ResourceId;
  						$dataForm->OwnerPartyId = $dataForm->atendido;
  						$lead = $this->executeUpdateContact($contacto, $dataForm, $usuario);
  						if(isset($lead['OwnerId'])){
  							$this->validateUpdateContact($vendedores,$lead, $le, $usuario, $dataForm, $vend);
  						}

  						return 'Se registro correctamente';
				  	}

				  	if($dataForm->tipoDMA=="N"){
						//2
				  		$dataForm->atendido = $vend->ResourceId;
  						$dataForm->OwnerPartyId = $dataForm->atendido;
  						$lead = $this->executeUpdateContact($contacto, $dataForm, $usuario);
  						if(isset($lead['OwnerId'])){
  							$this->validateUpdateContact($vendedores,$lead, $le, $usuario, $dataForm, $vend);
  						}
  						return 'Se registro correctamente';
				  	}

				  	$val = '';
				  	if(!isset($dataForm->tipoDMA)) $val = 'year';
  					if($dataForm->tipoDMA=="") $val = 'year';
  					if($dataForm->tipoDMA=="A") $val = 'year';
  					if($dataForm->tipoDMA=="M") $val = 'month';
  					if($dataForm->tipoDMA=="D") $val = 'day';

  					$dataForm->valorC = isset($dataForm->valorC)?$dataForm->valorC:'';
  					$dataForm->valorC==""?$this->yearFi:$dataForm->valorC;
  					$today = time();
  					$before = strtotime('-'.$dataForm->valorC.' '.$val, $today);
  					if($le["fechafinal"]>=$before){
						//1
  						$dataForm->atendido = $vend->ResourceId;
  						$dataForm->OwnerPartyId = $dataForm->atendido;
  						$lead = $this->executeUpdateContact($contacto, $dataForm, $usuario);
  						if(isset($lead['OwnerId'])){
  							$this->validateUpdateContact($vendedores,$lead, $le, $usuario, $dataForm, $vend);
  						}
  						return 'Se registro correctamente';
  					}
  				}
  			}
		}
		$count = 1;
		if($dataForm->atendido=="NSNR"){
			$vendedores = DB::table('resource')
						->select(
								'ResourceId'
								,'ResourceName'
								,'EmailAddress'
							)
						->where('Id','=',$dataForm->programa)
						->where('CTRRuleta_c','=','true')
						->orderBy('CTROrdenRuleta_c', 'ASC')->get();
			if(count($vendedores)==0){
				$error = 'El programa no tiene vendedores para la ruleta';
				DB::table('usuario_crm')
					->where('id','=',$usuario->id)
					->update(array('try'=>($usuario->try+1), 'error'=>$error));
				return $error;
			}

			$nextVendedor = DB::table('sailor')
							->where('producto','=',$dataForm->programa)
							->where('posicion','!=','0')->first();
			if(isset($nextVendedor)){
				$nextVendedor = $nextVendedor->posicion;
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
			$vend = DB::table('resource')
						->select(
								'ResourceId'
								,'ResourceName'
								,'EmailAddress'
							)
						->where('Id','=',$dataForm->programa)
						->where('ResourceId','=',$dataForm->atendido)->first();

			if(!isset($vend)){
				$error = 'El programa no tiene el vendedor indicado';
				DB::table('usuario_crm')
					->where('id','=',$usuario->id)
					->update(array('try'=>($usuario->try+1), 'error'=>$error));
				return $error;
			}
		}

		$dataForm->atendido = $vend->ResourceId;
  		$dataForm->OwnerPartyId = $dataForm->atendido;
  		$vendedores_2 = DB::table('resource')
						->select(
								'ResourceId'
								,'ResourceName'
								,'EmailAddress'
							)
						->where('Id','=',$dataForm->programa)
						->orderBy('CTROrdenRuleta_c', 'ASC')->get();
  		$lead = $this->executeUpdateContact($contacto, $dataForm, $usuario);
  		if(isset($lead['OwnerId'])){
  			if($lead['OwnerId'] != $vend->ResourceId){
	  			$vend = null;
	  			foreach($vendedores_2 as $vendedor){
	  				if($lead['OwnerId'] == $vendedor->ResourceId){
	  					$vend = $vendedor;
	  				}
	  			}
	  			if(!isset($vend)){
	  				$error = 'Se creo el contacto y el lead pero no se va poder enviar correo xq la vendedora no esta en los usuarios';
					DB::table('usuario_crm')
						->where('id','=',$usuario->id)
						->update(array('try'=>($usuario->try+1), 'error'=>$error));
				}
			}
			if(isset($vend)){
				DB::table('sailor_log')->insert(array(
					'producto'=>$dataForm->programa
					,'vendedor'=>$vend->ResourceId
					,'fecha_reg'=>time()
				));

				if(isset($vendedores)){
					DB::table('sailor')->where('producto','=',$dataForm->programa)->delete();
					$count_2 = 1;
					$vend_2 = null;
					foreach($vendedores as $vendedor){
						if($vend->ResourceId == $vendedor->ResourceId){
							$vend_2 = $vendedor;
						}
					}
					if(!isset($vend_2)){
						$error = 'Se creo el contacto y el lead pero no se va poder enviar correo xq la vendedora no esta en la ruleta';
						DB::table('usuario_crm')
							->where('id','=',$usuario->id)
							->update(array('try'=>($usuario->try+1), 'error'=>$error));
					}else{
						foreach($vendedores as $vendedor){
							$pos = 0;
							if($count == $count_2){
								$pos = $count+1;
								if($pos > count($vendedores)){
									$pos = 1;
								}
							}
							DB::table('sailor')->insert(array(
													'id'=>$count_2
													,'posicion'=>$pos
													,'estado'=>1
													,'producto'=>$dataForm->programa
													,'vendedor'=>$vendedor->ResourceId
												));
							$count_2++;
						}

						DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('crm'=>'1'));
						DB::table('campo_crm')->insert(array('user_id'=>$usuario->id, 'campo'=>'email_final', 'valor'=>$vend->EmailAddress));

					}
				}


			}
		}

		return 'Se registro correctamente';



	}

	public function executeUpdateContact($contacto, $dataForm, $usuario){
		$userUpdated = $this->updateContactMassive($contacto, $dataForm, 1);
  		if(!isset($userUpdated)){
			$error = 'Error al actualizar un contacto';
			DB::table('usuario_crm')
				->where('id','=',$usuario->id)
				->update(array('try'=>($usuario->try+1), 'error'=>$error));
			return $error;
		}

		if($dataForm->origen==1){
            $merged = $this->mergeContacto($contacto,$dataForm, 1);
            if(!isset($merged)){
            	$error = 'Error al realizar el merge del contacto';
				DB::table('usuario_crm')
					->where('id','=',$usuario->id)
					->update(array('try'=>($usuario->try+1), 'error'=>$error));
				return $error;
            }
        }

        $this->updatePerson($contacto, $dataForm);
        if(!isset($dataForm->productId)){
        	$error = 'Error el programa no tiene product id';
				DB::table('usuario_crm')
					->where('id','=',$usuario->id)
					->update(array('try'=>($usuario->try+1), 'error'=>$error));
				return $error;
        }
        $anterior = $this->buscarLeadFind($contacto,$dataForm, 1);
        if(!isset($anterior)){
            $lead = $this->createLead($contacto,$dataForm,1);
            if(!isset($lead)){
				$error = 'Error al crear el lead de un contacto ya creado';
				DB::table('usuario_crm')
					->where('id','=',$usuario->id)
					->update(array('try'=>($usuario->try+1), 'error'=>$error));
				return $error;
			}
			return $lead;
		}else{
			$lead = $this->updateLead($anterior,$dataForm, 1);
			if(!isset($lead)){
				$error = 'Error al crear el lead de un contacto ya creado';
				DB::table('usuario_crm')
					->where('id','=',$usuario->id)
					->update(array('try'=>($usuario->try+1), 'error'=>$error));
				return $error;
			}
			return $lead;
        }
    }

    public function validateUpdateContact($vendedores,$lead, $le, $usuario, $dataForm, $vend){
    	if($lead['OwnerId'] != $le['OwnerId']){
  			$vend = null;
  			foreach($vendedores as $vendedor){
  				if($lead['OwnerId'] == $vendedor->ResourceId){
  					$vend = $vendedor;
  				}
  			}
  			if(!isset($vend)){
  				$error = 'Se creo el contacto y el lead pero no se va poder enviar correo';
				DB::table('usuario_crm')
					->where('id','=',$usuario->id)
					->update(array('try'=>($usuario->try+1), 'error'=>$error));
			}
		}

		if(isset($vend)){
			DB::table('sailor_log')->insert(array(
				'producto'=>$dataForm->programa
				,'vendedor'=>$vend->ResourceId
				,'fecha_reg'=>time()
			));

			DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('crm'=>'1'));

			DB::table('campo_crm')->insert(array('user_id'=>$usuario->id, 'campo'=>'email_final', 'valor'=>$vend->EmailAddress));
		}
    }

	public function buscarLead($contacto, $try){
		$client = $this->getClient($this->wsLead);
		$soapaction = "http://xmlns.oracle.com/apps/marketing/leadMgmt/leads/leadService/findSalesLead";
		$request = $this->buscarLeadRequest($contacto);
		$response = $client->send($request, $soapaction, '');
		if(isset($response['result'])){
			if(isset($response['result']['Name'])){
				return array($response['result']);
			}else{
				return $response["result"];
			}
		}

		$try += 1;
		if($try<3)
			return $this->buscarLead($contacto, $try);
	}

	public function buscarLeadRequest($contacto){
		$request_xml ='
			<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:typ="http://xmlns.oracle.com/apps/marketing/leadMgmt/leads/leadService/types/" xmlns:typ1="http://xmlns.oracle.com/adf/svc/types/">
				<soapenv:Header/>
				<soapenv:Body>
					<typ:findSalesLead>
						<typ:findCriteria>
					    	<typ1:fetchStart>0</typ1:fetchStart>
					        <typ1:fetchSize>-1</typ1:fetchSize>
					        <typ1:filter>
						        <typ1:group>
						        	<typ1:conjunction>And</typ1:conjunction>
						            <typ1:item>
						            	<typ1:conjunction>And</typ1:conjunction>
										<typ1:upperCaseCompare>false</typ1:upperCaseCompare>
										<typ1:attribute>CustomerId</typ1:attribute>
										<typ1:operator>=</typ1:operator>
										<typ1:value>'.$contacto["PartyId"].'</typ1:value>
									</typ1:item>
								</typ1:group>
						    </typ1:filter>
					    </typ:findCriteria>
					    <typ:findControl>
					    	<typ1:retrieveAllTranslations>false</typ1:retrieveAllTranslations>
					    </typ:findControl>
					</typ:findSalesLead>
				</soapenv:Body>
			</soapenv:Envelope>';
		return $request_xml;
	}

	public function determinarUltimo($leads){
		$arrayFinal = array();
		foreach($leads as $lead){
			$fecha1 = isset($lead["CTRActualizado_c"])?strtotime(str_replace('/','-',substr($lead["CTRActualizado_c"], 0,10))):false;
			$fecha2 = strtotime($lead["LastUpdateDate"]);
			$fecha1 = $fecha1==false? $fecha2:$fecha1;
			$lead["fechafinal"] = $fecha1;
			$arrayFinal[]= $lead;
		}

		if(count($arrayFinal)>1){
			for($i=0;$i<count($arrayFinal)-1;$i++){
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

	public function updateContactMassive($contacto, $dataForm, $try){
		$client = $this->getClient($this->wsContacto);
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
                            '.(isset($dataForm->SalutoryIntroduction)?'<ns1:SalutoryIntroduction>'.$dataForm->SalutoryIntroduction.'</ns1:SalutoryIntroduction>':'').'
                            '.(isset($dataForm->FirstName)?'<ns1:FirstName>'.$dataForm->FirstName.'</ns1:FirstName>':'').'
                            '.(isset($dataForm->LastName)?'<ns1:LastName>'.$dataForm->LastName.'</ns1:LastName>':'').'
                            '.(isset($dataForm->PersonDEO_CTRApellidoMaterno_c)?'<ns1:PersonDEO_CTRApellidoMaterno_c>'.$dataForm->PersonDEO_CTRApellidoMaterno_c.'</ns1:PersonDEO_CTRApellidoMaterno_c>':'').'
                            <ns1:PersonDEO_CTRTipodedocumento_c>'.$dataForm->PersonDEO_CTRTipodedocumento_c.'</ns1:PersonDEO_CTRTipodedocumento_c>
                            <ns1:PersonDEO_CTRNrodedocumento_c>'.$dataForm->PersonDEO_CTRNrodedocumento_c.'</ns1:PersonDEO_CTRNrodedocumento_c>
                            '.(isset($dataForm->HomePhoneNumber)?'<ns1:HomePhoneNumber>'.$dataForm->HomePhoneNumber.'</ns1:HomePhoneNumber>':'').'
                            '.(isset($dataForm->MobileNumber)?'<ns1:MobileNumber>'.$dataForm->MobileNumber.'</ns1:MobileNumber>':'');

        if((isset($dataForm->AddressElementAttribute2) || isset($dataForm->AddressElementAttribute3)
        	|| isset($dataForm->AddressLine1) || isset($dataForm->Country) || isset($dataForm->City))
        	&& isset($contacto["PrimaryAddress"])){
            $request_xml .= '
                            <ns1:PrimaryAddress>
                                '.(isset($dataForm->AddressElementAttribute2)?'<ns2:AddressElementAttribute2>'.$dataForm->AddressElementAttribute2.'</ns2:AddressElementAttribute2>':'').'
                                '.(isset($dataForm->AddressElementAttribute3)?'<ns2:AddressElementAttribute3>'.$dataForm->AddressElementAttribute3.'</ns2:AddressElementAttribute3>':'').'
                                '.(isset($dataForm->AddressLine1)?'<ns2:AddressLine1>'.$dataForm->AddressLine1.'</ns2:AddressLine1>':'').'
                                '.(isset($dataForm->Country)?'<ns2:Country>'.$dataForm->Country.'</ns2:Country>':'').'
                                '.(isset($dataForm->City)?'<ns2:City>'.$dataForm->City.'</ns2:City>':'').'
                            </ns1:PrimaryAddress>';
        }
         $request_xml .= '
                        	<ns1:DateOfBirth>'.$dataForm->DateOfBirth.'</ns1:DateOfBirth>
                            '.(isset($dataForm->PersonDEO_CTRPaisdenacimiento_c)?'<ns1:PersonDEO_CTRPaisdenacimiento_c>'.$dataForm->PersonDEO_CTRPaisdenacimiento_c.'</ns1:PersonDEO_CTRPaisdenacimiento_c>':'').'
                            '.(isset($dataForm->PersonDEO_CTRCiudaddeNacimiento_c)?'<ns1:PersonDEO_CTRCiudaddeNacimiento_c>'.$dataForm->PersonDEO_CTRCiudaddeNacimiento_c.'</ns1:PersonDEO_CTRCiudaddeNacimiento_c>':'').'
                            '.(isset($dataForm->PersonDEO_CTRNacionalidad_c)?'<ns1:PersonDEO_CTRNacionalidad_c>'.$dataForm->PersonDEO_CTRNacionalidad_c.'</ns1:PersonDEO_CTRNacionalidad_c>':'').'
                            '.(isset($dataForm->EmailAddress)?'<ns1:EmailAddress>'.$dataForm->EmailAddress.'</ns1:EmailAddress>':'').'
                            '.(isset($dataForm->PersonDEO_CTRCorreoPUCP_c)?'<ns1:PersonDEO_CTRCorreoPUCP_c>'.$dataForm->PersonDEO_CTRCorreoPUCP_c.'</ns1:PersonDEO_CTRCorreoPUCP_c>':'').'
                            '.(isset($dataForm->PersonDEO_CTRCuentaSkype_c)?'<ns1:PersonDEO_CTRCuentaSkype_c>'.$dataForm->PersonDEO_CTRCuentaSkype_c.'</ns1:PersonDEO_CTRCuentaSkype_c>':'').'
                            '.(isset($dataForm->PersonDEO_CTRExalumno_c)?'<ns1:PersonDEO_CTRExalumno_c>'.$dataForm->PersonDEO_CTRExalumno_c.'</ns1:PersonDEO_CTRExalumno_c>':'').'
                            '.(isset($dataForm->PersonDEO_CTRCodigoPUCP_c)?' <ns1:PersonDEO_CTRCodigoPUCP_c>'.$dataForm->PersonDEO_CTRCodigoPUCP_c.'</ns1:PersonDEO_CTRCodigoPUCP_c>':'').'
                            '.(isset($dataForm->PersonDEO_CTRCompania_c)?'<ns1:PersonDEO_CTRCompania_c>'.$dataForm->PersonDEO_CTRCompania_c.'</ns1:PersonDEO_CTRCompania_c>':'').'
                            '.(isset($dataForm->JobTitle)?'<ns1:JobTitle>'.$dataForm->JobTitle.'</ns1:JobTitle>':'').'
                            '.(isset($dataForm->WorkPhoneNumber)?'<ns1:WorkPhoneNumber>'.$dataForm->WorkPhoneNumber.'</ns1:WorkPhoneNumber>':'').'
                            '.(isset($dataForm->WorkPhoneExtension)?'<ns1:WorkPhoneExtension>'.$dataForm->WorkPhoneExtension.'</ns1:WorkPhoneExtension>':'').'
                            '.(isset($dataForm->FaxNumber)?'<ns1:FaxNumber>'.$dataForm->FaxNumber.'</ns1:FaxNumber>':'').'
                            '.(isset($dataForm->PersonDEO_CTRAniosdeexperiencia_c)?'<ns1:PersonDEO_CTRAniosdeexperiencia_c>'.$dataForm->PersonDEO_CTRAniosdeexperiencia_c.'</ns1:PersonDEO_CTRAniosdeexperiencia_c>':'').'
                            '.(isset($dataForm->PersonDEO_CTRObservacion_c)?' <ns1:PersonDEO_CTRObservacion_c>'.$dataForm->PersonDEO_CTRObservacion_c.'</ns1:PersonDEO_CTRObservacion_c>':'').'
                            '.(isset($dataForm->PersonDEO_CTRProcedencia_c)?' <ns1:PersonDEO_CTRProcedencia_c>'.$dataForm->PersonDEO_CTRProcedencia_c.'</ns1:PersonDEO_CTRProcedencia_c>':'').'
                            '.(isset($dataForm->PersonDEO_CTRAutorizoDatosPersonFinesM_c)?' <ns1:PersonDEO_CTRAutorizoDatosPersonFinesM_c>'.$dataForm->PersonDEO_CTRAutorizoDatosPersonFinesM_c.'</ns1:PersonDEO_CTRAutorizoDatosPersonFinesM_c>':'').'
                            '.(isset($dataForm->PersonDEO_CTRAutorizoEnvInfProgAca_c)?' <ns1:PersonDEO_CTRAutorizoEnvInfProgAca_c>'.$dataForm->PersonDEO_CTRAutorizoEnvInfProgAca_c.'</ns1:PersonDEO_CTRAutorizoEnvInfProgAca_c>':'').'
                            '.(isset($dataForm->PersonDEO_CTRSalarioMedioAnual_c)?' <ns1:PersonDEO_CTRSalarioMedioAnual_c>'.$dataForm->PersonDEO_CTRSalarioMedioAnual_c.'</ns1:PersonDEO_CTRSalarioMedioAnual_c>':'').'
                            '.(isset($dataForm->CurrencyCode)?' <ns1:CurrencyCode>'.$dataForm->CurrencyCode.'</ns1:CurrencyCode>':'').'
                        </ns4:contact>
                    </ns4:updateContact>
                </soapenv:Body>
            </soapenv:Envelope>';

        return $request_xml;
	}

	public function mergeContacto($contacto, $dataForm, $try){
		$client = $this->getClient($this->wsContacto);
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
        if(isset($user->CTRGradoacademico_c) || isset($user->CTRInstitucionAcademica_c)
        	|| isset($user->CTROtrasUniversidadesInst_c) || isset($user->CTREspecialidad_c)
        		|| isset($user->CTRAnoMesquefinalizoEstSup_c) || isset($user->CTRNivelacademico_c)){
            $request_xml .= '
        		<con:PersonDEO_InformacionAcademicaCollection_c>
                    '.(isset($user->CTRGradoacademico_c)?'<per:CTRGradoacademico_c>'.$user->CTRGradoacademico_c.'</per:CTRGradoacademico_c>':'').'
                    '.(isset($user->CTRInstitucionAcademica_c)?'<per:CTRInstitucionAcademica_c>'.$user->CTRInstitucionAcademica_c.'</per:CTRInstitucionAcademica_c>':'').'
                    '.(isset($user->CTROtrasUniversidadesInst_c)?'<per:CTROtrasUniversidadesInst_c>'.$user->CTROtrasUniversidadesInst_c.'</per:CTROtrasUniversidadesInst_c>':'').'
                    '.(isset($user->CTREspecialidad_c)?'<per:CTREspecialidad_c>'.$user->CTREspecialidad_c.'</per:CTREspecialidad_c>':'').'
                    '.(isset($user->CTRAnoMesquefinalizoEstSup_c)?'<per:CTRAnoMesquefinalizoEstSup_c>'.$user->CTRAnoMesquefinalizoEstSup_c.'</per:CTRAnoMesquefinalizoEstSup_c>':'').'
                    '.(isset($user->CTRNivelacademico_c)?'<per:CTRNivelacademico_c>'.$user->CTRNivelacademico_c.'</per:CTRNivelacademico_c>':'').'
                </con:PersonDEO_InformacionAcademicaCollection_c>';
        }

        if(isset($user->CTRInstituciondeidiomas_c) || isset($user->CTRNivelalcanzado_c)
        	|| isset($user->CTRAnioMesfinalizoIdioma_c) || isset($user->CTRCiudad_c)){
            $request_xml .= '
        		<con:PersonDEO_IdiomaCollection_c>
                    '.(isset($user->CTRInstituciondeidiomas_c)?'<per:CTRInstituciondeidiomas_c>'.$user->CTRInstituciondeidiomas_c.'</per:CTRInstituciondeidiomas_c>':'').'
                    '.(isset($user->CTRNivelalcanzado_c)?'<per:CTRNivelalcanzado_c>'.$user->CTRNivelalcanzado_c.'</per:CTRNivelalcanzado_c>':'').'
                    '.(isset($user->CTRAnioMesfinalizoIdioma_c)?'<per:CTRAnioMesfinalizoIdioma_c>'.$user->CTRAnioMesfinalizoIdioma_c.'</per:CTRAnioMesfinalizoIdioma_c>':'').'
                    '.(isset($user->CTRCiudad_c)?'<per:CTRCiudad_c>'.$user->CTRCiudad_c.'</per:CTRCiudad_c>':'').'
                </con:PersonDEO_IdiomaCollection_c>';
        }

        if((isset($user->AddressElementAttribute2) || isset($user->AddressElementAttribute3)
        	|| isset($user->AddressLine1) || isset($user->Country) || isset($user->City))
        	 && isset($contacto["PrimaryAddress"])==false){
            $request_xml .= '
                <con:PrimaryAddress>
                	<com:PartyId>'.$contacto['PartyId'].'</com:PartyId>
                	'.(isset($user->AddressElementAttribute2)?'<com:AddressElementAttribute2>'.$user->AddressElementAttribute2.'</com:AddressElementAttribute2>':'').'
                    '.(isset($user->AddressElementAttribute3)?'<com:AddressElementAttribute3>'.$user->AddressElementAttribute3.'</com:AddressElementAttribute3>':'').'
                    '.(isset($user->AddressLine1)?'<com:AddressLine1>'.$user->AddressLine1.'</com:AddressLine1>':'').'
                    '.(isset($user->Country)?'<com:Country>'.$user->Country.'</com:Country>':'').'
                    '.(isset($user->City)?'<com:City>'.$user->City.'</com:City>':'').'
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

	public function updatePerson($contacto, $dataForm){
		$emails = $this->getPerson($contacto, 1);
		if(isset($emails)){
			if(isset($emails['Email'])){
				$client = $this->getClient($this->wsPerson);
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
        	if(isset($user->EmailAddress2)){
        		if($user->EmailAddress2 == $email["EmailAddress"])
        			$email1 = false;
        	}else{
        		$email1 = false;
        	}

        	if(isset($user->PersonDEO_CTRCorreoPUCP_c)){
        		if($user->PersonDEO_CTRCorreoPUCP_c == $email["EmailAddress"])
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
					<con:EmailAddress>'.$user->EmailAddress2.'</con:EmailAddress>
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
					<con:EmailAddress>'.$user->PersonDEO_CTRCorreoPUCP_c.'</con:EmailAddress>
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

	public function buscarLeadFind($contacto, $dataForm, $try){
		$client = $this->getClient($this->wsLead);
		$soapaction = "http://xmlns.oracle.com/apps/marketing/leadMgmt/leads/leadService/findSalesLead";
		$request = $this->buscarLeadFindRequest($contacto, $dataForm);
		$response = $client->send($request, $soapaction, '');
		if(isset($response['result'])){
			if(isset($response['result']['Name']))
				return $response['result'];
			else
				return $response['result'][count($response['result'])-1];
		}

		$try += 1;
		if($try<3)
			return $this->buscarLeadFind($contacto, $dataForm, $try);

	}

	public function buscarLeadFindRequest($contacto, $dataForm){
		$request_xml ='
			<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
				xmlns:typ="http://xmlns.oracle.com/apps/marketing/leadMgmt/leads/leadService/types/"
				xmlns:typ1="http://xmlns.oracle.com/adf/svc/types/">
				<soapenv:Header/>
				<soapenv:Body>
					<typ:findSalesLead>
						<typ:findCriteria>
					    	<typ1:fetchStart>0</typ1:fetchStart>
					        <typ1:fetchSize>-1</typ1:fetchSize>
					        <typ1:filter>
						        <typ1:group>
						        	<typ1:conjunction>And</typ1:conjunction>
						            <typ1:item>
						            	<typ1:conjunction>And</typ1:conjunction>
										<typ1:upperCaseCompare>false</typ1:upperCaseCompare>
										<typ1:attribute>CustomerId</typ1:attribute>
										<typ1:operator>=</typ1:operator>
										<typ1:value>'.$contacto["PartyId"].'</typ1:value>
									</typ1:item>
									<typ1:item>
						               	<typ1:conjunction>And</typ1:conjunction>
										<typ1:upperCaseCompare>false</typ1:upperCaseCompare>
										<typ1:attribute>CTRProductoAsociado_Id_c</typ1:attribute>
										<typ1:operator>=</typ1:operator>
										<typ1:value>'.$dataForm->productId.'</typ1:value>
									</typ1:item>
								</typ1:group>
						    </typ1:filter>
					    </typ:findCriteria>
					    <typ:findControl>
					        <typ1:retrieveAllTranslations>false</typ1:retrieveAllTranslations>
					    </typ:findControl>
					</typ:findSalesLead>
				</soapenv:Body>
			</soapenv:Envelope>';

			return $request_xml;
	}

	public function updateLead($anterior, $dataForm, $try){
		$client = $this->getClient($this->wsLead);
		$soapaction = "http://xmlns.oracle.com/apps/marketing/leadMgmt/leads/leadService/updateSalesLead";
		$request = $this->updateLeadRequest($anterior, $dataForm);
		$response = $client->send($request, $soapaction, '');
		if(isset($response['result'])){
			return $response['result'];
		}

		$try += 1;
		if($try<3)
			return $this->updateLead($anterior, $dataForm, $try);

	}

	public function updateLeadRequest($anterior, $dataForm){
		$request_otroMedio = '';
		if(isset($dataForm->PersonDEO_CTRProcedencia_c)){
			if(!isset($dataForm->OtroMedio))
				$dataForm->OtroMedio = '';
			if($dataForm->PersonDEO_CTRProcedencia_c==8)
				$request_otroMedio = '<lead:CTROtroMedio_c>'.$dataForm->OtroMedio.'</lead:CTROtroMedio_c>';
		}
		$request_charla = '';
		if(isset($dataForm->CTRFechaCharla_c)){
			$request_charla = '<lead:CTRFechaCharla_c>'.$dataForm->CTRFechaCharla_c.'</lead:CTRFechaCharla_c>';
		}

		$request_xml = '
			<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
				xmlns:typ="http://xmlns.oracle.com/apps/marketing/leadMgmt/leads/leadService/types/"
				xmlns:lead="http://xmlns.oracle.com/oracle/apps/marketing/leadMgmt/leads/leadService/"
				xmlns:lead1="http://xmlns.oracle.com/apps/marketing/leadMgmt/leads/leadService/"
				xmlns:not="http://xmlns.oracle.com/apps/crmCommon/notes/noteService"
				xmlns:not1="http://xmlns.oracle.com/apps/crmCommon/notes/flex/noteDff/">
				<soapenv:Header/>
				<soapenv:Body>
					<typ:updateSalesLead>
						<typ:salesLead>
					    	<lead:LeadId>'.$anterior['LeadId'].'</lead:LeadId>
					        <lead:CTR_OrigenDelRegistro_c>'.$dataForm->CTR_OrigenDelRegistro_c.'</lead:CTR_OrigenDelRegistro_c>
					        <lead:Name>'.$dataForm->FirstName.' '.$dataForm->LastName.' - '.$dataForm->nombrePro.'</lead:Name>
                            '.$request_otroMedio.'
                            '.$request_charla.'
					    </typ:salesLead>
					</typ:updateSalesLead>
				</soapenv:Body>
			</soapenv:Envelope>';

			return $request_xml;

	}

}
