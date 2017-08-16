<?php
namespace Web;
use Silex\Application;
use Web\Soap;
class Person{
	
	var $wsPerson = 'https://cang-test.crm.us2.oraclecloud.com:443/foundationParties/PersonService?WSDL';
	
    public function getPerson($userInserted, $try){
		$soap = new Soap();
		$client = $soap->getClient($this->wsPerson);
		$soapaction = "http://xmlns.oracle.com/apps/cdm/foundation/parties/personService/applicationModule/findPerson";
		$request = $this->getPersonRequest($userInserted);
		$response = $client->send($request, $soapaction, '');
		
		$savexml = new \Web\Logsrv();
		$savexml->savelog($request,'Person-request');
		$savexml->savelog( json_encode($response),'Person-response');
		
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
		//var_dump($request_xml);
		return $request_xml;
	}
}
?>