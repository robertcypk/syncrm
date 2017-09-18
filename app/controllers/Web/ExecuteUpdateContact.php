<?php
namespace Web;

use Web\MergeContact;
use Web\MassiveContact;
use Web\Buscarleadfind;
use Web\Createlead;
use Web\Uperson;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Web\Emailuser;

class ExecuteUpdateContact
{
    public $wsPerson = 'https://cang-test.crm.us2.oraclecloud.com:443/foundationParties/PersonService?WSDL';

    public function init(Request $request, Application $app)
    {
        $idlog = $request->get('idlog');
        $loguser = $app['orm.em']->getRepository('Entity\Loguser')->findOneBy(array('idlog'=>$idlog ));
        if (!empty($loguser)) {
            $campos = $app['orm.em']->getRepository('Entity\Campo_crm')->findBy(array('user_id'=>$loguser->getUserid() ));
            if (count($campos)==0) {
                return 'No existen campos del usuario';
            }
            //dataForm
            $dataForm = new \stdClass();
            foreach ($campos as $campo) {
                $dataForm->{$campo->getCampo()} = $campo->getValor();
            }
            $dataForm->atendido = $loguser->getOwnerid();
            $dataForm->ownerpartyid = $dataForm->atendido;
            //usuario_crm
            $usuario = $app['orm.em']->getConnection()->prepare("SELECT * FROM usuario_crm WHERE tipo=1 AND crm=1 OR crm=0  AND try!=4 AND id='".$loguser->getUserid()."'");
            $usuario->execute();
            $usuario = $usuario->fetchAll();
                    
            $contacto = new \Web\Checkcontact();
            $contacto = $contacto->checkContactf($dataForm, 1, $usuario['id'], $app);
                    
            if (empty($contacto)) {
                return 'No hay usuario para actualizae';
            }

            $lead = $this->executeUpdateContact($contacto, $dataForm, $usuario, $app);
                    
            $savexml = new \Web\Logsrv();
            $savexml->savelog(json_encode($lead), 'executeUpdateContact-result');

            if (!empty($lead['OwnerId'])) {
                /***/
                $vendedores = $app['orm.em']->getRepository('Entity\Resource')->findBy(array('Id'=>$loguser->getProgramaid()), array('CTROrdenRuleta_c'=> 'ASC'));
                if ($lead['OwnerId'] != $loguser->getOwnerid()) {
                    $vend = null; //new \stdClass(); //vuelve a inicializar
                    foreach ($vendedores as $vendedor) {
                        //recorre el registro de vendedores otra vez
                        if ($lead['OwnerId'] == $vendedor->getResourceId()) {
                            // si el ownerid de lead es igual al resourceid de la tabla resource
                            $vend = $vendedor;
                        }
                    }
                    if (empty($vend)) { //no existen vendedor?
                        $error = 'Se creo el contacto y el lead pero no se va poder enviar correo xq la vendedora no esta en los usuarios';
                        $try = $usuario['try']+1;
                        $qb = $app['orm.em']->createQueryBuilder();
                        $q = $qb->update('Entity\Usuario_crm', 'u')->set('u.try', $qb->expr()->literal($try))->set('u.error', $qb->expr()->literal($error))->where('u.id = ?1')->setParameter(1, $loguser->getUserid())
                                        ->getQuery();
                        $p = $q->execute();
                                
                        $logger = new Emailuser();
                        $log = $logger->logger('Error De Registro', $error, 'checkContact-request', $app);
                        
                        return 0;
                    }
                }
                // existen vendedores de programa en recursos?
                if (!empty($vendedores)) {
                    /* existe */
                    $count_2 = 1;
                    $vend_2 = null;
                    // existe vendedor de programa?
                    if (empty($vend)) {
                        //tabla resource
                        foreach ($vendedores as $vendedor) {
                            //$vend['ownerid']
                            if ($lead['OwnerId'] == $vendedor->getResourceId()) {
                                $vend_2 = $vendedor;
                            }
                        }
                    } else {
                        $vend_2 = $vend;
                    }
                                
                    if (!isset($vend_2)) {
                        $error = 'Se creo el contacto y el lead pero no se va poder enviar correo xq la vendedora no esta en la ruleta';
                        $qb = $app['orm.em']->createQueryBuilder();
                        $q = $qb->update('Entity\Usuario_crm', 'u')
                                            ->set('u.try', $qb->expr()->literal(($usuario['try']+1)))->set('u.error', $qb->expr()->literal($error))
                                            ->where('u.id = ?1')->setParameter(1, $loguser->getUserid())
                                            ->getQuery();
                        $p = $q->execute();

                        $logger = new Emailuser();
                        $log = $logger->logger('Error De Registro', $error, 'checkContact-request', $app);
                                        
                        return 0;
                    } else { // tabla sailor
                        /**/
                        $checkemail = $app["orm.em"]->getRepository('Entity\Campo_crm')->findOneBy(array('user_id'=> $loguser->getUserid(),'campo' => 'email_final'));
                                    
                        //actualizar estado CRM
                        $qb = $app['orm.em']->createQueryBuilder();
                        $q = $qb->update('Entity\Usuario_crm', 'u')->set('u.crm', $qb->expr()->literal('1'))->where('u.id = ?1')->setParameter(1, $loguser->getUserid())->getQuery();
                        $p = $q->execute();
                        $app['orm.em']->flush();
                                    
                        //Verifica si el campo existe//$usuario['id']
                        if (empty($checkemail)) {
                            $campo_crm = new \Entity\Campo_crm;
                            $campo_crm->setUserid($loguser->getUserid());
                            $campo_crm->setCampo('email_final');
                            $campo_crm->setValor($vend_2->getEmailaddress());
                            $app['orm.em']->persist($campo_crm);
                            $app['orm.em']->flush();
                        } else {
                            $qb = $app['orm.em']->createQueryBuilder();
                            $q = $qb->update('Entity\Campo_crm', 'c')
                                                ->set('c.valor', $qb->expr()->literal($vend_2->getEmailaddress()))
                                                ->where(
                                                    $qb->expr()->andX(
                                                        $qb->expr()->eq('c.campo', '?1'),
                                                        $qb->expr()->eq('c.user_id ', ' ?2')
                                                    )
                                                )
                                                ->setParameter(1, 'email_final')->setParameter(2, $loguser->getUserid())->getQuery();
                            $p = $q->execute();
                            $app['orm.em']->flush();
                        }
                        $email = new \Web\EmailCrm();
                        return $email->syncEmailUserCRM($app, $vend_2->getEmailaddress(), $loguser->getUserid());
                    }
                    /* existe */
                } else {
                    $error = 'No existen vendores registrados para el programa';
                    $try = $usuario['try']+1;
                    $qb = $app['orm.em']->createQueryBuilder();
                    $q = $qb->update('Entity\Usuario_crm', 'u')
                                        ->set('u.try', $qb->expr()->literal($try))->set('u.error', $qb->expr()->literal($error))
                                        ->where('u.id = ?1')->setParameter(1, $loguser->getUserid())->getQuery();
                    $p = $q->execute();

                    $logger = new Emailuser();
                    $log = $logger->logger('Error De Registro', $error, 'checkContact-request', $app);

                    return $error;
                }
                /***/
            } else {
                /***/
                $lead = $this->executeUpdateContact($contacto, $dataForm, $usuario, $app);
                
                if (!empty($lead['OwnerId'])) {
                    $vendedores = $app['orm.em']->getRepository('Entity\Resource')->findBy(array('Id'=>$loguser->getProgramaid()), array('CTROrdenRuleta_c'=> 'ASC'));
                    if ($lead['OwnerId'] != $loguser->getOwnerid()) {
                        $vend = null;
                        //new \stdClass(); //vuelve a inicializar
                        foreach ($vendedores as $vendedor) {
                            //recorre el registro de vendedores otra vez
                            if ($lead['OwnerId'] == $vendedor->getResourceId()) {
                                $vend = $vendedor;
                            }
                        }
                                
                        if (empty($vend)) { //no existen vendedor?
                            $error = 'Se creo el contacto y el lead pero no se va poder enviar correo xq la vendedora no esta en los usuarios';
                            $try = $usuario['try']+1;
                            $qb = $app['orm.em']->createQueryBuilder();
                            $q = $qb->update('Entity\Usuario_crm', 'u')
                                            ->set('u.try', $qb->expr()->literal($try))->set('u.error', $qb->expr()->literal($error))
                                            ->where('u.id = ?1')->setParameter(1, $loguser->getUserid())->getQuery();
                            $p = $q->execute();

                            $logger = new Emailuser();
                            $log = $logger->logger('Error De Registro', $error, 'checkContact-request', $app);
                                        
                            return 0;
                        }
                    }
                                
                    // existen vendedores de programa en recursos?
                    if (!empty($vendedores)) {
                        $count_2 = 1;
                        $vend_2 = null;
                        // existe vendedor de programa?
                        if (empty($vend)) {
                            //tabla resource
                            foreach ($vendedores as $vendedor) {
                                if ($lead['OwnerId'] == $vendedor->getResourceId()) {
                                    $vend_2 = $vendedor;
                                }
                            }
                        } else {
                            $vend_2 = $vend;
                        }
                                    
                        if (!isset($vend_2)) {
                            $error = 'Se creo el contacto y el lead pero no se va poder enviar correo xq la vendedora no esta en la ruleta';
                            $qb = $app['orm.em']->createQueryBuilder();
                            $q = $qb->update('Entity\Usuario_crm', 'u')
                                                ->set('u.try', $qb->expr()->literal(($usuario['try']+1)))
                                                ->set('u.error', $qb->expr()->literal($error))
                                                ->where('u.id = ?1')
                                                ->setParameter(1, $loguser->getUserid())
                                                ->getQuery();
                            $p = $q->execute();
                            
                            $logger = new Emailuser();
                            $log = $logger->logger('Error De Registro', $error, 'checkContact-request', $app);

                            return 0;
                        } else { // tabla sailor
                                        
                            /**/
                            $checkemail = $app["orm.em"]->getRepository('Entity\Campo_crm')->findOneBy(array('user_id'=> $loguser->getUserid(),'campo' => 'email_final'));
                                        
                            //actualizar estado CRM
                                        
                            $qb = $app['orm.em']->createQueryBuilder();
                            $q = $qb->update('Entity\Usuario_crm', 'u')
                                                    ->set('u.crm', $qb->expr()->literal('1'))
                                                    ->where('u.id = ?1')
                                                    ->setParameter(1, $loguser->getUserid())
                                                    ->getQuery();
                            $p = $q->execute();
                            $app['orm.em']->flush();
                                        
                            //Verifica si el campo existe
                            if (empty($checkemail)) {
                                $campo_crm = new \Entity\Campo_crm;
                                $campo_crm->setUserid($loguser->getUserid());
                                $campo_crm->setCampo('email_final');
                                $campo_crm->setValor($vend_2->getEmailaddress());
                                $app['orm.em']->persist($campo_crm);
                                $app['orm.em']->flush();
                            } else {
                                $qb = $app['orm.em']->createQueryBuilder();
                                $q = $qb->update('Entity\Campo_crm', 'c')
                                                    ->set('c.valor', $qb->expr()->literal($vend_2->getEmailaddress()))
                                                    ->where(
                                                        $qb->expr()->andX($qb->expr()->eq('c.campo', '?1'), $qb->expr()->eq('c.user_id ', ' ?2'))
                                                    )
                                                    ->setParameter(1, 'email_final')
                                                    ->setParameter(2, $loguser->getUserid())
                                                    ->getQuery();
                                $p = $q->execute();
                                $app['orm.em']->flush();
                            }
                            $email = new \Web\EmailCrm();
                            return $email->syncEmailUserCRM($app, $vend_2->getEmailaddress(), $loguser->getUserid());
                        }
                    } else {
                        $error = 'No existen vendores registrados para el programa';
                        $try = $usuario['try']+1;
                        $qb = $app['orm.em']->createQueryBuilder();
                        $q = $qb->update('Entity\Usuario_crm', 'u')->set('u.try', $qb->expr()->literal($try))
                                            ->set('u.error', $qb->expr()->literal($error))
                                            ->where('u.id = ?1')->setParameter(1, $loguser->getUserid())->getQuery();
                        $p = $q->execute();
                        
                        $logger = new Emailuser();
                        $log = $logger->logger('Error De Registro', $error, 'checkContact-request', $app);

                        return $error;
                    }
                }
                /***/
            }
        
            /*
            if(!empty($lead['OwnerId']))
            {
            }
            else
            {


            }else{
                    $error = 'No existen leads registrados';
                            $try = $usuario['try']+1;
                            $qb = $app['orm.em']->createQueryBuilder();
                            $q = $qb->update('Entity\Usuario_crm', 'u')
                                    ->set('u.try', $qb->expr()->literal( $try ) )->set('u.error', $qb->expr()->literal( $error ) )
                                    ->where('u.id = ?1')->setParameter(1, $loguser->getUserid())
                                    ->getQuery();
                            $p = $q->execute();
                        return $error;
                }
            */
            /* */
        } else {
            return 'No existen usuarios para actualizar leads';
        }
    }
    /* Execute Update Contact */
    public function executeUpdateContact($contacto, $dataForm, $usuario, $app)
    {
        $MassiveContact = new MassiveContact();
        $userUpdated = $MassiveContact->updateContactMassive($contacto, $dataForm, 1);
        
        
        if (empty($userUpdated['result'])) {
            $error = 'Error al actualizar un contacto';
            //DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('try'=>($usuario->try+1), 'error'=>$error));
            $qb = $app['orm.em']->createQueryBuilder();
            $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal(($usuario['try']+1)))
                        ->set('u.error', $qb->expr()->literal($error))
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
            $p = $q->execute();
            return $error;
        }
        
