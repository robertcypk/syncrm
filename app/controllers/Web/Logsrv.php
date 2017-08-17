<?php
namespace Web;

/**
* guardar eventos de la app
*/
class Logsrv
{
    public function savelog($arg, $servicio)
    {
        $file = 'log-'.$servicio.'-'.date('d-m-Y').'.txt';
        if (is_array($arg)) {
            $message = json_encode($arg);
        } else {
            $message = $arg;
        }
        

        $win = "sync\app\controllers\Web";
        $unix = "sync/app/controllers/Web";
        $ruta = str_replace($win, '', __DIR__).'uploads/';
        file_put_contents($ruta.$file, PHP_EOL . $message, FILE_APPEND);
        return '';
    }
}
