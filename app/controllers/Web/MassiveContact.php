<?php
namespace Web;

use Web\Soap;
use Web\Emailuser;
use Silex\Application;

class MassiveContact
{
    public $wsContacto="https://cang-test.crm.us2.oraclecloud.com:443/crmCommonSalesParties/ContactService?WSDL";
    /*
    * Update Contact Masive
    */

    public function updateContactMassive($contacto, $dataForm, $try)
    {
        $soap = new Soap();
        $client = $soap->getClient($this->wsContacto);
        $soapaction = "http://xmlns.oracle.com/apps/crmCommon/salesParties/contactService/updateContact";
        
        $request = $this->updateContactRequest($contacto, $dataForm);
        
        $savexml = new \Web\Logsrv();
        $savexml->savelog($request, 'updateContactMassive');

        $response = $client->send($request, $soapaction, '');
        

        if (!empty($response['result'])) {
            return $response;
        }
        $try += 1;
        if ($try<3) {
            return $this->updateContactMassive($contacto, $dataForm, $try);
        }
    }

    public function updateContactRequest($contacto, $dataForm)
    {
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
                            '.(isset($dataForm->salutoryintroduction)?'<ns1:SalutoryIntroduction>'.$dataForm->salutoryintroduction.'</ns1:SalutoryIntroduction>':'').'
                            '.(isset($dataForm->firstname)?'<ns1:FirstName>'.$dataForm->firstname.'</ns1:FirstName>':'').'
                            '.(isset($dataForm->lastname)?'<ns1:LastName>'.$dataForm->lastname.'</ns1:LastName>':'').'
                            '.(isset($dataForm->persondeoctrapellidomaternoc)?'<ns1:PersonDEO_CTRApellidoMaterno_c>'.$dataForm->persondeoctrapellidomaternoc.'</ns1:PersonDEO_CTRApellidoMaterno_c>':'').'
                            <ns1:PersonDEO_CTRTipodedocumento_c>'.$dataForm->persondeoctrtipodedocumentoc.'</ns1:PersonDEO_CTRTipodedocumento_c>
                            <ns1:PersonDEO_CTRNrodedocumento_c>'.$dataForm->persondeoctrnrodedocumentoc.'</ns1:PersonDEO_CTRNrodedocumento_c>
                            '.(isset($dataForm->homephonenumber)?'<ns1:HomePhoneNumber>'.$dataForm->homephonenumber.'</ns1:HomePhoneNumber>':'').'
                            '.(isset($dataForm->mobilenumber)?'<ns1:MobileNumber>'.$dataForm->mobilenumber.'</ns1:MobileNumber>':'').'
							'.(isset($dataForm->persondeoctrsituacionactualc)?'<ns1:PersonDEO_CTRSituacionActual_c>'.$dataForm->persondeoctrsituacionactualc.'</ns1:PersonDEO_CTRSituacionActual_c>':'');

        /*if(isset($user->addresselementattribute2))
                $user->city = $dataForm->addresselementattribute2;
        if(isset($user->country)){
            if($user->country=='PE'){
                $user->city = '';
            }else{
                $user->addresselementattribute2 = '';
            }
        }*/
        
        if ((isset($dataForm->addresselementattribute2) || isset($dataForm->addresselementattribute3)
            || isset($dataForm->addressline1) || isset($dataForm->country) || isset($dataForm->city))
            && isset($contacto["PrimaryAddress"])) {
            if ($dataForm->country !='PE') {
                //OTROS PAISES
                $request_xml .= '
                            <ns1:PrimaryAddress>
							    '.(isset($dataForm->addresselementattribute3)?'<ns2:City>'.$dataForm->addresselementattribute3.'</ns2:City>':'').'
							    '.(isset($dataForm->addresselementattribute2)?'<ns2:AddressLine2>'.$dataForm->addresselementattribute2.'</ns2:AddressLine2>':'').'
                                '.(isset($dataForm->addressline1)?'<ns2:AddressLine1>'.$dataForm->addressline1.'</ns2:AddressLine1>':'').'
                                '.(isset($dataForm->country)?'<ns2:Country>'.$dataForm->country.'</ns2:Country>':'').'
                                '.(isset($dataForm->city)?'<ns2:City>'.$dataForm->city.'</ns2:City>':'').'
                            </ns1:PrimaryAddress>';
            } else {
                //SOLO PERU
                $request_xml .= '
                            <ns1:PrimaryAddress>
                                '.(isset($dataForm->addresselementattribute2)?'<ns2:AddressElementAttribute2>'.$dataForm->addresselementattribute2.'</ns2:AddressElementAttribute2>':'').'
                                '.(isset($dataForm->addresselementattribute3)?'<ns2:AddressElementAttribute3>'.$dataForm->addresselementattribute3.'</ns2:AddressElementAttribute3>':'').'
                                '.(isset($dataForm->addressline1)?'<ns2:AddressLine1>'.$dataForm->addressline1.'</ns2:AddressLine1>':'').'
                                '.(isset($dataForm->country)?'<ns2:Country>'.$dataForm->country.'</ns2:Country>':'').'
                            </ns1:PrimaryAddress>';
            }
        }

        if (!empty($user->persondeoctrcompaniacempresa) and  empty($user->persondeoctrcompaniacruc)) {
            $user->persondeoctrcompaniac = $user->persondeoctrcompaniacempresa;
        } else if (empty($user->persondeoctrcompaniacempresa) and !empty($user->persondeoctrcompaniacruc)) {
            $user->persondeoctrcompaniac = $user->persondeoctrcompaniacruc;
        } else if (!empty($user->persondeoctrcompaniacruc) and !empty($user->persondeoctrcompaniacempresa)) {
            $user->persondeoctrcompaniac = $user->persondeoctrcompaniacempresa.'/'.$user->persondeoctrcompaniacruc;
        } else {
            $user->persondeoctrcompaniac = '';
        }

        $request_xml .= '<ns1:DateOfBirth>'.$dataForm->dateofbirth.'</ns1:DateOfBirth>
                            '.(isset($dataForm->persondeoctrpaisdenacimientoc)?'<ns1:PersonDEO_CTRPaisdenacimiento_c>'.$dataForm->persondeoctrpaisdenacimientoc.'</ns1:PersonDEO_CTRPaisdenacimiento_c>':'').'
                            '.(isset($dataForm->persondeoctrciudaddenacimientoc)?'<ns1:PersonDEO_CTRCiudaddeNacimiento_c>'.$dataForm->persondeoctrciudaddenacimientoc.'</ns1:PersonDEO_CTRCiudaddeNacimiento_c>':'').'
                            '.(isset($dataForm->persondeoctrnacionalidadc)?'<ns1:PersonDEO_ctrnacionalidad_c>'.$dataForm->persondeoctrnacionalidadc.'</ns1:PersonDEO_ctrnacionalidad_c>':'').'
                            '.(isset($dataForm->emailaddress)?'<ns1:EmailAddress>'.strtolower($dataForm->emailaddress).'</ns1:EmailAddress>':'').'
							'.(isset($dataForm->persondeoctrcuentaskypec)?'<ns1:PersonDEO_CTRCuentaSkype_c>'.$dataForm->persondeoctrcuentaskypec.'</ns1:PersonDEO_CTRCuentaSkype_c>':'').'
                            '.(isset($dataForm->persondeoctrtipoalumnoc)?'<ns1:PersonDEO_CTRTipoAlumno_c>'.$dataForm->persondeoctrtipoalumnoc.'</ns1:PersonDEO_CTRTipoAlumno_c>':'').'
                            '.(isset($dataForm->persondeoctrcodigopucpc)?' <ns1:PersonDEO_CTRCodigoPUCP_c>'.$dataForm->persondeoctrcodigopucpc.'</ns1:PersonDEO_CTRCodigoPUCP_c>':'').'
                            '.(isset($dataForm->persondeoctrdondelaborasc)?'<ns1:PersonDEO_CTRDondeLaboras_c>'.$dataForm->persondeoctrdondelaborasc.'</ns1:PersonDEO_CTRDondeLaboras_c>':'').'
                            '.(isset($dataForm->persondeoctrestudiospreviosc)?'<ns3:PersonDEO_CTREstudiosPrevios_c>'.$dataForm->persondeoctrestudiospreviosc.'</ns3:PersonDEO_CTREstudiosPrevios_c>':'').'
							'.(isset($dataForm->persondeoctrprogramaqueestudioc)?'<ns3:PersonDEO_CTRProgramaQueEstudio_c>'.$dataForm->persondeoctrprogramaqueestudioc.'</ns3:PersonDEO_CTRProgramaQueEstudio_c>':'').'
							'.(isset($dataForm->persondeoctrcompaniac)?'<ns1:PersonDEO_CTRCompania_c>'.$dataForm->persondeoctrcompaniac.'</ns1:PersonDEO_CTRCompania_c>':'').'
                            '.(isset($dataForm->jobtitle)?'<ns1:JobTitle>'.$dataForm->jobtitle.'</ns1:JobTitle>':'').'
                            '.(isset($dataForm->workphonenumber)?'<ns1:WorkPhoneNumber>'.$dataForm->workphonenumber.'</ns1:WorkPhoneNumber>':'').'
                            '.(isset($dataForm->workphoneextension)?'<ns1:WorkPhoneExtension>'.$dataForm->workphoneextension.'</ns1:WorkPhoneExtension>':'').'
                            '.(isset($dataForm->faxnumber)?'<ns1:FaxNumber>'.$dataForm->faxnumber.'</ns1:FaxNumber>':'').'
                            '.(isset($dataForm->persondeoctraniosdeexperienciac)?'<ns1:PersonDEO_CTRAniosdeexperiencia_c>'.$dataForm->persondeoctraniosdeexperienciac.'</ns1:PersonDEO_CTRAniosdeexperiencia_c>':'').'
                            '.(isset($dataForm->persondeoctrobservacionc)?' <ns1:PersonDEO_CTRObservacion_c>'.$dataForm->persondeoctrobservacionc.'</ns1:PersonDEO_CTRObservacion_c>':'').'
                            '.(isset($dataForm->persondeoctrprocedenciac)?' <ns1:PersonDEO_CTRProcedencia_c>'.$dataForm->persondeoctrprocedenciac.'</ns1:PersonDEO_CTRProcedencia_c>':'').'';
        
        if (isset($dataForm->persondeoctrautorizodatospersonfinesmc)) {
            $request_xml .= '<ns1:PersonDEO_CTRAutorizoDatosPersonFinesM_c>t</ns1:PersonDEO_CTRAutorizoDatosPersonFinesM_c>';
        } else {
            $request_xml .= '<ns1:PersonDEO_CTRAutorizoDatosPersonFinesM_c>false</ns1:PersonDEO_CTRAutorizoDatosPersonFinesM_c>';
        }
        
        if (isset($dataForm->persondeoctrautorizoenvinfprogacac)) {
            $request_xml .= '<ns1:PersonDEO_CTRAutorizoEnvInfProgAca_c>t</ns1:PersonDEO_CTRAutorizoEnvInfProgAca_c>';
        } else {
            $request_xml .= '<ns1:PersonDEO_CTRAutorizoEnvInfProgAca_c>false</ns1:PersonDEO_CTRAutorizoEnvInfProgAca_c>';
        }
        
        $request_xml .= (isset($dataForm->persondeoctrsalariomedioanualc)?' <ns1:PersonDEO_CTRSalarioMedioAnual_c>'.$dataForm->persondeoctrsalariomedioanualc.'</ns1:PersonDEO_CTRSalarioMedioAnual_c>':'').'
                            '.(isset($dataForm->currencycode)?' <ns1:CurrencyCode>'.$dataForm->currencycode.'</ns1:CurrencyCode>':'').'
                        </ns4:contact>
                    </ns4:updateContact>
                </soapenv:Body>
            </soapenv:Envelope>';
            
        return $request_xml;
    }
}
