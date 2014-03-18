<?php

class SesionController extends My_Controller_Action
{
     private $_model_sesion;
     private $_model_espectaculo;
     private $_model_usuarios;
     
     public function init()
    {
        $this->_model_sesion = new Application_Model_Sesion();
        $this->_model_espectaculo = new Application_Model_Espectaculo();
         $this->_model_usuarios = new Application_Model_Usuario();
    }
    
     public function __call($method, $args)
    {
        if ('Action' == substr($method, -6)) {
            $controller = $this->getRequest()->getControllerName();
            $url = '/' . $controller . '/index';
            return $this->_redirect($url);
        }
 
        throw new Exception('Invalid method');
    }
    
    // Handle GET and return a list of resources
    public function indexAction(){   
        
        if(isset($_GET["apikey"]))
        {
            $api =  $this->getRequest()->getParam('apikey');
                     
            $select = $this->_model_usuarios->select()
                     ->where("api_key = ?", $api);           
            
            if ($select->query()->rowCount() == 1){
                $array = $select->query()->fetchAll();
               $select_sesion = $this->_model_usuarios->select()
                        ->setIntegrityCheck(false)
                        ->join('espectaculo','espectaculo.id_usuario = usuario.id_usuario' )
                        ->join('sesion','espectaculo.id_espectaculo = sesion.id_espectaculo' )
                        ->where('espectaculo.id_usuario = ?' , $array[0]["id_usuario"]);
        
        
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
        if(isset($_GET["apikey"]) && isset($_GET["id"]))
        {
            $api =  $this->getRequest()->getParam('apikey');
                     
            $select = $this->_model_usuarios->select()
                     ->where("api_key = ?", $api);           
            
            if ($select->query()->rowCount() == 1){
                $id_espectaculo =  $this->getRequest()->getParam('id');           
                $select = $this->_model_espectaculo->select()
                     ->where("id_espectaculo = ?", $id_espectaculo);      
                
                if($select->query()->rowCount() == 1){

                    $select = $this->_model_sesion->select()
                            ->where("id_espectaculo = ?", $id_espectaculo);

                    $lista_eventos = $select->query()->fetchAll();

                    $this->_helper->json($lista_eventos);
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
        
        if (isset($_POST["fecha_hora_inicio"]) && isset($_POST["duracion"]) && isset($_POST["capacidad"])
                && isset($_POST["precio"])&& isset($_POST["iva"])&& isset($_POST["id_espectaculo"])
                && isset($_POST["api_key"]))
        {
                $api = $this->getRequest()->getParam('api_key');
                $select = $this->_model_usuarios->select()
                         ->where("api_key = ?", $api);
                $array = $select->query()->fetchAll();

            if ($select->query()->rowCount() == 1){
                 
                $id_espectaculo = $this->getRequest()->getParam('id_espectaculo');               
                $select = $this->_model_espectaculo->select()
                         ->where("id_espectaculo = ?", $id_espectaculo);
                $array = $select->query()->fetchAll();
                
                if ($select->query()->rowCount() == 1){                 
                
                    $fechaInicio = $this->getRequest()->getParam('fecha_hora_inicio');
                    $duracion = $this->getRequest()->getParam('duracion');
                    $capacidad = $this->getRequest()->getParam('capacidad');
                    $precio = $this->getRequest()->getParam('precio');
                    $iva = $this->getRequest()->getParam('iva');

                    $validator = new Zend_Validate_Date('YYYY-MM-DD hh:mm');
                    if ($validator->isValid($fechaInicio)){                                    
                        if(is_numeric($capacidad)){                                 
                            if(is_numeric($precio)){
                                if (is_numeric($iva)){

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

                                        $app_list = $this->_model_sesion->insert($fila);

                                         //recupero el id del espectaculo
                                         $select = $this->_model_sesion->select()
                                                ->where("fecha_hora_inicio = ?", $fechaInicio)
                                                ->where('id_espectaculo = ?', $id_espectaculo);
                                         $array = $select->query()->fetchAll();

                                         //JSON devuelta
                                         $arrayDevuelta = array(
                                            'id_sesion' =>  $array[0]["id_sesion"],
                                            'guardado' =>  "OK",                                   
                                        );

                                        $this->_helper->json($arrayDevuelta);
                                }else{
                                    $this->_helper->json("El IVA es un float");
                                }
                            }else{
                            $this->_helper->json("El PRECIO es un float!");
                            }
                        }else{
                            $this->_helper->json("La CAPACIDAD es un numero entero");
                        }
                  }else{
                        $this->_helper->json("FECHA invalida...Ej: (YYYY-MM-dd HH:mm:ss)");
                  } 
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
    
    
}

