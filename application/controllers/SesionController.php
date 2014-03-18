<?php

class SesionController extends My_Controller_Action
{
     private $_model_sesion;
     private $_model_espectaculo;
     private $_model_usuarios;
     private $_model_recinto;
     
     public function init()
    {
        $this->_model_sesion = new Application_Model_Sesion();
        $this->_model_espectaculo = new Application_Model_Espectaculo();
        $this->_model_usuarios = new Application_Model_Usuario();
        $this->_model_recinto = new Application_Model_Recinto(); 
    }
    
   
    // Handle GET and return a list of resources
    public function indexAction(){   
        
        $api =  $this->getRequest()->getParam('apikey');
        if($api)
        {          
            $select = $this->_model_usuarios->select()
                     ->where("api_key = ?", $api);           
            $respuesta = $this->_model_usuarios->fetchAll($select)->toArray();
            
            if ($respuesta){
               
               $select_sesion = $this->_model_usuarios->select()
                        ->setIntegrityCheck(false)
                        ->join('espectaculo','espectaculo.id_usuario = usuario.id_usuario' )
                        ->join('sesion','espectaculo.id_espectaculo = sesion.id_espectaculo' )
                        ->where('espectaculo.id_usuario = ?' , $respuesta[0]["id_usuario"]);      
        
                $sesions = $this->_model_sesion->fetchAll($select_sesion)->toArray();                 
                $this->_helper->json($sesions);

            }else{
                 $this->_helper->json("Error con la apikey");
            }
        }else{
           $this->_helper->json("Falta la apikey");
        }
      
    }

   
    // Handle GET and return a specific resource item
    public function getAction(){
        $api =  $this->getRequest()->getParam('apikey');
        $id_espectaculo =  $this->getRequest()->getParam('id');
        if($api && $id_espectaculo)
        {       
            $select = $this->_model_usuarios->select()
                     ->where("api_key = ?", $api);           
            
            $resultado = $this->_model_usuarios->fetchAll($select)->toArray();
            if ($resultado){
                           
                $select = $this->_model_espectaculo->select()
                     ->where("id_espectaculo = ?", $id_espectaculo);      
                $resultado2 = $this->_model_espectaculo->fetchAll($select)->toArray();
                if($resultado2){

                    $select_sesion = $this->_model_usuarios->select()
                        ->setIntegrityCheck(false)
                        ->join('espectaculo','espectaculo.id_usuario = usuario.id_usuario' )
                        ->join('sesion','espectaculo.id_espectaculo = sesion.id_espectaculo' )
                        ->where('espectaculo.id_espectaculo = ?' , $id_espectaculo);
        
                $sesions = $this->_model_sesion->fetchAll($select_sesion)->toArray();  
                
                $this->_helper->json($sesions);
                }else{
                 $this->_helper->json("Error con id");
                }
            }else{
                 $this->_helper->json("Error con la apikey");
            }
        }else{
           $this->_helper->json("Falta la apikey");
        }
    }
    
    public function testAction(){
        
    }

    // Handle POST requests to create a new resource item
    public function postAction(){
        
        $api = $this->getRequest()->getParam('api_key');
        $id_espectaculo = $this->getRequest()->getParam('id_espectaculo');
        $fechaInicio = $this->getRequest()->getParam('fecha_hora_inicio');
        $duracion = $this->getRequest()->getParam('duracion');
        $capacidad = $this->getRequest()->getParam('capacidad');
        $precio = $this->getRequest()->getParam('precio');
        $iva = $this->getRequest()->getParam('iva');

        $direccion = $this->getRequest()->getParam('direccion');
        $ciudad = $this->getRequest()->getParam('ciudad');
        $provincia = $this->getRequest()->getParam('provincia');
        $pais = $this->getRequest()->getParam('pais');
        
        if ($api && $id_espectaculo && $fechaInicio && $duracion && $capacidad
                && $precio && $iva && $direccion && $ciudad && $provincia && $pais)
        {
                
                $select = $this->_model_usuarios->select()
                         ->where("api_key = ?", $api);
                
                $resultado = $this->_model_usuarios->fetchAll($select)->toArray();

            if ($resultado){              
                               
                $select = $this->_model_espectaculo->select()
                         ->where("id_espectaculo = ?", $id_espectaculo);
                $resultado2 = $this->_model_usuarios->fetchAll($select)->toArray();
                
                if ($resultado && $this->validarSesion($direccion, $ciudad, $provincia, $pais, $fechaInicio, $capacidad, $precio, $iva) ){                 


                    ///Insertamos una sesion///
                    $fila = array(
                        'fecha_hora_inicio' =>  $fechaInicio,
                        'duracion' =>  $duracion,
                        'capacidad' => $capacidad,
                        'vendidas' => 0,
                        'precio' => $precio,
                        'iva' => $iva,
                        'activa' => 1,
                        'id_espectaculo' => $id_espectaculo,                                        
                    );

                    $sesion = $this->_model_sesion->insert($fila);


                      ///Insertamos un recinto///
                        $fila = array(
                            'direccion' =>  $direccion,
                            'ciudad' =>  $ciudad,
                            'provincia' => $provincia,
                            'pais' => $pais,
                            'id_sesion' => $array[0]["id_sesion"],                                        
                        );

                        $recinto = $this->_model_recinto->insert($fila);

                     //JSON devuelta
                     $arrayDevuelta = array(
                        'id_sesion' =>  $sesion[0]["id_sesion"],
                        'guardado' =>  "OK",                                   
                    );

                    $this->_helper->json($arrayDevuelta);
              }else{
                    $this->_helper->json("Error con el id de espectaculo!");
              }
           }else{
                $this->_helper->json("Error con la api key");
                }
        }else{
            $this->_helper->json("Falta mandar datos!!!");
        }
    }

    // Handle PUT requests to update a specific resource item
    public function putAction(){
        $this->_helper->json("Esto no esta implementado");
    }

    // Handle DELETE requests to delete a specific item
    public function deleteAction(){
         $this->_helper->json("Esto no esta implementado");
    }
    
    private function anadirRecinto($direccion,$ciudad,$provincia,$pais){
        if(is_string($direccion) && !empty($direccion)){   
            if(is_string($ciudad) && !empty($ciudad)){   
                if(is_string($provincia) && !empty($provincia)){   
                    if(is_string($pais) && !empty($pais)){   
                       return TRUE;
                    }else{
                        return FALSE;
                    }
                }else{
                    return FALSE;
                }
            }else{
                return FALSE;
            }   
        }else{
            return FALSE;
        }
    }
    
    
    protected function validarSesion($direccion,$ciudad,$provincia,$pais,$fechaInicio,$capacidad,$precio,$iva){
        if($this->anadirRecinto($direccion,$ciudad,$provincia,$pais)!= FALSE){
            $validator = new Zend_Validate_Date('YYYY-MM-DD hh:mm:ss');
            $fecha = new Zend_Date();
            if ($validator->isValid($fechaInicio) && $fecha->get('YYYY-MM-DD hh:mm:ss') < $fechaInicio){                                    
                if(is_numeric($capacidad)){                                 
                    if(is_numeric($precio)){
                        if (is_numeric($iva)){

                            return TRUE;
                        }else{
                            return FALSE;
                        }
                    }else{
                        return FALSE;
                    }
                }else{
                    return FALSE;
                }
            }else{
                  return FALSE;
            }
        }else{
              return FALSE;
        }
    }
}

