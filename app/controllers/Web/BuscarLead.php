<?php

namespace Web;

use Silex\Application;
use Web\Soap;

class BuscarLead{
	var $wsLead = 'https://cang-test.crm.us2.oraclecloud.com:443/mklLeads/SalesLeadService?WSDL';
    public function buscarLead($contacto, $try){
		$soap = new Soap();
		$client = $soap->getClient($this->wsLead);
		$soapaction = "http://xmlns.oracle.com/apps/marketing/leadMgmt/leads/leadService/findSalesLead";
		$request = $this->buscarLeadRequest($contacto);
		$response = $client->send($request, $soapaction, '');
		
		if(!empty($response['faultstring'])){
			return $response['faultstring'];
		}
		
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
}    
?>