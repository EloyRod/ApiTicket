<?php

class UsuariosController extends My_Controller_Action
{
    
    private $_model_usuario;

    public function init()
    {
        parent::init();
        $this->_model_usuario = new Application_Model_Usuario();
  
    }

    
    
    public function indexAction(){
        
        $usuario = $this->getRequest()->getParam('usuario');
        $password = $this->getRequest()->getParam('password');
        $dni = $this->getRequest()->getParam('dni');
        $telefono = $this->getRequest()->getParam('telefono');
        $email = $this->getRequest()->getParam('email');
        $nombre_empresa = $this->getRequest()->getParam('nombre_empresa');
        $cif = $this->getRequest()->getParam('cif');
        
        
        $validator = new Zend_Validate_EmailAddress();

            $select = $this->_model_usuario->select()
                      ->where("email = ?", $email);

        if ($usuario && $password && $dni && $telefono && $email && $nombre_empresa && $cif)
        {
           
            $validator = new Zend_Validate_EmailAddress();

            $select = $this->_model_usuario->select()
                      ->where("email = ?", $email);

            $resultado = $this->_model_usuario->fetchAll($select)->toArray();

            if (!$resultado && $this->validar($usuario, $dni, $telefono, $email, $nombre_empresa,$cif) != FALSE){
                
                $api = $this->devuelveApiKey();

                    ///Insertamos un Usuario///
                    $fila = array(
                        'api_key' =>  $api,
                        'usuario' =>  $usuario,
                        'password' => $password,
                        'dni' => $dni,
                        'telefono' => $telefono,
                        'email' => $email,
                        'activo' => 1,
                        'nombre_empresa' => $nombre_empresa,
                        'cif' => $cif          
                    );

                     $app_list = $this->_model_usuario->insert($fila);


                     $arrayDevuelta = array(
                        'api_key' =>  $api,
                        'usuario' =>  $usuario,
                    );

                    $this->mandarEmail($api, $usuario, $password);
                    $this->_helper->json($arrayDevuelta);

           }else{
            $this->_helper->json("Datos enviados erroneos!");
            }
        }else{
            $this->_helper->json("Falta mandar datos!!!");
        }      
                
    }
    
    public function testAction(){
        /// Para hacer los test de Usuarios nuevos!
    }
    
    protected function devuelveApiKey(){
        //Calculo aleatoriamente el api key que me tienen que pasar
        //para hacer una peticion al servidor.
        do{
            $api =sha1(rand(0,999).rand(999,9999).rand(1,300));
            $_model_usuario = new Application_Model_Usuario();
            $select = $_model_usuario->select()
                      ->where("api_key = ?", $api);
        }while(!$select->query()->rowCount() == 0);

        return $api; 
    }
       
    protected function mandarEmail($api,$user,$pass){
        //Configuración SMTP
        $host = 'smtp.gmail.com';
        $param = array(
          'auth' => 'login',
          'ssl' => 'ssl',
          'port' => '465',
          'username' => 'eloyrodgon@gmail.com'
        );
        $tr = new Zend_Mail_Transport_Smtp($host, $param);
        Zend_Mail::setDefaultTransport($tr);
        //Creamos email
        $mail = new Zend_Mail();
        $mail->setFrom('eloyrodgon@gmail.com', 'Eloy Rodriguez');
        $mail->addTo('oliveiras6969@hotmail.com', 'nombre email destino');
        $mail->setSubject('Hola');
        $mail->setBodyText('Este es el contenido del email.');
        $sent = true;
        try {
          $mail->send();
        }
        catch (Exception $e) {
          $sent = false;
        }
        //Devolvemos si hemos tenido éxito
        return $sent;
    }
    
    protected function validar($usuario,$dni,$telefono,$email,$nombre_empresa,$cif){
        $validator = new Zend_Validate_EmailAddress();
      
        if($this->validar_nombre_usuario($usuario) != FALSE){
            if($validator->isValid($email)){                   
                if ($this->validateNif($dni) != FALSE){
                    if ($this->validateCif($cif) != FALSE){
                        if ($this->validar_telefono($telefono) != FALSE){

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
    
    //Validar CIF y DNI//
    // Valida NIFs (DNIs y NIFs especiales)
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
    
    
    // Valida CIFs
    protected function validateCif ($cif /*$check*/) {
            $cif_codes = 'JABCDEFGHI';

            // $cif = array_pop ($check);

            $sum = (string) $this->getCifSum ($cif);
            $n = (10 - substr ($sum, -1)) % 10;

            if (preg_match ('/^[ABCDEFGHJNPQRSUVW]{1}/', $cif)) {
                    if (in_array ($cif[0], array ('A', 'B', 'E', 'H'))) {
                            // Numerico
                            return ($cif[8] == $n);
                    } elseif (in_array ($cif[0], array ('K', 'P', 'Q', 'S'))) {
                            // Letras
                            return ($cif[8] == $cif_codes[$n]);
                    } else {
                            // Alfanumérico
                            if (is_numeric ($cif[8])) {
                                    return ($cif[8] == $n);
                            } else {
                                    return ($cif[8] == $cif_codes[$n]);
                            }
                    }
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

    /*
     * Números telefónicos
     * Esto es para validar números de teléfono españoles sin código de pais es decir: 924870975
    */
    private function validar_telefono ($telefono){
        return $this->resultado(preg_match('/^[0-9]{9,9}$/', $telefono));
    }
  
}

