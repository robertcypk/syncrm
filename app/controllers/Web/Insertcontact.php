<?php
namespace Web;

use Silex\Application;
use Web\Soap;
use Web\Emailuser;

class Insertcontact
{
    public function insertContact($dataForm, $try, $wsContacto)
    {
        $soap = new Soap();
        $client = $soap->getClient($wsContacto);
        $soapaction = "http://xmlns.oracle.com/apps/crmCommon/salesParties/contactService/createContact";
        $request = $this->insertContactRequest($dataForm);
        
        $response = $client->send($request, $soapaction, '');
        
        //$response['xml'] = base64_encode($request);
        $savexml = new \Web\Logsrv();
        $savexml->savelog($request, 'Insertcontact');
        
        if (!empty($response['faultstring'])) {
            return $response;
        }
        
        if (isset($response["result"])) {
            return $response["result"]["Value"];
        } else {
            return '--'.$client->getError().'--';
        }

        $try += 1;
        if ($try<3) {
            return $this->insertContact($dataForm, $try, $wsContacto);
        }
    }

    public function insertContactRequest($user)
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
  					<ns4:createContact>
  						<ns4:contact>
  							'.(isset($user->salutoryintroduction)?'<ns1:SalutoryIntroduction>'.$user->salutoryintroduction.'</ns1:SalutoryIntroduction>':'').'
  							'.(isset($user->firstname)?'<ns1:FirstName>'.$user->firstname.'</ns1:FirstName>':'').'
					        '.(isset($user->lastname)?'<ns1:LastName>'.$user->lastname.'</ns1:LastName>':'').'
							'.(isset($user->persondeoctrapellidomaternoc)?'<ns1:PersonDEO_CTRApellidoMaterno_c>'.$user->persondeoctrapellidomaternoc.'</ns1:PersonDEO_CTRApellidoMaterno_c>':'').'
					        <ns1:PersonDEO_CTRTipodedocumento_c>'.$user->persondeoctrtipodedocumentoc.'</ns1:PersonDEO_CTRTipodedocumento_c>
						    <ns1:PersonDEO_CTRNrodedocumento_c>'.$user->persondeoctrnrodedocumentoc.'</ns1:PersonDEO_CTRNrodedocumento_c>
						    '.(isset($user->homephonenumber)?'<ns1:HomePhoneNumber>'.$user->homephonenumber.'</ns1:HomePhoneNumber>':'').'
						    '.(isset($user->mobilenumber)?'<ns1:MobileNumber>'.$user->mobilenumber.'</ns1:MobileNumber>':'').'
							'.(isset($user->persondeoctrsituacionactualc)?'<ns1:PersonDEO_CTRSituacionActual_c>'.$user->persondeoctrsituacionactualc.'</ns1:PersonDEO_CTRSituacionActual_c>':'');

        if (isset($user->addresselementattribute2) || isset($user->addresselementattribute3)
            || isset($user->addressline1) || isset($user->country) || isset($user->city)) {
            if ($user->country !='PE') {
                $request_xml .= '
                            <ns1:PrimaryAddress>
                                '.(isset($user->addresselementattribute3)?'<ns2:City>'.$user->addresselementattribute3.'</ns2:City>':'').'
                                '.(isset($user->addresselementattribute2)?'<ns2:AddressLine2>'.$user->addresselementattribute2.'</ns2:AddressLine2>':'').'
                                '.(isset($user->addressline1)?'<ns2:AddressLine1>'.$user->addressline1.'</ns2:AddressLine1>':'').'
                                '.(isset($user->country)?'<ns2:Country>'.$user->country.'</ns2:Country>':'').'
                                '.(isset($user->city)?'<ns2:City>'.$user->city.'</ns2:City>':'').'
                            </ns1:PrimaryAddress>';
            } else {
                $request_xml .= '
                            <ns1:PrimaryAddress>
                                '.(isset($user->addresselementattribute2)?'<ns2:AddressElementAttribute2>'.$user->addresselementattribute2.'</ns2:AddressElementAttribute2>':'').'
                                '.(isset($user->addresselementattribute3)?'<ns2:AddressElementAttribute3>'.$user->addresselementattribute3.'</ns2:AddressElementAttribute3>':'').'
                                '.(isset($user->addressline1)?'<ns2:AddressLine1>'.$user->addressline1.'</ns2:AddressLine1>':'').'
                                '.(isset($user->country)?'<ns2:Country>'.$user->country.'</ns2:Country>':'').'
                            </ns1:PrimaryAddress>';
            }
        }
        
