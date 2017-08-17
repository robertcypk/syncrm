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
        //$x = $this->procedenciatxt(1,$app);
        //echo "{$x}";
        
        $jdcode = json_decode('[{"ContactPointId":"100000002031259","ContactPointType":"EMAIL","Status":"A","OwnerTableName":"HZ_PARTIES","OwnerTableId":"100000001990490","PrimaryFlag":"false","OrigSystemReference":null,"LastUpdateDate":"2015-08-24T07:07:55.212503Z","LastUpdatedBy":"lchocos@pucp.pe","CreationDate":"2015-08-24T07:07:41.054056Z","CreatedBy":"lchocos@pucp.pe","LastUpdateLogin":"-1","RequestId":"-99999","ObjectVersionNumber":"2","CreatedByModule":"HZ_IMPORT","ContactPointPurpose":"BUSINESS","PrimaryByPurpose":"N","StartDate":"2015-08-24","EndDate":"4712-12-31","RelationshipId":null,"PartyUsageCode":null,"OrigSystem":null,"EmailFormat":"","EmailAddress":"jaz7500@gmail.com","PartyName":"JAZMIN LY","OverallPrimaryFlag":"false","OriginalSystemReference":{"OrigSystemReferenceId":"100000002083782","OrigSystem":"CRMONDEMAND","OrigSystemReference":"300000003136772","OwnerTableName":"HZ_CONTACT_POINTS","OwnerTableId":"100000002031259","Status":"A","ReasonCode":null,"OldOrigSystemReference":null,"StartDateActive":"2015-08-24","EndDateActive":"4712-12-31","CreatedBy":"lchocos@pucp.pe","CreationDate":"2015-08-24T07:07:46.56004Z","LastUpdatedBy":"lchocos@pucp.pe","LastUpdateDate":"2015-08-24T07:07:46.56004Z","LastUpdateLogin":"-1","ObjectVersionNumber":"1","CreatedByModule":"HZ_IMPORT","PartyId":"100000001990490","RequestId":"-99999","SourceSystemRefInformation":{"OrigSystemRefId":"100000002083782","__FLEX_Context":null,"_FLEX_NumOfSegments":"0"}},"EmailInformation":{"ContactPointId":"100000002031259","ContactPointType":"EMAIL","__FLEX_Context":null,"_FLEX_NumOfSegments":"0"}},{"ContactPointId":"100000002031258","ContactPointType":"EMAIL","Status":"A","OwnerTableName":"HZ_PARTIES","OwnerTableId":"100000001990490","PrimaryFlag":"true","OrigSystemReference":null,"LastUpdateDate":"2017-08-17T20:40:12.136Z","LastUpdatedBy":"FORMWEB2","CreationDate":"2015-08-24T07:07:41.054056Z","CreatedBy":"lchocos@pucp.pe","LastUpdateLogin":"56F8F8D9A059DBC0E053A69FC80A45BF","RequestId":"-99999","ObjectVersionNumber":"3","CreatedByModule":"HZ_IMPORT","ContactPointPurpose":"PERSONAL","PrimaryByPurpose":"N","StartDate":"2015-08-24","EndDate":"4712-12-31","RelationshipId":null,"PartyUsageCode":null,"OrigSystem":null,"EmailFormat":"","EmailAddress":"jaz7500@gmail.com","PartyName":"JAZMIN LY","OverallPrimaryFlag":"true","OriginalSystemReference":{"OrigSystemReferenceId":"100000002084733","OrigSystem":"CRMONDEMAND","OrigSystemReference":"300000003136771","OwnerTableName":"HZ_CONTACT_POINTS","OwnerTableId":"100000002031258","Status":"A","ReasonCode":null,"OldOrigSystemReference":null,"StartDateActive":"2015-08-24","EndDateActive":"4712-12-31","CreatedBy":"lchocos@pucp.pe","CreationDate":"2015-08-24T07:07:46.56004Z","LastUpdatedBy":"lchocos@pucp.pe","LastUpdateDate":"2015-08-24T07:07:46.56004Z","LastUpdateLogin":"-1","ObjectVersionNumber":"1","CreatedByModule":"HZ_IMPORT","PartyId":"100000001990490","RequestId":"-99999","SourceSystemRefInformation":{"OrigSystemRefId":"100000002084733","__FLEX_Context":null,"_FLEX_NumOfSegments":"0"}},"EmailInformation":{"ContactPointId":"100000002031258","ContactPointType":"EMAIL","__FLEX_Context":null,"_FLEX_NumOfSegments":"0"}}]');

        $user = new \stdClass();
        $user->emailaddress2 = 'cesar.huasupoma@gmail.com';
        $user->persondeoctrcorreopucpc = '';

        foreach($jdcode as $email){
            echo $email->EmailAddress.'-'.$email->Status.'-'.$email->PrimaryFlag .'<br>';
                if(!empty($user->emailaddress2)){
                    if( $user->emailaddress2 == $email->EmailAddress ){
                        echo '-false-';
                    }
                }else{
                    echo '-false-';
                }
                if(!empty($user->persondeoctrcorreopucpc) ){
                    if( $user->persondeoctrcorreopucpc == $email->EmailAddress ){
                        echo '-false-';
                    }
                }else{
                    echo '-false-';
                }
        }
        
        return '';
    }

    public function procedenciatxt($txt='', $app )
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
