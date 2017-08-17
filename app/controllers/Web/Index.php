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
        /*
        $jdcode = json_decode('[{"EmailAddress":"cesar.huasupoma.l@gmail.com","Status":"A","PrimaryFlag":"false"},{"EmailAddress":"cesar.huasupoma@pucp.pe","Status":"A","PrimaryFlag":"false"},{"EmailAddress":"cesar.huasupoma.l@gmail.com","Status":"A","PrimaryFlag":"false"}]');

        $user = new \stdClass();
        $user->emailaddress2 = 'cesar.huasupoma@pucp.pe';
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
        */
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
