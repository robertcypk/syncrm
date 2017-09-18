<?php
namespace Web;

use Silex\Application;
use Web\Soap;
use Web\Emailuser;

class Checkcontact
{
    public $wsContacto = 'https://cang-test.crm.us2.oraclecloud.com:443/crmCommonSalesParties/ContactService?WSDL';
    public function checkContactf($dataForm, $try, $usuario, $app)
    {
        $soap = new Soap();
        $client = $soap->getClient($this->wsContacto);
        //Persondeo_ctrnrodedocumento_c
        $soapaction = "http://xmlns.oracle.com/apps/crmCommon/salesParties/contactService/findContact";
        if (isset($dataForm->persondeoctrnrodedocumentoc)) {
            $request = $this->getContactRequest($dataForm);
            
            $savexml = new \Web\Logsrv();
            $savexml->savelog($request, 'checkContact-request');

            $response = $client->send($request, $soapaction, '');
            
            if (!empty($response['faultstring'])) {
                return $response['faultstring'];
            }
            
            if (isset($response['result'])) {
                if (isset($response['result']['Value'])) {
                    if (isset($response['result']['Value']['PartyId'])) {
                        $savexml = new \Web\Logsrv();
                        $savexml->savelog($response['result'], 'checkContact');

                        return $response['result']['Value'];
                    } else {
                        //DB::table('usuario_crm')->where('id','=',$usuario)->update(array('dupli'=>'1'));
                        $qb = $app['orm.em']->createQueryBuilder();
                        $q = $qb->update('Entity\Usuario_crm', 'u')
                                ->set('u.dupli', $qb->expr()->literal(1))
                                ->where('u.id = ?1')
                                ->setParameter(1, $usuario['id'])
                                ->getQuery();
                        $p = $q->execute();
                        
                        //$logger = new Emailuser();
                        //$log = $logger->logger('checkContact', json_encode($response['result']['Value']), $app);
                        

                        return $response['result']['Value'][count($response['result']['Value'])-1];
                    }
                } else {
                    return false;
                }
            }
            $try += 1;
            if ($try<3) {
                return $this->checkContactf($dataForm, $try, $usuario, $app, $wsContacto);
            }
        }
    }
    public function getContactRequest($dataForm)
    {
        //Persondeo_ctrnrodedocumento_c
        $request_xml ='
			<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
				xmlns:typ="http://xmlns.oracle.com/apps/crmCommon/salesParties/contactService/types/"
				xmlns:typ1="http://xmlns.oracle.com/adf/svc/types/">
				<soapenv:Header/>
				<soapenv:Body>
					<typ:findContact>
						<typ:findCriteria>
							<typ1:fetchStart>0</typ1:fetchStart>
						    <typ1:fetchSize>-1</typ1:fetchSize>
						    <typ1:filter>
						    	<typ1:group>
						        	<typ1:conjunction>And</typ1:conjunction>
						            <typ1:item>
		                             	<typ1:conjunction>And</typ1:conjunction>
										<typ1:upperCaseCompare>false</typ1:upperCaseCompare>
										<typ1:attribute>PersonDEO_CTRNrodedocumento_c</typ1:attribute>
										<typ1:operator>=</typ1:operator>
										<typ1:value>'.$dataForm->persondeoctrnrodedocumentoc.'</typ1:value>
									</typ1:item>
						        </typ1:group>
						    </typ1:filter>
						    <typ1:findAttribute>PartyId</typ1:findAttribute>
						    <typ1:findAttribute>PrimaryAddress</typ1:findAttribute>

						</typ:findCriteria>
						<typ:findControl>
							<typ1:retrieveAllTranslations>false</typ1:retrieveAllTranslations>
						</typ:findControl>
					</typ:findContact>
				</soapenv:Body>
			</soapenv:Envelope>';
        return $request_xml;
    }
}
