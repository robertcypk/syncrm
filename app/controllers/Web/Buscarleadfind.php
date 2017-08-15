<?php
namespace Web;
use Silex\Application;
use Web\Soap;
class Buscarleadfind{
	var $wsLead = 'https://cang-test.crm.us2.oraclecloud.com:443/mklLeads/SalesLeadService?WSDL';
    public function buscarLeadFind($contacto, $dataForm, $try){
		$soap = new Soap();
		$client = $soap->getClient($this->wsLead);
		$soapaction = "http://xmlns.oracle.com/apps/marketing/leadMgmt/leads/leadService/findSalesLead";
		$request = $this->buscarLeadFindRequest($contacto, $dataForm);
		$response = $client->send($request, $soapaction, '');
		
		if(!empty($response['faultstring'])){
			return $response;
		}
		
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
										<typ1:value>'.$dataForm->programa.'</typ1:value>
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
}
?>