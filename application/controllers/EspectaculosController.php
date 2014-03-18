<?php

class EspectaculosController extends My_Controller_Action
{
     private $_model_espectaculos;
     private $_model_usuarios;
     

     
     public function init()
    {
        $this->_model_espectaculos = new Application_Model_Espectaculo();
        $this->_model_usuarios = new Application_Model_Usuario();
    }
    
    // Handle GET and return a list of resources
    public function indexAction(){    
        $api = $this->getRequest()->getParam('apikey');
        
        if($api)
        {
            $select = $this->_model_usuarios->select()
                    ->where("api_key = ?", $api)
                    ->where("activo = ?", 1);
            $resultado = $this->_model_usuarios->fetchAll($select)->toArray();
            if ($resultado){

             $select = $this->_model_espectaculos->select()
                     ->where("id_usuario = ?", $resultado[0]["id_usuario"]);
             
             $this->_helper->json($select->query()->fetchAll());
            // return $app_list;
            }else{
                 $this->_helper->json("Error con la apikey");
            }
        }else{
           $this->_helper->json("Falta la apikey");
        }     
        
    }

    // Handle GET and return a specific resource item
    public function getAction(){
        $api = $this->getRequest()->getParam('apikey');
        $titulo =  $this->getRequest()->getParam('titulo');
        if ($api && $titulo)
        {
            $select = $this->_model_usuarios->select()
                     ->where("api_key = ?", $api)
                     ->where("activo = ?", 1);

            $resultado = $this->_model_usuarios->fetchAll($select)->toArray();
            if ($resultado){

               $select = $this->_model_espectaculos->select()
                     ->where("id_usuario = ?", $resultado[0]["id_usuario"])
                     ->where("titulo = ?", $titulo);

             $this->_helper->json($select->query()->fetchAll());
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
        $titulo = $this->getRequest()->getParam('titulo');
        $descripcion = $this->getRequest()->getParam('descripcion');
        $url = $this->getRequest()->getParam('url');
        
        if ($api && $titulo && $descripcion && $url)
        {
            
            $select = $this->_model_usuarios->select()
                     ->where("api_key = ?", $api);
            
            $respuesta = $this->_model_usuarios->fetchAll($select)->toArray();

            if ($respuesta && $this->validarEspectaculos($titulo, $descripcion, $url) != FALSE){
                
                ///Insertamos un Espectaculo///
                $fila = array(
                    'titulo' =>  $titulo,
                    'descripcion' =>  $descripcion,
                    'url' => $url,
                    'activo' => 1,
                    'id_usuario' => $array[0]["id_usuario"],                                        
                );

                 $app_list = $this->_model_espectaculos->insert($fila);

                 //JSON devuelta
                 $arrayDevuelta = array(
                    'id_espectaculo' =>  $app_list[0]["id_espectaculo"],
                    'titulo' =>  $titulo,                                   
                );

                $this->_helper->json($arrayDevuelta);

           }else{
                $this->_helper->json("Error con la api key");
                }
        }else{
            $this->_helper->json("Falta mandar datos!!!");
        }
    }

    // Handle PUT requests to update a specific resource item
    public function putAction(){
        $this->_helper->json("Esta funcion aun no esta implementada");
    }

    // Handle DELETE requests to delete a specific item
    public function deleteAction(){
        $this->_helper->json("Esta funcion aun no esta implementada");
    }
    
    
     private function validar_web ($url){
        if (strlen($url)>0){
            return (preg_match('/^[http:\/\/|www.|https:\/\/]/i', $url));
        }
        return FALSE;
    }
    
    protected function validar_titulo ($nombre){
        return $this->resultado(preg_match('/^[a-z\d_]{4,100}$/i', $nombre));
    }
    
        protected function validar_descripcion ($nombre){
        return $this->resultado(preg_match('/^[a-z\d_]{4,200}$/i', $nombre));
    }
    
    private function resultado($valor){
        if ($valor){
             return TRUE;

        }else{
            return FALSE;
        }
    }
    
    protected function validarEspectaculos($titulo,$descripcion,$url){
        if($this->validar_web($url)){
            if(is_string($titulo) && !empty($titulo)){
                $select = $this->_model_espectaculos->select()
                               ->where("titulo = ?", $titulo)
                               ->where("id_usuario = ?", $array[0]["id_usuario"]);
                if($select->query()->rowCount() == 0){
                    if (is_string($descripcion) && !empty($descripcion)){
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
}

