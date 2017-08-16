<?php
namespace Web;
use Web\Person;
use Web\Soap;
use Web\Emailuser;
use Silex\Application;
class Uperson{
	var $wsPerson = 'https://cang-test.crm.us2.oraclecloud.com:443/foundationParties/PersonService?WSDL';
	/* Update person */	
	public function updatePerson($contacto, $dataForm){
			$person = new Person();
			$app = new Application();
			$emails = $person->getPerson($contacto, 1);
			
			//$response['emails'] = json_encode($emails['Email']);
			//return $response;
			
			if(!empty($emails)){
				if(!empty($emails['Email'])){
					$soap = new Soap;
					$client = $soap->getClient($this->wsPerson);
					$soapaction = "http://xmlns.oracle.com/apps/cdm/foundation/parties/personService/applicationModule/mergePerson";
					$request = $this->updatePersonRequest($emails['Email'], $dataForm, $contacto);
					
					$savexml = new \Web\Logsrv();
					//$savexml->savelog($request,'Uperson');
					$savexml->savelog($emails['Email']['EmailAddress'],'Person-response');
					
					if(!empty($request)){
						$response['response'] = $client->send($request, $soapaction, '');
						$response['xml'] = base64_encode($request);
						return $response;
					}
				}
				
			}
			
	}

	public function updatePersonRequest($emails, $user, $contacto){
		$email1 = true;
        $email2 = true;
		$email3 = true;
        $arraEmail = array();
		//EmailAddress
        if( empty($emails['EmailAddress']) ){
        	$arraEmail[] = $emails;
		}else{
			$arraEmail[] = $emails['EmailAddress'];
		}

		$savexml = new \Web\Logsrv();
		$savexml->savelog($arraEmail,'Person-updatePersonRequest');
		
		foreach($arraEmail as $email){
				/*if(!empty($user->emailaddress)){
					if( $email['Status']=='A' and $email['PrimaryFlag']==true and (strtolower($user->emailaddress) == strtolower($email["EmailAddress"])) ){
						$email1 = false;
					}
				}else{
					$email1 = false;
				}*/
				if(!empty($user->emailaddress2)){
					if( $email['Status']=='A' and $email['PrimaryFlag']==false and 
						(strtolower($user->emailaddress2) == strtolower($email["EmailAddress"])) ){
						$email2 = false;
					}
				}else{
					$email2 = false;
				}
				if(!empty($user->persondeoctrcorreopucpc) ){
					if( $email['Status']=='A' and $email['PrimaryFlag']==false and (strtolower($user->persondeoctrcorreopucpc) == strtolower($email["EmailAddress"])) ){
						$email3 = false;
					}
				}else{
					$email3 = false;
				}
        }

        /*$request_xml_1 = '';
        if($email1==true){
			$request_xml_1 ='<per:Email>
					<con:OwnerTableName>HZ_PARTIES</con:OwnerTableName>
					<con:OwnerTableId>'.$contacto['PartyId'].'</con:OwnerTableId>
					<con:PrimaryFlag>true</con:PrimaryFlag>
					<con:ContactPointPurpose>PERSONAL</con:ContactPointPurpose>
					<con:EmailAddress>'.strtolower($user->emailaddress).'</con:EmailAddress>
					<con:PrimaryByPurpose>N</con:PrimaryByPurpose>
					<con:CreatedByModule>HZ_WS</con:CreatedByModule>
				</per:Email>';
		}*/
		
		$request_xml_2 = '';
		if($email==true){
			$request_xml_2 ='<per:Email>
					<con:OwnerTableName>HZ_PARTIES</con:OwnerTableName>
					<con:OwnerTableId>'.$contacto['PartyId'].'</con:OwnerTableId>
					<con:PrimaryFlag>false</con:PrimaryFlag>
					<con:ContactPointPurpose>BUSINESS</con:ContactPointPurpose>
					<con:EmailAddress>'.strtolower($user->emailaddress2).'</con:EmailAddress>
					<con:PrimaryByPurpose>N</con:PrimaryByPurpose>
					<con:CreatedByModule>HZ_WS</con:CreatedByModule>
				</per:Email>';
		}
		
		$request_xml_3 = '';
		if($email3==true){
			$request_xml_3 ='<per:Email>
					<con:OwnerTableName>HZ_PARTIES</con:OwnerTableName>
					<con:OwnerTableId>'.$contacto['PartyId'].'</con:OwnerTableId>
					<con:PrimaryFlag>false</con:PrimaryFlag>
					<con:ContactPointPurpose>PUCP</con:ContactPointPurpose>
					<con:EmailAddress>'.strtolower($user->persondeoctrcorreopucpc).'</con:EmailAddress>
					<con:PrimaryByPurpose>N</con:PrimaryByPurpose>
					<con:CreatedByModule>HZ_WS</con:CreatedByModule>
				</per:Email>';
		}

		//'.$request_xml_1.'

		if($email2 || $email3){
			$request_xml ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
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
						        '.$request_xml_2.'
								'.$request_xml_3.'
							</typ:personParty>
						</typ:mergePerson>
					</soapenv:Body>
				</soapenv:Envelope>';

				return $request_xml;

		}

		return '';
	}
	
}
?>