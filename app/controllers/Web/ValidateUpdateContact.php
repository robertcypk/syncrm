<?php
namespace Web;

class ValidateUpdateContact{
	
	public function validateUpdateContact($vendedores,$lead, $le, $usuario, $dataForm, $vend,$app){
		$qb = $app['orm.em']->createQueryBuilder();

    	if($lead['OwnerId'] != $le['OwnerId']){
			
  			$vend = null;
  			foreach($vendedores as $vendedor){
  				if($lead['OwnerId'] == $vendedor->ResourceId){
  					$vend = $vendedor;
  				}
  			}
  			if(!isset($vend)){
  				$error = 'Se creo el contacto y el lead pero no se va poder enviar correo';
				//DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('try'=>($usuario->try+1), 'error'=>$error));
				
                $q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.try', $qb->expr()->literal( ($usuario['try']+1) ) )
                        ->set('u.error', $qb->expr()->literal( $error ) )
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
                $p = $q->execute();
				return $error;
			}
		}

		if(isset($vend)){

			/* SAILOR LOG 
			DB::table('sailor_log')->insert(array(
				'producto'=>$dataForm->programa
				,'vendedor'=>$vend->ResourceId
				,'fecha_reg'=>time()
			));
			*/

			//DB::table('usuario_crm')->where('id','=',$usuario->id)->update(array('crm'=>'1'));
			$q = $qb->update('Entity\Usuario_crm', 'u')
                        ->set('u.crm', $qb->expr()->literal( '1' ) )
                        ->where('u.id = ?1')
                        ->setParameter(1, $usuario['id'])
                        ->getQuery();
            $p = $q->execute();

			//DB::table('campo_crm')->insert(array('user_id'=>$usuario->id, 'campo'=>'email_final', 'valor'=>$vend->EmailAddress));
			$campo_crm = new \Entity\Campo_crm;
			$campo_crm->setUserid( $usuario['id'] );
			$campo_crm->setCampo( 'email_final' );
			$campo_crm->setValor( $vend->EmailAddress );
			$campo_crm->setUid('preestablecido');
			$campo_crm->setPag('preestablecido');
			$app['orm.em']->persist($campo_crm);
			$app['orm.em']->flush();	
		}
    }
}
?>