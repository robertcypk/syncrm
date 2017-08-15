<?php
namespace Web;
use Web\Soap;
//use Web\Person;
class Updatepersonemail{
    	public function updatePersonEmail($userInserted, $try, $wsPerson){
		$person = New \Web\Person;
		$emails = $person->getPerson($userInserted, 1);
		if(!empty($emails)){
			$soap = new Soap();
			$client = $soap->getClient($wsPerson);
			$soapaction = "http://xmlns.oracle.com/apps/cdm/foundation/parties/personService/applicationModule/updatePerson";
			$request = $this->updatePersonEmailRequest($userInserted, $emails);
			
			//$response['xml'] = $request;
			
			$response = $client->send($request, $soapaction, '');
			
			if(isset($response['result'])){
				return $response['result']['Value'];
			}else{
				$response['PrimaryEmailContactPTId'] = $emails["PrimaryEmailContactPTId"];
				$response['PartyId'] = $userInserted['PartyId'];
				return $response;
			}	
			$try += 1;
			if($try<3)
				return $this->updatePersonEmail($userInserted, $try, $wsPerson);
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
}
?>