<?php
namespace Web;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Silex\ControllerProviderInterface;
use Web\Emailuser;

//use Web\Insertcontact;
//use Web\Insertmergeperson;
//use Web\Updatepersonemail;
//use Web\Createlead;
class Createcontact
{
    public $wsContacto = 'https://cang-test.crm.us2.oraclecloud.com:443/crmCommonSalesParties/ContactService?WSDL';
    public $wsPerson = 'https://cang-test.crm.us2.oraclecloud.com:443/foundationParties/PersonService?WSDL';
    public $wsResource = 'https://cang-test.crm.us2.oraclecloud.com:443/foundationResources/ResourceService?WSDL';
    public $wsLead = 'https://cang-test.crm.us2.oraclecloud.com:443/mklLeads/SalesLeadService?WSDL';
    public $wsPrograma = 'https://cang-test.crm.us2.oraclecloud.com:443/mktExtensibility/MarketingCustomObjectService?WSDL';
    public function createContact(Request $request, Application $app)
    {
        $resourceId = $request->get('resourceid');
        $userid = $request->get('userid');
        $finalemail = $request->get('email');
        
        
        if (empty($resourceId) and empty($userid) and empty($finalemail)) {
            return 'Sin procesar';
        }
        
        $campos = $app['orm.em']->getRepository('Entity\Campo_crm')->findBy(array('user_id'=>$userid));
        
        $usuarios = $app['orm.em']->getConnection()->prepare("SELECT DISTINCT * FROM usuario_crm WHERE tipo=1 AND crm=0 AND try!=4 and id='".$userid."'");
        $usuarios->execute();
        $usuarios = $usuarios->fetchAll();
        
        
        if (empty($usuarios)) {
            return 'No hay nuevos usuarios a registrar';
        } else {
            $usuario = $usuarios[0];
        }
        
        if (count($campos)==0) {
            $error = 'No existen campos del usuario no se puede enviar email';
            $qb = $app['orm.em']->createQueryBuilder();
            $q = $qb->update('Entity\Usuario_crm', 'u')
                                ->set('u.email', $qb->expr()->literal('1'))
                                ->set('u.error', $qb->expr()->literal($error))
                                ->where('u.id = ?1')
                                ->setParameter(1, $usuario['id'])
                                ->getQuery();
            $p = $q->execute();

            exit;
        }
        
        $dataForm = new \stdClass();
        foreach ($campos as $campo) {
            $dataForm->{$campo->getCampo()} = $campo->getValor();
        }
        
        
        //
        $dataForm->atendido = $resourceId;
        $dataForm->ownerpartyid = $dataForm->atendido;
        
        
        $insertcontact = new \Web\Insertcontact;
        $userInserted = $insertcontact->insertContact($dataForm, 1, $this->wsContacto);
        
        if (isset($userInserted['faultstring'])) {
            $error = 'Error al crear un nuevo contacto';
            $qb = $app['orm.em']->createQueryBuilder();
            $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal(($usuario['try']+1)))
                        ->set('u.error', $qb->expr()->literal($error))
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
            $p = $q->execute();
            
            $logger = new Emailuser();
            $log = $logger->logger('Error De Registro', $error, 'Insertcontact', $app);

            return $error;
        }
        
        
        $imperson = new \Web\Insertmergeperson;
        $imperson_rs = $imperson->insertmergeperson($userInserted, $dataForm, 1, $this->wsPerson);
        
        
        if (isset($imperson_rs['faultstring'])) {
            $error = $imperson_rs['faultstring'];
            $qb = $app['orm.em']->createQueryBuilder();
            $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal(($usuario['try']+1)))
                        ->set('u.error', $qb->expr()->literal($error))
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
            $p = $q->execute();
            
            $logger = new Emailuser();
            $log = $logger->logger('Error De Registro', $error, 'Insertmergeperson', $app);

            return $error;
        }
        
        
        $update_persone_mail = new \Web\Updatepersonemail;
        $update_persone_mail = $update_persone_mail->updatePersonEmail($userInserted, 1, $this->wsPerson);
        if (isset($update_persone_mail['faultstring'])) {
            $error = $update_persone_mail['PartyId'].'-'.$update_persone_mail['PrimaryEmailContactPTId'].'-'.$update_persone_mail['faultstring'];
            $qb = $app['orm.em']->createQueryBuilder();
            $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal(($usuario['try']+1)))
                        ->set('u.error', $qb->expr()->literal($error))
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
            $p = $q->execute();
            
            $logger = new Emailuser();
            $log = $logger->logger('Error De Registro', $error, 'Updatepersonemail', $app);

            return $error;
        }

        $createlead = new \Web\Createlead;
        $lead_c = $createlead->createLead($userInserted, $dataForm, 1, $this->wsLead);
        if (!isset($lead_c)) {
            $error = 'Error al crear el lead del nuevo contacto';
            $qb = $app['orm.em']->createQueryBuilder();
            $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal(($usuario['try']+1)))
                        ->set('u.error', $qb->expr()->literal($error))
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
            $p = $q->execute();

            $logger = new Emailuser();
            $log = $logger->logger('Error De Registro', $error, 'Createlead', $app);
            
            return $error;
        }
        /*
        DB::table('sailor_log')->insert(array(
                                    'producto'=>$dataForm->programa
                                    ,'vendedor'=>$vend->ResourceId
                                    ,'fecha_reg'=>time()
                                ));
        */
        /*
        if(isset($vendedores)){
            //DB::table('sailor')->where('producto','=',$dataForm->programa)->delete();
            $delete_product = $app["orm.em"]->getRepository('Entity\Sailor')->findOneBy( array('producto'=> $dataForm->programa) );
            $app["orm.em"]->remove($delete_product);
            $app["orm.em"]->flush();
            $count_2 = 1;
            foreach($vendedores as $vendedor){
                $pos = 0;
                if($count == $count_2){
                    $pos = $count+1;
                    if($pos > count($vendedores)){
                        $pos = 1;
                    }
                }
                            $sailor_in = new \Entity\Sailor;
                            $sailor_in->setId($count_2);
                            $sailor_in->setPosicion( $pos );
                            $sailor_in->setEstado( 1 );
                            $sailor_in->setProducto( $dataFrom->programa );
                            $sailor_in->setVendedor( $vendedores->getResourceId() );
                $count_2++;
            }
        }
        */
        $qb = $app['orm.em']->createQueryBuilder();
        $q = $qb->update('Entity\Usuario_crm', 'u')
                ->set('u.crm', $qb->expr()->literal('1'))
                ->where('u.id = ?1')
                ->setParameter(1, $usuario['id'])
                ->getQuery();
        $p = $q->execute();
        
        
        $campo_crm = new \Entity\Campo_crm;
        $campo_crm->setUserId($usuario['id']);
        $campo_crm->setCampo('email_final');
        $campo_crm->setValor($finalemail);
        $campo_crm->setUid('preestablecido');
        $campo_crm->setPag('preestablecido');
        $app['orm.em']->persist($campo_crm);
        $app['orm.em']->flush();
        
        //notificar vendedor
        $email = new \Web\EmailCrm();
        return $email->syncEmailUserCRM($app, $finalemail, $usuario['id']);
        
        
        //return '1';
    }
}
