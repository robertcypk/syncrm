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

class Index implements ControllerProviderInterface
{
    public $wsContacto = 'https://cang-test.crm.us2.oraclecloud.com:443/crmCommonSalesParties/ContactService?WSDL';
    /**********************************************************************************************/
    public function connect(Application $app)
    {
        $factory=$app['controllers_factory'];
        $factory->get('/', 'Web\Index::index');
        $factory->get('/paises', 'Web\Paises::lista');
        $factory->get('/academias', 'Web\Academias::lista');
        $factory->post('/registro', 'Web\Registro::form');
        $factory->post('/archivos', 'Web\Registro::upload');
        //$factory->get('/wr','Web\Index::verxml');
        //$factory->get('/putc','Web\Index::write');
        //$factory->get('/');
        return $factory;
    }
    public function index(Request $request, Application $app)
    {
        $jdcode = json_decode('[{"ContactPointId":"300000018988537","ContactPointType":"EMAIL","Status":"A","OwnerTableName":"HZ_PARTIES","OwnerTableId":"300000018984284","PrimaryFlag":"false","OrigSystemReference":"300000018988537","LastUpdateDate":"2017-07-26T22:22:09.038Z","LastUpdatedBy":"FORMWEB2","CreationDate":"2017-07-26T22:22:09.0Z","CreatedBy":"FORMWEB2","LastUpdateLogin":"5540C3F29CD516C0E053A79FC80AE011","RequestId":null,"ObjectVersionNumber":"1","CreatedByModule":"HZ_WS","ContactPointPurpose":"BUSINESS","PrimaryByPurpose":"N","StartDate":"2017-07-26","EndDate":"4712-12-31","RelationshipId":null,"PartyUsageCode":null,"OrigSystem":null,"EmailFormat":"","EmailAddress":"CESAR.HUASUPOMA.L@GMAIL.COM","PartyName":"CESAR HUASUPOMA","OverallPrimaryFlag":"false","EmailInformation":{"ContactPointId":"300000018988537","ContactPointType":"EMAIL","__FLEX_Context":null,"_FLEX_NumOfSegments":"0"}},{"ContactPointId":"300000019076601","ContactPointType":"EMAIL","Status":"A","OwnerTableName":"HZ_PARTIES","OwnerTableId":"300000018984284","PrimaryFlag":"false","OrigSystemReference":"300000019076601","LastUpdateDate":"2017-08-16T23:44:11.036Z","LastUpdatedBy":"FORMWEB2","CreationDate":"2017-08-16T23:44:11.001Z","CreatedBy":"FORMWEB2","LastUpdateLogin":"56E85C68A2EF378BE053A79FC80AB463","RequestId":null,"ObjectVersionNumber":"1","CreatedByModule":"HZ_WS","ContactPointPurpose":"BUSINESS","PrimaryByPurpose":"N","StartDate":"2017-08-16","EndDate":"4712-12-31","RelationshipId":null,"PartyUsageCode":null,"OrigSystem":null,"EmailFormat":"","EmailAddress":"diego.pachas2@ricoh-la.com","PartyName":"CESAR HUASUPOMA","OverallPrimaryFlag":"false","EmailInformation":{"ContactPointId":"300000019076601","ContactPointType":"EMAIL","__FLEX_Context":null,"_FLEX_NumOfSegments":"0"}},{"ContactPointId":"300000018988536","ContactPointType":"EMAIL","Status":"A","OwnerTableName":"HZ_PARTIES","OwnerTableId":"300000018984284","PrimaryFlag":"true","OrigSystemReference":"300000018988536","LastUpdateDate":"2017-08-17T16:58:13.112Z","LastUpdatedBy":"FORMWEB2","CreationDate":"2017-07-26T22:22:06.012Z","CreatedBy":"FORMWEB2","LastUpdateLogin":"56F6EAFC25E1964AE053A69FC80A49B3","RequestId":null,"ObjectVersionNumber":"7","CreatedByModule":"HZ_WS","ContactPointPurpose":"PERSONAL","PrimaryByPurpose":"N","StartDate":"2017-07-26","EndDate":"4712-12-31","RelationshipId":null,"PartyUsageCode":null,"OrigSystem":null,"EmailFormat":"","EmailAddress":"cesar.huasupoma@pucp.pe","PartyName":"CESAR HUASUPOMA","OverallPrimaryFlag":"true","EmailInformation":{"ContactPointId":"300000018988536","ContactPointType":"EMAIL","__FLEX_Context":null,"_FLEX_NumOfSegments":"0"}}]');

        $user = new \stdClass();
        $user->emailaddress2 = 'CESAR.HUASUPOMA.L@GMAIL.COM';
        $user->persondeoctrcorreopucpc = '';
        $email1 = true;
        $email2 = true;
        $email3 = true;
        foreach ($jdcode as $email) {
            echo '<br>----'.$email->EmailAddress.'-'.$email->Status.'-'.$email->PrimaryFlag .'--------<br>';
            if (!empty($user->emailaddress2)) {
                if ($user->emailaddress2 === $email->EmailAddress) {
                    $email2 = false;
                }
            } else {
                $email2 = false;
            }
            if (!empty($user->persondeoctrcorreopucpc)) {
                if ($user->persondeoctrcorreopucpc === $email->EmailAddress) {
                    $email3 = false;
                }
            } else {
                $email3 = false;
            }
        }
        if ($email2 != false) {
            echo 'xml emailaddress2:'.$user->emailaddress2.'<br>';
        }
        if ($email3 != false) {
            echo 'xml pucp:'.$user->emailaddress2.'<br>';
        }
        return '';
    }

    public function procedenciatxt($txt='', $app)
    {
        $array = [
            0 => [
                'codigo' => '1',
                'nombre' => 'Aviso en periódicos'
            ],
            1 => [
                'codigo' => '2',
                'nombre' => 'Aviso en revistas'
            ],
            2 => [
                'codigo' => '16',
                'nombre' => 'Búsqueda en Google'
            ],
            3 => [
                'codigo' => '3',
                'nombre' => 'Colegas o amigos'
            ],
            4 => [
                'codigo' => '11',
                'nombre' => 'Eventos'
            ],
            5 => [
                'codigo' => '12',
                'nombre' => 'Página Web CENTRUM'
            ],
            6 => [
                'codigo' => '4',
                'nombre' => 'Publicidad en correo electrónico'
            ],
            7 => [
                'codigo' => '21',
                'nombre' => 'Redes Sociales'
            ],
        ];
        $resultado = 'Sin procedencia';
        foreach ($array as $key => $value) {
            if ($value['codigo'] == $txt) {
                $resultado = $value['nombre'];
            }
        }
        return $resultado;
    }
    /*
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
        return base64_decode('');
    }*/
    //--
/**************************************************************************************/
}
