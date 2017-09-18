<?php
namespace Web;

use Silex\Application;
use Web\Soap;

class Updatelead
{
    public $wsLead = 'https://cang-test.crm.us2.oraclecloud.com:443/mklLeads/SalesLeadService?WSDL';
    public function updateLead($anterior, $dataForm, $try)
    {
        $soap = new Soap();
        $client = $soap->getClient($this->wsLead);
        $soapaction = "http://xmlns.oracle.com/apps/marketing/leadMgmt/leads/leadService/updateSalesLead";
        $request = $this->updateLeadRequest($anterior, $dataForm);
        
        $savexml = new \Web\Logsrv();
        $savexml->savelog($request, 'Updatelead');
        
        $response = $client->send($request, $soapaction, '');
        


        if (!empty($response['faultstring'])) {
            return $response;
        }
        
        
        if (isset($response['result'])) {
            return $response['result'];
        }

        $try += 1;
        if ($try<3) {
            return $this->updateLead($anterior, $dataForm, $try);
        }
    }

    public function updateLeadRequest($anterior, $dataForm)
    {
        $request_otroMedio = '';
        if (isset($dataForm->persondeoctrprocedenciac)) {
            if (!isset($dataForm->otromedio)) {
                $dataForm->otromedio = '';
            }
            if ($dataForm->persondeoctrprocedenciac==8) {
                $request_otroMedio = '<lead:CTROtroMedio_c>'.$dataForm->otromedio.'</lead:CTROtroMedio_c>';
            }
        }
        $request_charla = '';
        if (isset($dataForm->ctrfechacharlac)) {
            $request_charla = '<lead:CTRFechaCharla_c>'.$dataForm->ctrfechacharlac.'</lead:CTRFechaCharla_c>';
        }

        $request_xml = '
			<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
				xmlns:typ="http://xmlns.oracle.com/apps/marketing/leadMgmt/leads/leadService/types/"
				xmlns:lead="http://xmlns.oracle.com/oracle/apps/marketing/leadMgmt/leads/leadService/"
				xmlns:lead1="http://xmlns.oracle.com/apps/marketing/leadMgmt/leads/leadService/"
				xmlns:not="http://xmlns.oracle.com/apps/crmCommon/notes/noteService"
				xmlns:not1="http://xmlns.oracle.com/apps/crmCommon/notes/flex/noteDff/">
				<soapenv:Header/>
				<soapenv:Body>
					<typ:updateSalesLead>
						<typ:salesLead>
					    	<lead:LeadId>'.$anterior['LeadId'].'</lead:LeadId>
					        <lead:CTR_OrigenDelRegistro_c>'.$dataForm->ctrorigendelregistroc.'</lead:CTR_OrigenDelRegistro_c>
					        <lead:Name>'.$dataForm->firstname.' '.$dataForm->lastname.' - '.$dataForm->nombrepro.'</lead:Name>
                            '.$request_otroMedio.'
                            '.$request_charla.'
							'.(isset($dataForm->CTRUTMCampaignSource_c)?' <lead:CTRUTMCampaignSource_c>'.$dataForm->CTRUTMCampaignSource_c.'</lead:CTRUTMCampaignSource_c>':'').'
							'.(isset($dataForm->CTRUTMCampaignMedium_c)?' <lead:CTRUTMCampaignMedium_c>'.$dataForm->CTRUTMCampaignMedium_c.'</lead:CTRUTMCampaignMedium_c>':'').'
							'.(isset($dataForm->CTRUTMCampaignName_c)?' <lead:CTRUTMCampaignName_c>'.$dataForm->CTRUTMCampaignName_c.'</lead:CTRUTMCampaignName_c>':'').'
							'.(isset($dataForm->CTRUTMCampaignTerm_c)?' <lead:CTRUTMCampaignTerm_c>'.$dataForm->CTRUTMCampaignTerm_c.'</lead:CTRUTMCampaignTerm_c>':'').'
							'.(isset($dataForm->CTRUTMCampaignContent_c)?' <lead:CTRUTMCampaignContent_c>'.$dataForm->CTRUTMCampaignContent_c.'</lead:CTRUTMCampaignContent_c>':'').'
							'.(isset($dataForm->description)?' <lead:Description>'.$dataForm->description.'</lead:Description>':'').'
						</typ:salesLead>
					</typ:updateSalesLead>
				</soapenv:Body>
			</soapenv:Envelope>';
            
        //var_dump( $request_xml );
        return $request_xml;
    }
}
