<?php
namespace Web;
use Silex\Application;
use Web\Soap;
class GetCatalogos{
	var $wsCatalago = 'https://cang-test.crm.us2.oraclecloud.com:443/ocSalesCatalogBrowse/SalesCatalogRuntimeService?WSDL';
	public function getCatalogos($territorios){
		$soap = new Soap();
		$client = $soap->getClient($this->wsCatalago);
		$soapaction = "http://xmlns.oracle.com/apps/orderCapture/salesCatalog/browse/salesCatalogService/getCatalogCategories";
		$arrayFinal = array();
		foreach($territorios as $territorio){
			if(isset($territorio["TerritoryRule"]["TerritoryRuleBoundary1"]["TerritoryRuleBndryValue1"]["TerrDimIntgId"])){
				$TerrDimIntgId = $territorio["TerritoryRule"]["TerritoryRuleBoundary1"]["TerritoryRuleBndryValue1"]["TerrDimIntgId"];
				$request = $this->getCatalogoRequest($TerrDimIntgId);
				$response = $client->send($request, $soapaction, '');
				if(isset($response["result"])){
					$catalogos = $response["result"];
					if(isset($catalogos["ProdGrpDetailsId"])){
						if($catalogos["ProductGroupDescription"]=="hoja"){
							$catalogos["TerritoryResource"] = $territorio["TerritoryResource"];
							$arrayFinal[] = $catalogos;
						}
					}else{
						foreach($catalogos as $catalogo){
				    		if($catalogo["ProductGroupDescription"]=="hoja"){
								$catalogo["TerritoryResource"] = $territorio["TerritoryResource"];
								$arrayFinal[] = $catalogo;
							}
				    	}
					}
				}
			}
		}
		return $arrayFinal;
	}
		public function getCatalogoRequest($TerrDimIntgId){
		$request_xml ='
			<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
				<soap:Body>
					<ns1:getCatalogCategories xmlns:ns1="http://xmlns.oracle.com/apps/orderCapture/salesCatalog/browse/salesCatalogService/types/">
					<ns1:usageCode>BASE</ns1:usageCode>
					<ns1:usageModeCode>MOO</ns1:usageModeCode>
					<ns1:runEligibilityFlag>false</ns1:runEligibilityFlag>
					<ns1:ancestorProductGroupId>'.$TerrDimIntgId.'</ns1:ancestorProductGroupId>
					<ns1:minimumDepth>1</ns1:minimumDepth>
					<ns1:maximumDepth>9999</ns1:maximumDepth>
					<ns1:minimumPathId>0</ns1:minimumPathId>
					<ns1:maximumSizeToReturn>50</ns1:maximumSizeToReturn>
					</ns1:getCatalogCategories>
				</soap:Body>
			</soap:Envelope>';
		return $request_xml;
	}
}

?>