        $request_xml .= '
					    	<ns1:DateOfBirth>'.$user->dateofbirth.'</ns1:DateOfBirth>
					        '.(isset($user->persondeoctrpaisdenacimientoc)?'<ns1:PersonDEO_CTRPaisdenacimiento_c>'.$user->persondeoctrpaisdenacimientoc.'</ns1:PersonDEO_CTRPaisdenacimiento_c>':'').'
						    '.(isset($user->persondeoctrciudaddenacimientoc)?'<ns1:PersonDEO_CTRCiudaddeNacimiento_c>'.$user->persondeoctrciudaddenacimientoc.'</ns1:PersonDEO_CTRCiudaddeNacimiento_c>':'').'
						    '.(isset($user->persondeoctrnacionalidadc)?'<ns1:PersonDEO_CTRNacionalidad_c>'.$user->persondeoctrnacionalidadc.'</ns1:PersonDEO_CTRNacionalidad_c>':'').'
						    '.(isset($user->emailaddress)?'<ns1:EmailAddress>'.strtolower($user->emailaddress).'</ns1:EmailAddress>':'').'
						    '.(isset($user->persondeoctrcorreopucpc)?'<ns1:PersonDEO_CTRCorreoPUCP_c>'.strtolower($user->persondeoctrcorreopucpc).'</ns1:PersonDEO_CTRCorreoPUCP_c>':'').'
						    '.(isset($user->persondeoctrcuentaskypec)?'<ns1:PersonDEO_CTRCuentaSkype_c>'.$user->persondeoctrcuentaskypec.'</ns1:PersonDEO_CTRCuentaSkype_c>':'');

        if (isset($user->ctrgradoacademicoc) || isset($user->ctrinstitucionacademicac)
            || isset($user->ctrotrasuniversidadesinstc) || isset($user->ctrespecialidadc)
            || isset($user->ctranomesquefinalizoestsupc) || isset($user->ctrnivelacademicoc)) {
            $request_xml .= '
			 				<ns1:PersonDEO_InformacionAcademicaCollection_c>
			 					'.(isset($user->ctrgradoacademicoc)?'<ns3:CTRGradoacademico_c>'.$user->ctrgradoacademicoc.'</ns3:CTRGradoacademico_c>':'').'
			 					'.(isset($user->ctrinstitucionacademicac)?'<ns3:CTRInstitucionAcademica_c>'.$user->ctrinstitucionacademicac.'</ns3:CTRInstitucionAcademica_c>':'').'
			 					'.(isset($user->ctrotrasuniversidadesinstc)?'<ns3:CTROtrasUniversidadesInst_c>'.$user->ctrotrasuniversidadesinstc.'</ns3:CTROtrasUniversidadesInst_c>':'').'
			 					'.(isset($user->ctrespecialidadc)?'<ns3:CTREspecialidad_c>'.$user->ctrespecialidadc.'</ns3:CTREspecialidad_c>':'').'
			 					'.(isset($user->ctranomesquefinalizoestsupc)?'<ns3:CTRAnoMesquefinalizoEstSup_c>'.$user->ctranomesquefinalizoestsupc.'</ns3:CTRAnoMesquefinalizoEstSup_c>':'').'
			 					'.(isset($user->ctrnivelacademicoc)?'<ns3:CTRNivelacademico_c>'.$user->ctrnivelacademicoc.'</ns3:CTRNivelacademico_c>':'').'
								'.(isset($user->persondeoctrestudiospreviosc)?'<ns3:PersonDEO_CTREstudiosPrevios_c>'.$user->persondeoctrestudiospreviosc.'</ns3:PersonDEO_CTREstudiosPrevios_c>':'').'
								'.(isset($user->persondeoctrprogramaqueestudioc)?'<ns3:PersonDEO_CTRProgramaQueEstudio_c>'.$user->persondeoctrprogramaqueestudioc.'</ns3:PersonDEO_CTRProgramaQueEstudio_c>':'').'
			 				</ns1:PersonDEO_InformacionAcademicaCollection_c>';
        }

        $request_xml .= '
                            '.(isset($user->persondeoctrtipoalumnoc)?'<ns1:PersonDEO_CTRTipoAlumno_c>'.$user->persondeoctrtipoalumnoc.'</ns1:PersonDEO_CTRTipoAlumno_c>':'').'
						    '.(isset($user->persondeoctrcodigopucpc)?' <ns1:PersonDEO_CTRCodigoPUCP_c>'.$user->persondeoctrcodigopucpc.'</ns1:PersonDEO_CTRCodigoPUCP_c>':'');

