<?php
namespace Web;
class ValidarContacto{
	    public function validateUpdateContact($vendedores,$lead, $le, $usuario, $dataForm, $vend,$app){
		
		if($lead['OwnerId'] != $le['OwnerId']){
			echo 'Es diferente, asignar nuevamente un vendedor<br>';
  			$vend = null;
			
			foreach($vendedores as $vendedor){
  				if($lead['OwnerId'] == $vendedor->getResourceId()){
  					$vend = $vendedor;
  				}
  			}
			
  			if(!isset($vend)){
  				$error = 'Se creo el contacto y el lead pero no se va poder enviar correo';
				$qb = $app['orm.em']->createQueryBuilder();
                $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal( ($usuario[0]['try']+1) ) )
                        ->set('u.error', $qb->expr()->literal( $error ) )
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
                $p = $q->execute();
			}
		}
		
		//actualiza
		if(isset($vend) and isset($usuario[0]['id']) ){
			
			//ingreso al crm
			$sailor_log = new \Entity\Sailorlog;
			$sailor_log->setProducto( $dataForm->programa );
			$sailor_log->setVendedor( $vend->getOwnerid() );
			$sailor_log->setFechareg( time() );
			$app['orm.em']->persist($sailor_log);
			$app['orm.em']->flush();
			
			//validado
			$qb = $app['orm.em']->createQueryBuilder();
                $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.crm', $qb->expr()->literal( (1) ) )
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario[0]['id'])
                        ->getQuery();
                $p = $q->execute();

			//asignar el vendedor al cliente
			$campo_crm = new \Entity\Campo_crm;
			$campo_crm->setUserid( $usuario[0]['id'] );
			$campo_crm->setCampo( 'email_final' );
			$campo_crm->setValor( $vend->getEmailAddress() );
			$app['orm.em']->persist($campo_crm);
			$app['orm.em']->flush();
			
			return 'Actualizado user_id:'.$campo_crm->getUserid().' > vendedor :'.$vend->getEmailAddress();
		}else{
			return 'Sin procesar';
		}
    }
}
?>