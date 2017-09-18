<?php
namespace Web;

use Silex\Application;
use Web\Soap;

class Insertmergeperson
{
    public function insertmergeperson($userInserted, $dataForm, $try, $wsPerson)
    {
        $soap = new Soap();
        $client = $soap->getClient($wsPerson);
        $soapaction = "http://xmlns.oracle.com/apps/cdm/foundation/parties/personService/applicationModule/mergePerson";
        $request = $this->insertMergePersonRequest($userInserted, $dataForm);
        
        $savexml = new \Web\Logsrv();
        $savexml->savelog($request, 'Insertmergeperson');
        
        $response = $client->send($request, $soapaction, '');
        
        if (!empty($response['result'])) {
            return $response['result']['Value'];
        } else {
            return $response;
        }

        $try += 1;
        if ($try<3) {
            return $this->insertmergeperson($userInserted, $dataForm, $try, $wsPerson);
        }
    }

    public function insertMergePersonRequest($userInserted, $dataForm)
    {
        $email_pucp = '';
        if (!empty($dataForm->persondeoctrcorreopucpc)) {
            $email_pucp .= '<per:Email>
					               <con:OwnerTableName>HZ_PARTIES</con:OwnerTableName>
					               <con:OwnerTableId>'.$userInserted['PartyId'].'</con:OwnerTableId>
					               <con:PrimaryFlag>false</con:PrimaryFlag>
					               <con:ContactPointPurpose>PUCP</con:ContactPointPurpose>
					               <con:EmailAddress>'.$dataForm->persondeoctrcorreopucpc.'</con:EmailAddress>
					               <con:PrimaryByPurpose>N</con:PrimaryByPurpose>
					               <con:CreatedByModule>HZ_WS</con:CreatedByModule>
							    </per:Email>';
        }

        if (!empty($dataForm->emailaddress2)) {
            $email_pucp .= '<per:Email>
					               <con:OwnerTableName>HZ_PARTIES</con:OwnerTableName>
					               <con:OwnerTableId>'.$userInserted['PartyId'].'</con:OwnerTableId>
					               <con:PrimaryFlag>false</con:PrimaryFlag>
					               <con:ContactPointPurpose>BUSINESS</con:ContactPointPurpose>
					               <con:EmailAddress>'.$dataForm->emailaddress2.'</con:EmailAddress>
					               <con:PrimaryByPurpose>N</con:PrimaryByPurpose>
					               <con:CreatedByModule>HZ_WS</con:CreatedByModule>
							    </per:Email>';
        }
        
        /*if(  !empty($dataForm->emailaddress) ){
            $email_pucp .= '<per:Email>
                                   <con:OwnerTableName>HZ_PARTIES</con:OwnerTableName>
                                   <con:OwnerTableId>'.$userInserted['PartyId'].'</con:OwnerTableId>
                                   <con:PrimaryFlag>false</con:PrimaryFlag>
                                   <con:ContactPointPurpose>BUSINESS</con:ContactPointPurpose>
                                   <con:EmailAddress>'.$dataForm->emailaddress.'</con:EmailAddress>
                                   <con:PrimaryByPurpose>N</con:PrimaryByPurpose>
                                   <con:CreatedByModule>HZ_WS</con:CreatedByModule>
                                </per:Email>';
        }*/
        
        if (empty($email_pucp)) {
            return '';
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
}