        if (isset($user->ctrinstituciondeidiomasc) || isset($user->ctrnivelalcanzadoc)
            || isset($user->ctraniomesfinalizoidiomac) || isset($user->ctrciudadc)) {
            $request_xml .= '
							<ns1:PersonDEO_IdiomaCollection_c>
								'.(isset($user->ctrinstituciondeidiomasc)?'<ns3:CTRInstituciondeidiomas_c>'.$user->ctrinstituciondeidiomasc.'</ns3:CTRInstituciondeidiomas_c>':'').'
								'.(isset($user->ctrnivelalcanzadoc)?'<ns3:CTRNivelalcanzado_c>'.$user->ctrnivelalcanzadoc.'</ns3:CTRNivelalcanzado_c>':'').'
								'.(isset($user->ctraniomesfinalizoidiomac)?'<ns3:CTRAnioMesfinalizoIdioma_c>'.$user->ctraniomesfinalizoidiomac.'</ns3:CTRAnioMesfinalizoIdioma_c>':'').'
								'.(isset($user->ctrciudadc)?'<ns3:CTRCiudad_c>'.$user->ctrciudadc.'</ns3:CTRCiudad_c>':'').'
							</ns1:PersonDEO_IdiomaCollection_c>';
        }

        if (!empty($user->persondeoctrcompaniacempresa) and  empty($user->persondeoctrcompaniacruc)) {
            $user->persondeoctrcompaniac = $user->persondeoctrcompaniacempresa;
        } else if (empty($user->persondeoctrcompaniacempresa) and !empty($user->persondeoctrcompaniacruc)) {
            $user->persondeoctrcompaniac = $user->persondeoctrcompaniacruc;
        } else if (!empty($user->persondeoctrcompaniacruc) and !empty($user->persondeoctrcompaniacempresa)) {
            $user->persondeoctrcompaniac = $user->persondeoctrcompaniacempresa.'-'.$user->persondeoctrcompaniacruc;
        } else {
            $user->persondeoctrcompaniac = '';
        }

        $request_xml .= '
							'.(isset($user->persondeoctrdondelaborasc)?'<ns1:PersonDEO_CTRDondeLaboras_c>'.$user->persondeoctrdondelaborasc.'</ns1:PersonDEO_CTRDondeLaboras_c>':'').'
						    '.(isset($user->persondeoctrcompaniac)?'<ns1:PersonDEO_CTRCompania_c>'.$user->persondeoctrcompaniac.'</ns1:PersonDEO_CTRCompania_c>':'').'
						    '.(isset($user->jobtitle)?'<ns1:JobTitle>'.$user->jobtitle.'</ns1:JobTitle>':'').'
						    '.(isset($user->workphonenumber)?'<ns1:WorkPhoneNumber>'.$user->workphonenumber.'</ns1:WorkPhoneNumber>':'').'
						    '.(isset($user->workphoneextension)?'<ns1:WorkPhoneExtension>'.$user->workphoneextension.'</ns1:WorkPhoneExtension>':'').'
						    '.(isset($user->faxnumber)?'<ns1:FaxNumber>'.$user->faxnumber.'</ns1:FaxNumber>':'').'
						    '.(isset($user->persondeoctraniosdeexperienciac)?'<ns1:PersonDEO_CTRAniosdeexperiencia_c>'.$user->persondeoctraniosdeexperienciac.'</ns1:PersonDEO_CTRAniosdeexperiencia_c>':'').'
						    '.(isset($user->persondeoctrobservacionc)?' <ns1:PersonDEO_CTRObservacion_c>'.$user->persondeoctrobservacionc.'</ns1:PersonDEO_CTRObservacion_c>':'').'
						    '.(isset($user->persondeoctrprocedenciac)?' <ns1:PersonDEO_CTRProcedencia_c>'.$user->persondeoctrprocedenciac.'</ns1:PersonDEO_CTRProcedencia_c>':'').'
						    '.(isset($user->persondeoctrautorizodatospersonfinesmc)?' <ns1:PersonDEO_CTRAutorizoDatosPersonFinesM_c>'.strtolower($user->persondeoctrautorizodatospersonfinesmc).'</ns1:PersonDEO_CTRAutorizoDatosPersonFinesM_c>':'').'
						    '.(isset($user->persondeoctrautorizoenvinfprogacac)?' <ns1:PersonDEO_CTRAutorizoEnvInfProgAca_c>'.strtolower($user->persondeoctrautorizoenvinfprogacac).'</ns1:PersonDEO_CTRAutorizoEnvInfProgAca_c>':'').'
						    '.(isset($user->persondeoctrsalariomedioanualc)?' <ns1:PersonDEO_CTRSalarioMedioAnual_c>'.$user->persondeoctrsalariomedioanualc.'</ns1:PersonDEO_CTRSalarioMedioAnual_c>':'').'
						    '.(isset($user->currencycode)?' <ns1:CurrencyCode>'.$user->currencycode.'</ns1:CurrencyCode>':'').'
						    <ns1:OwnerPartyId>'.$user->ownerpartyid.'</ns1:OwnerPartyId>
						    <ns1:Type>'.$user->type.'</ns1:Type>
						</ns4:contact>
					</ns4:createContact>
				</soapenv:Body>
			</soapenv:Envelope>';
            
        return $request_xml;
    }
}