        //if($dataForm->origen==1){ // admision
        $mergecontacto = new MergeContact();
        $merged = $mergecontacto->mergeContacto($contacto, $dataForm, 1);
        if (!isset($merged)) {
            $error = 'Error al realizar el merge del contacto';
            //DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('try'=>($usuario->try+1), 'error'=>$error));
            $qb = $app['orm.em']->createQueryBuilder();
            $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal(($usuario['try']+1)))
                        ->set('u.error', $qb->expr()->literal($error))
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
            $p = $q->execute();
            return $error;
        }
        // }
        
        
        $updateperson = new Uperson();
        $updateperson = $updateperson->updatePerson($contacto, $dataForm);
        if (!isset($dataForm->programa)) { //CTRNumProd_c - productId
            $error = 'Error el programa no tiene product id';
            //DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('try'=>($usuario->try+1), 'error'=>$error));
            $qb = $app['orm.em']->createQueryBuilder();
            $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal(($usuario['try']+1)))
                        ->set('u.error', $qb->expr()->literal($error))
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
            $p = $q->execute();
            return $error;
        }
        
        $buscarleadfind = new Buscarleadfind();
        $anterior = $buscarleadfind->buscarLeadFind($contacto, $dataForm, 1);
        
        //var_dump( $anterior );
        if (!empty($anterior['faultstring'])) {
            return $anterior['faultstring'];
        }
        
        if (!isset($anterior)) {
            $createlead = new Createlead();
            $lead = $createlead->createLead($contacto, $dataForm, 1);
            if (!isset($lead)) {
                $error = 'Error al crear el lead de un contacto ya creado';
                //DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('try'=>($usuario->try+1), 'error'=>$error));
                $qb = $app['orm.em']->createQueryBuilder();
                $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal(($usuario['try']+1)))
                        ->set('u.error', $qb->expr()->literal($error))
                        ->where('u.id = ?1')->setParameter(1, $usuario['id'])->getQuery();
                $p = $q->execute();
                return $error;
            } elseif (!empty($lead['faultstring'])) {
                return $lead['faultstring'];
            }
            return $lead;
        } else {
            $updatelead = new \Web\Updatelead();
            $updatelead = $updatelead->updateLead($anterior, $dataForm, 1);
            
            if (!isset($updatelead)) {
                $error = 'Error al actualizar el lead de un contacto ya creado';
                //DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('try'=>($usuario->try+1), 'error'=>$error));
                $qb = $app['orm.em']->createQueryBuilder();
                $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal(($usuario['try']+1)))
                        ->set('u.error', $qb->expr()->literal($error))
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
                $p = $q->execute();
                return $error;
            } elseif (!empty($updatelead['faultstring'])) {
                return $updatelead['faultstring'];
            }
            return $updatelead;
        }
    }
    /**/
}
