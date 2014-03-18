<?php

class EntradaController extends My_Controller_Action
{
     private $_model_sesion;
     private $_model_espectaculo;
     private $_model_usuarios;
     private $_model_entrada;
     private $_model_comprador;
     
     public function init()
    {
        $this->_model_sesion = new Application_Model_Sesion();
        $this->_model_espectaculo = new Application_Model_Espectaculo();
        $this->_model_usuarios = new Application_Model_Usuario();
        $this->_model_entrada = new Application_Model_Entrada(); 
        $this->_model_comprador = new Application_Model_Compradores();
    }
    
    // Handle GET and return a list of resources
    public function indexAction(){   
        
        $api =  $this->getRequest()->getParam('apikey');
        if($api)
        {                                
            $select = $this->_model_usuarios->select()
                     ->where("api_key = ?", $api);           
            $resultado = $this->_model_usuarios->fetchAll($select)->toArray();
            
            if ($resultado){

               $select_sesion = $this->_model_usuarios->select()
                        ->setIntegrityCheck(false)
                        ->join('espectaculo','espectaculo.id_usuario = usuario.id_usuario' )
                        ->join('sesion','espectaculo.id_espectaculo = sesion.id_espectaculo' )
                        ->join('entrada','entrada.id_sesion = sesion.id_sesion')
                        ->join('compradores','compradores.id_comprador = entrada.id_comprador' )
                        ->where('espectaculo.id_usuario = ?' , $resultado[0]["id_usuario"]);
      
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
        $id_sesion =  $this->getRequest()->getParam('sesion');
        if($api && $id_espectaculo && $id_sesion)
        {
           $select = $this->_model_usuarios->select()
                     ->where("api_key = ?", $api);           
            
            if ($select->query()->rowCount() == 1){
                
                $select = $this->_model_espectaculo->select()
                     ->setIntegrityCheck(false)
                     ->join('sesion','espectaculo.id_espectaculo = sesion.id_espectaculo' )
                     ->where("id_espectaculo = ?", $id_espectaculo)
                     ->where("id_sesion = ?", $id_sesion);      
                
                if($select->query()->rowCount() == 1){

                    $select_sesion = $this->_model_usuarios->select()
                        ->setIntegrityCheck(false)
                        ->join('espectaculo','espectaculo.id_usuario = usuario.id_usuario' )
                        ->join('sesion','espectaculo.id_espectaculo = sesion.id_espectaculo' )
                        ->join('entrada','entrada.id_sesion = sesion.id_sesion')
                        ->join('compradores','compradores.id_comprador = entrada.id_comprador' )
                        ->where('sesion.id_espectaculo = ?' , $id_sesion);
        
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
        $id_sesion = $this->getRequest()->getParam('id_sesion');
        $nombre = $this->getRequest()->getParam('nombre');
        $apellidos = $this->getRequest()->getParam('apellidos');
        $dni = $this->getRequest()->getParam('dni');
        $email = $this->getRequest()->getParam('email');
        $telefono = $this->getRequest()->getParam('telefono');
        $numero_entradas = $this->getRequest()->getParam('numero_entradas');
        
        if ($api && $id_espectaculo && $id_sesion && $nombre && $apellidos && $dni && $email && $telefono && $numero_entradas)
        {             
                $select_sesion = $this->_model_sesion->select()
                        ->setIntegrityCheck(false)
                        ->join('espectaculo','espectaculo.id_espectaculo = sesio.id_espectaculo' )
                        ->join('usuario','espectaculo.id_usuario = usuario.id_usuario' )
                        ->where('espectaculo.id_espectaculo = ?' , $id_espectaculo)
                        ->where('usuario.api_key = ?' , $api);
        
               
            if ($select_sesion->query()->rowCount() == 1){
               
                 $select = $this->_model_sesion->select()
                      ->where("id_sesion = ?", $id_sesion)
                      ->where("id_espectaculo = ?", $id_espectaculo);
                
                 if ($select->query()->rowCount() == 1){
                   
                    $array_entradas = $select->query()->fetchAll();
                    $capacidad = $array_entradas[0]["capacidad"];
                    $vendidas = $array_entradas[0]["vendidas"];
                    $total = $capacidad - $vendidas;

                    if(($vendidas +  $numero_entradas) < $capacidad && $this->validarEntrada($nombre, $apellidos, $dni, $telefono, $email) != FALSE){


                        ///Comprobamos si existe el usuario////

                        $select = $this->_model_comprador->select()
                                ->where("email = ?", $email)
                                ->where("dni = ?", $dni);

                        if($select->query()->rowCount() == 1){
                            $array = $select->query()->fetchAll();
                        }else{
                            ///Insertamos un comprador///
                            $fila = array(
                                'nombre' =>  $nombre,
                                'apellidos' =>  $apellidos,
                                'dni' => $dni,
                                'email' => $email,
                                'telefono' => $telefono,                                       
                            );

                            $comprador = $this->_model_comprador->insert($fila);
                               
                        }

                        $todo = array();
                        //Insertamos la entrada//
                        $fecha = new Zend_Date();

                        for($i = 0; $i < $numero_entradas ; $i++){
                            $fila = array(
                                'numero' => $this->updateSesion($id_sesion, $id_espectaculo),
                                'fecha' => $fecha->get('YYYY-MM-dd HH:mm:ss'),
                                'activo' => 1,
                                'id_sesion' => $id_sesion,
                                'id_comprador' => $comprador[0]["id_comprador"],                                       
                            );

                            $entrada = $this->_model_entrada->insert($fila);
                            $todo[$i] = $entrada;
                        }

                        $this->_helper->json($todo);
                  }else{
                        $this->_helper->json("No quedan tantas entradas! solo quedan: " . $total);
                  }
              }else{
                        $this->_helper->json("Error con ID de SESION!");
                  }
              
           }else{
                $this->_helper->json("Error con la api key y/o id de espectaculo!");
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
    
    
    ////Validar CIF y DNI///////

    // Valida NIFs (DNIs y NIFs especiales)
    // El código comentado es para usar en modelos CakePHP
    protected function validateNif ($nif /*$check*/) {
            $nif_codes = 'TRWAGMYFPDXBNJZSQVHLCKE';

            // $nif = strtoupper (array_pop ($check));

            $sum = (string) $this->getCifSum ($nif);
            $n = 10 - substr($sum, -1);

            if (preg_match ('/^[0-9]{8}[A-Z]{1}$/', $nif)) {
                    // DNIs
                    $num = substr($nif, 0, 8);

                    return ($nif[8] == $nif_codes[$num % 23]);
            } elseif (preg_match ('/^[XYZ][0-9]{7}[A-Z]{1}$/', $nif)) {
                    // NIEs normales
                    $tmp = substr ($nif, 1, 7);
                    $tmp = strtr(substr ($nif, 0, 1), 'XYZ', '012') . $tmp;

                    return ($nif[8] == $nif_codes[$tmp % 23]);
            } elseif (preg_match ('/^[KLM]{1}/', $nif)) {
                    // NIFs especiales
                    return ($nif[8] == chr($n + 64));
            } elseif (preg_match ('/^[T]{1}[A-Z0-9]{8}$/', $nif)) {
                    // NIE extraño
                    return true;
            }

            return false;
    }
   

    private function getCifSum ($cif) {
            $sum = $cif[2] + $cif[4] + $cif[6];

            for ($i = 1; $i<8; $i += 2) {
                    $tmp = (string) (2 * $cif[$i]);

                    $tmp = $tmp[0] + ((strlen ($tmp) == 2) ?  $tmp[1] : 0);

                    $sum += $tmp;
            }

            return $sum;
    }
    
    
    
    ////Validar NombreUsuario, Telefono///////
    private function resultado($valor){
        if ($valor){
             return TRUE;
        }else{
            return FALSE;
        }
    }
    
    /*
    Esta regla es para permitir usuarios de 4 hasta 28 caracteres de longitud, alfanuméricos y permitir guiones bajos.
    */
    private function validar_nombre_usuario ($nombre){
        return $this->resultado(preg_match('/^[a-z\d_]{4,28}$/i', $nombre));
    }

    /*Números telefónicos
    Esto es para validar números de teléfono españoles sin código de pais es decir: 924870975
    */
    private function validar_telefono ($telefono){
        return $this->resultado(preg_match('/^[0-9]{9,9}$/', $telefono));
    }

    private function updateSesion($id_sesion,$id_espectaculo){
        $select = $this->_model_sesion->select()
                      ->where("id_sesion = ?", $id_sesion)
                      ->where("id_espectaculo = ?", $id_espectaculo);
        
        $array = $select->query()->fetchAll();
        
        $vendidas = $array[0]["vendidas"];
        $update = $vendidas + 1;
        //Actualizamos las vendidas
        
        $data = array(
            'vendidas'      => $update,
        );
 
        $where = 'id_sesion = ' . $id_sesion;
        $app_list = $this->_model_sesion->update($data, $where );
        
        //Devuelvo el numero de entrada
        return $update;
    }
    
    protected function validarEntrada($nombre,$apellidos,$dni,$telefono,$email){
            if ($this->validar_nombre_usuario($nombre)){                                    
                if($this->validar_nombre_usuario($apellidos)){                                 
                    if($this->validateNif($dni)){
                        if ($this->validar_telefono($telefono)){
                            $validator = new Zend_Validate_EmailAddress();
                            if ($validator->isValid($email)){
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
