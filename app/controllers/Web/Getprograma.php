<?php
namespace Web;
use Silex\Application;
use Web\Soap;
class Getprograma{
	var $wsPrograma = 'https://cang-test.crm.us2.oraclecloud.com:443/mktExtensibility/MarketingCustomObjectService?WSDL';
    public function programas(){
		$soap = new Soap();
		
		$client = $soap->getClient($this->wsPrograma);
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
}
?>