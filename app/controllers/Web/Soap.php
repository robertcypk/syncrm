<?php
namespace Web;

use Silex\Application;

require_once(__DIR__.'/lib/nusoap.php');
require_once(__DIR__.'/lib/nusoapmime.php');

class Soap{
    public function getClient($wsdl){
			$proxyhost = '';
			$proxyport = '';
			$proxyusername = 'FORMWEB2'; //user pre
			$proxypassword = 'qAWSED1'; //pass pre
			
            $client = new \nusoap_client($wsdl, 'wsdl',$proxyhost, $proxyport, $proxyusername, $proxypassword);
            $client->setCredentials($proxyusername, $proxypassword, 'basic');
            $client->soap_defencoding = 'utf-8';
            $client->decode_utf8 = false;
            $client->useHTTPPersistentConnection(); // Uses http 1.1 instead of 1.0
            $client->use_curl = TRUE;
            return $client;
        }
	 
	 public function getClientMime($wsdl){
		$proxyhost = '';
		$proxyport = '';
		$proxyusername = 'FORMWEB2'; //user pre
		$proxypassword = 'qAWSED1'; //pass pre
			
		$client = new \nusoap_client_mime($wsdl, 'wsdl',$proxyhost, $proxyport, $proxyusername, $proxypassword);
		$client->setCredentials($proxyusername, $proxypassword, 'basic');
		$client->soap_defencoding = 'US-ASCII';
    	$client->decode_utf8 = false;
    	$client->useHTTPPersistentConnection(); // Uses http 1.1 instead of 1.0
    	$client->use_curl = TRUE;
    	return $client;
    }
}
?>