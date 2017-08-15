<?php 
namespace Web;
use Web\Soap;
class MergeContact{
	var $wsContacto = 'https://cang-test.crm.us2.oraclecloud.com:443/crmCommonSalesParties/ContactService?WSDL';
		/* Merge Contacto */
	public function mergeContacto($contacto, $dataForm, $try){
		$soap = new Soap();
		$client = $soap->getClient($this->wsContacto);
		$soapaction = "http://xmlns.oracle.com/apps/crmCommon/salesParties/contactService/mergeContact";
		$request = $this->mergeContactoRequest($contacto, $dataForm);
		
		if(!empty($response['faultstring'])){
			return $response;
		}
		
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
                    '.(isset($user->ctrciudadc)?'<per:CTRCiudad_c>'.$user->ctrciudadc.'</per:CTRCiudad_c>':'').'
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

}
?>