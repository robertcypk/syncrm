<?php
namespace Web;
use Silex\Application;
use Web\Soap;
class GetTerritorios{
	var $wsTerritorio = 'https://cang-test.crm.us2.oraclecloud.com:443/salesTerrMgmtTerritories/TerritoryPublicService?WSDL';
    public function getTerritorios(){
		$soap = new Soap();
		
		$client = $soap->getClient($this->wsTerritorio);
		$today = date('Y-m-d',time());
		$soapaction = "http://xmlns.oracle.com/oracle/apps/sales/territoryMgmt/territories/territoryService/findTerritories";
		$request = $this->getTerritorioRequest($today);
		$response = $client->send($request, $soapaction, '');
		if(isset($response['result'])){
			if(isset($response['result']['Name'])){
				return array($response['result']);
			}else{
				return $response['result'];
			}
		}

	}

	public function getTerritorioRequest($today){
		$request_xml ='
			<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
     			xmlns:typ="http://xmlns.oracle.com/oracle/apps/sales/territoryMgmt/territories/territoryService/types/"
     			xmlns:typ1="http://xmlns.oracle.com/adf/svc/types/">
 	        	<soapenv:Header/>
 	            <soapenv:Body>
 	                <typ:findTerritories>
 	                	<typ:findCriteria>
 	                		<typ1:fetchStart>0</typ1:fetchStart>
 	                        <typ1:fetchSize>-1</typ1:fetchSize>
 	                        <typ1:filter>
 	                            <typ1:group>
 	                             	<typ1:conjunction>And</typ1:conjunction>
 	                             	<typ1:item>
 	                             		<typ1:conjunction>And</typ1:conjunction>
 										<typ1:upperCaseCompare>false</typ1:upperCaseCompare>
 										<typ1:attribute>ParentTerritoryId</typ1:attribute>
 										<typ1:operator>=</typ1:operator>
 										<typ1:value>300000007639214</typ1:value>
 									</typ1:item>
 									<typ1:item>
 	                             		<typ1:conjunction>And</typ1:conjunction>
 										<typ1:upperCaseCompare>false</typ1:upperCaseCompare>
 										<typ1:attribute>TerritoryLevel</typ1:attribute>
 										<typ1:operator>=</typ1:operator>
 										<typ1:value>2</typ1:value>
 									</typ1:item>
 									<typ1:item>
 										<typ1:conjunction>And</typ1:conjunction>
 										<typ1:upperCaseCompare>false</typ1:upperCaseCompare>
 										<typ1:attribute>StatusCode</typ1:attribute>
 										<typ1:operator>=</typ1:operator>
 										<typ1:value>FINALIZED</typ1:value>
 									</typ1:item>
 									<typ1:item>
 										<typ1:conjunction>And</typ1:conjunction>
 										<typ1:upperCaseCompare>false</typ1:upperCaseCompare>
 										<typ1:attribute>LatestVersionFlag</typ1:attribute>
 										<typ1:operator>=</typ1:operator>
 										<typ1:value>true</typ1:value>
 									</typ1:item>
                    				<typ1:item>
 										<typ1:conjunction>And</typ1:conjunction>
 										<typ1:upperCaseCompare>false</typ1:upperCaseCompare>
 										<typ1:attribute>EffectiveEndDate</typ1:attribute>
 										<typ1:operator>ONORAFTER</typ1:operator>
 										<typ1:value>'.$today.'</typ1:value>
 									</typ1:item>
 								</typ1:group>
 							</typ1:filter>
 							<typ1:findAttribute>Name</typ1:findAttribute>
 							<typ1:findAttribute>OwnerOrgId</typ1:findAttribute>
 							<typ1:findAttribute>TerritoryResource</typ1:findAttribute>
 	                        <typ1:childFindCriteria>
 	                            <typ1:findAttribute>ResourceId</typ1:findAttribute>
 	                            <typ1:findAttribute>ResourceName</typ1:findAttribute>
 	                            <typ1:findAttribute>CTRRuleta_c</typ1:findAttribute>
 	                            <typ1:findAttribute>CTROrdenRuleta_c</typ1:findAttribute>
 	                            <typ1:childAttrName>TerritoryResource</typ1:childAttrName>
 	                        </typ1:childFindCriteria>
 	                        <typ1:findAttribute>TerritoryRule</typ1:findAttribute>
 						    <typ1:childFindCriteria>
 						        <typ1:findAttribute>TerrRuleId</typ1:findAttribute>
 	                            <typ1:findAttribute>TerritoryRuleBoundary1</typ1:findAttribute>
 	                            <typ1:childFindCriteria>
 	                                <typ1:findAttribute>TerritoryRuleBndryValue1</typ1:findAttribute>
 	                                <typ1:childFindCriteria>
 	                                	<typ1:findAttribute>TerrDimIntgId</typ1:findAttribute>
 	                                	<typ1:childAttrName>TerritoryRuleBndryValue1</typ1:childAttrName>
 	                                </typ1:childFindCriteria>
 	                                <typ1:childAttrName>TerritoryRuleBoundary1</typ1:childAttrName>
 	                            </typ1:childFindCriteria>
 	                            <typ1:childAttrName>TerritoryRule</typ1:childAttrName>
 	                        </typ1:childFindCriteria>
 	                    </typ:findCriteria>
 	                    <typ:findControl>
 	                        <typ1:retrieveAllTranslations>false</typ1:retrieveAllTranslations>
 	                    </typ:findControl>
 	                </typ:findTerritories>
 	            </soapenv:Body>
 	        </soapenv:Envelope>';
 	        return $request_xml;
	}
}
?>