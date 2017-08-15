<?php

namespace Web;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Silex\ControllerProviderInterface;
use Web\Registro;
use Web\Soap;
use Web\BuscarLead;
class Index implements ControllerProviderInterface{
	var $wsContacto = 'https://cang-test.crm.us2.oraclecloud.com:443/crmCommonSalesParties/ContactService?WSDL';
/**********************************************************************************************/
	public function connect(Application $app) {
		$factory=$app['controllers_factory'];
		$factory->get('/','Web\Index::index');
		$factory->get('/paises','Web\Paises::lista');
		$factory->get('/academias','Web\Academias::lista');
		$factory->post('/registro','Web\Registro::form');
		$factory->post('/archivos','Web\Registro::upload');
		$factory->get('/wr','Web\Index::verxml');
		//$factory->get('/putc','Web\Index::write');
		
		//$factory->get('/');
		return $factory;
  	}
	public function index(Request $request,Application $app){
		return '';
	}
	public function write(Request $request,Application $app){
		$file = 'test.txt';
		$message = 'some message that should appear on the last line of test.txt';
		$win = "sync\app\controllers\Web";
		$unix = "sync/app/controllers/Web";
		$ruta = str_replace($win,'',__DIR__).'uploads/';
		file_put_contents($ruta.$file, PHP_EOL . $message, FILE_APPEND);
		return '';
	}
	public function verxml(Request $request,Application $app){
		return base64_decode('CgkJCQk8c29hcGVudjpFbnZlbG9wZSB4bWxuczpzb2FwZW52PSJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy9zb2FwL2VudmVsb3BlLyIKCQkJCQl4bWxuczp0eXA9Imh0dHA6Ly94bWxucy5vcmFjbGUuY29tL2FwcHMvY2RtL2ZvdW5kYXRpb24vcGFydGllcy9wZXJzb25TZXJ2aWNlL2FwcGxpY2F0aW9uTW9kdWxlL3R5cGVzLyIKCQkJCQl4bWxuczpwZXI9Imh0dHA6Ly94bWxucy5vcmFjbGUuY29tL2FwcHMvY2RtL2ZvdW5kYXRpb24vcGFydGllcy9wZXJzb25TZXJ2aWNlLyIKCQkJCQl4bWxuczpwYXI9Imh0dHA6Ly94bWxucy5vcmFjbGUuY29tL2FwcHMvY2RtL2ZvdW5kYXRpb24vcGFydGllcy9wYXJ0eVNlcnZpY2UvIgoJCQkJCXhtbG5zOnNvdXI9Imh0dHA6Ly94bWxucy5vcmFjbGUuY29tL2FwcHMvY2RtL2ZvdW5kYXRpb24vcGFydGllcy9mbGV4L3NvdXJjZVN5c3RlbVJlZi8iCgkJCQkJeG1sbnM6Y29uPSJodHRwOi8veG1sbnMub3JhY2xlLmNvbS9hcHBzL2NkbS9mb3VuZGF0aW9uL3BhcnRpZXMvY29udGFjdFBvaW50U2VydmljZS8iCgkJCQkJeG1sbnM6Y29uMT0iaHR0cDovL3htbG5zLm9yYWNsZS5jb20vYXBwcy9jZG0vZm91bmRhdGlvbi9wYXJ0aWVzL2ZsZXgvY29udGFjdFBvaW50LyIKCQkJCQl4bWxuczpwYXIxPSJodHRwOi8veG1sbnMub3JhY2xlLmNvbS9hcHBzL2NkbS9mb3VuZGF0aW9uL3BhcnRpZXMvZmxleC9wYXJ0eVNpdGUvIgoJCQkJCXhtbG5zOnBlcjE9Imh0dHA6Ly94bWxucy5vcmFjbGUuY29tL2FwcHMvY2RtL2ZvdW5kYXRpb24vcGFydGllcy9mbGV4L3BlcnNvbi8iCgkJCQkJeG1sbnM6cmVsPSJodHRwOi8veG1sbnMub3JhY2xlLmNvbS9hcHBzL2NkbS9mb3VuZGF0aW9uL3BhcnRpZXMvcmVsYXRpb25zaGlwU2VydmljZS8iCgkJCQkJeG1sbnM6b3JnPSJodHRwOi8veG1sbnMub3JhY2xlLmNvbS9hcHBzL2NkbS9mb3VuZGF0aW9uL3BhcnRpZXMvZmxleC9vcmdDb250YWN0LyIKCQkJCQl4bWxuczpyZWwxPSJodHRwOi8veG1sbnMub3JhY2xlLmNvbS9hcHBzL2NkbS9mb3VuZGF0aW9uL3BhcnRpZXMvZmxleC9yZWxhdGlvbnNoaXAvIj4KCQkJCQk8c29hcGVudjpIZWFkZXIvPgoJCQkJCTxzb2FwZW52OkJvZHk+CgkJCQkJCTx0eXA6bWVyZ2VQZXJzb24+CgkJCQkJCSAgICA8dHlwOnBlcnNvblBhcnR5PgoJCQkJCQkgICAgICAgIDxwZXI6UGFydHlJZD4zMDAwMDAwMTg5OTAyNDA8L3BlcjpQYXJ0eUlkPgoJCQkJCQkgICAgICAgIAoJCQkJCQkJCQoJCQkJPHBlcjpFbWFpbD4KCQkJCQk8Y29uOk93bmVyVGFibGVOYW1lPkhaX1BBUlRJRVM8L2NvbjpPd25lclRhYmxlTmFtZT4KCQkJCQk8Y29uOk93bmVyVGFibGVJZD4zMDAwMDAwMTg5OTAyNDA8L2NvbjpPd25lclRhYmxlSWQ+CgkJCQkJPGNvbjpQcmltYXJ5RmxhZz5mYWxzZTwvY29uOlByaW1hcnlGbGFnPgoJCQkJCTxjb246Q29udGFjdFBvaW50UHVycG9zZT5CVVNJTkVTUzwvY29uOkNvbnRhY3RQb2ludFB1cnBvc2U+CgkJCQkJPGNvbjpFbWFpbEFkZHJlc3M+Y2VzYXIubEBob3RtYWlsLmNvbTwvY29uOkVtYWlsQWRkcmVzcz4KCQkJCQk8Y29uOlByaW1hcnlCeVB1cnBvc2U+TjwvY29uOlByaW1hcnlCeVB1cnBvc2U+CgkJCQkJPGNvbjpDcmVhdGVkQnlNb2R1bGU+SFpfV1M8L2NvbjpDcmVhdGVkQnlNb2R1bGU+CgkJCQk8L3BlcjpFbWFpbD4KCQkJCQkJCQkKCQkJCQkJCTwvdHlwOnBlcnNvblBhcnR5PgoJCQkJCQk8L3R5cDptZXJnZVBlcnNvbj4KCQkJCQk8L3NvYXBlbnY6Qm9keT4KCQkJCTwvc29hcGVudjpFbnZlbG9wZT4=');
	}
	//--
/**************************************************************************************/    
}
?>