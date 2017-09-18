<?php
namespace Web;

use Silex\Application;
use Web\Soap;

class Createlead
{
    public $wsLead = 'https://cang-test.crm.us2.oraclecloud.com:443/mklLeads/SalesLeadService?WSDL';
    public function createLead($userInserted, $dataForm, $try)
    {
        $soap = new Soap();
        $client = $soap->getClient($this->wsLead);
        $soapaction = "http://xmlns.oracle.com/apps/marketing/leadMgmt/leads/leadService/createSalesLead";
        $request = $this->createLeadRequest($userInserted, $dataForm);
        file_put_contents('createlead.log', $request);
        $response = $client->send($request, $soapaction, '');
        
        if (!empty($response['faultstring'])) {
            $savexml = new \Web\Logsrv();
            $savexml->savelog($request, 'Createlead');

            return $response;
        }
            
        if (isset($response['result'])) {
            return $response['result'];
        }

        $try += 1;
        if ($try<3) {
            return $this->createLead($userInserted, $dataForm, $try);
        }
    }

    public function createLeadRequest($userInserted, $dataForm)
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
				      <typ:createSalesLead>
				        <typ:salesLead>
				            <lead:Name>'.$dataForm->firstname.' '.$dataForm->lastname.' - '.$dataForm->nombrepro.'</lead:Name>
				            <lead:CustomerId>'.$userInserted['PartyId'].'</lead:CustomerId>
				            <lead:OwnerId>'.$dataForm->atendido.'</lead:OwnerId>
				            '.$request_otroMedio.'
				            '.$request_charla.'
				            <lead:CTR_OrigenDelRegistro_c>'.$dataForm->ctrorigendelregistroc.'</lead:CTR_OrigenDelRegistro_c>
				            <lead:CTRProductoAsociado_Id_c>'.$dataForm->programa.'</lead:CTRProductoAsociado_Id_c>
				            <lead:PrimaryProductGroupId>'.$dataForm->programa.'</lead:PrimaryProductGroupId>
							'.(isset($dataForm->CTRUTMCampaignSource_c)?' <lead:CTRUTMCampaignSource_c>'.$dataForm->CTRUTMCampaignSource_c.'</lead:CTRUTMCampaignSource_c>':'').'
							'.(isset($dataForm->CTRUTMCampaignMedium_c)?' <lead:CTRUTMCampaignMedium_c>'.$dataForm->CTRUTMCampaignMedium_c.'</lead:CTRUTMCampaignMedium_c>':'').'
							'.(isset($dataForm->CTRUTMCampaignName_c)?' <lead:CTRUTMCampaignName_c>'.$dataForm->CTRUTMCampaignName_c.'</lead:CTRUTMCampaignName_c>':'').'
							'.(isset($dataForm->CTRUTMCampaignTerm_c)?' <lead:CTRUTMCampaignTerm_c>'.$dataForm->CTRUTMCampaignTerm_c.'</lead:CTRUTMCampaignTerm_c>':'').'
							'.(isset($dataForm->CTRUTMCampaignContent_c)?' <lead:CTRUTMCampaignContent_c>'.$dataForm->CTRUTMCampaignContent_c.'</lead:CTRUTMCampaignContent_c>':'').'
							'.(isset($dataForm->description)?' <lead:Description>'.$dataForm->description.'</lead:Description>':'').'
				        </typ:salesLead>
				      </typ:createSalesLead>
				   </soapenv:Body>
			</soapenv:Envelope>';
        return $request_xml;
    }
}
