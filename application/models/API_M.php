<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class API_M extends CI_Model{


//INICIAR SESION
    public function IniciarSesionEntrenador(){
        $email_user = $this->input->get('user');
        $pass_encrip = hash('whirlpool',$this->input->get('password'));

        $query_email = $this->db->query('SELECT * FROM cuenta WHERE Email="'.$email_user.'" AND Contrasenia="'.$pass_encrip.'" AND Rol="Entrenador"');
        if($query_email->num_rows() > 0){
            $Resultados = $query_email->result();
            $Result_email;
            $Result_pass;
            $Result_IdCuenta;
            $Result_IdPersona;

            foreach($Resultados as $result){
                $Result_email = $result->Email;
                $Result_pass = $result->Contrasenia;
                $Result_IdCuenta = $result->Id_Cuenta;
                $Result_IdPersona = $result->Id_Persona;
            }

            if($email_user==$Result_email & $pass_encrip==$Result_pass){
                $query = $this->db->query('SELECT cuenta.*  FROM entrenador,cuenta,direccion WHERE '.
                    'cuenta.Id_Cuenta='.$Result_IdCuenta.'
                    AND cuenta.Id_Persona=entrenador.Id_Entrenador
                    AND direccion.Id_Persona=entrenador.Id_Entrenador
                    AND direccion.Tabla_persona="Entrenador"');

                    //return $query->result();
                        $data = array(
                            'Id_Cuenta' => $Result_IdCuenta,
                            'Email' => $Result_email,
                            'Contrasenia' => $Result_pass,
                            'Rol' => 'Entrenador',
                            'Id_Persona' => $Result_IdPersona
                        );
                        echo json_encode($data);
            }else{
                return false;
            }
        }else{
            return false;
        }
        
    }


//VERIFICAR EXISTENCIA DE EMAIL ANTES DE REGISTRAR
    public function ValidarEmail(){
        $email = $this->input->get('Email');
        $BuscarEmail = $this->db->query('SELECT * FROM cuenta WHERE Email="'.$email.'"');
        if($BuscarEmail->num_rows() > 0){
            return true;
        }else{
            return false;
        }
    }


//REGITRAR NUEVO ENTRENADOR
    public function NuevoEntrenador(){
        function generateToken($length = 8) {//generador de token para identificar el usuario
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@%$#=-!"), 0, $length);
        }

        $token=generateToken();//solicitar un token para el nuevo registro
        $datosEntrenador = array(//carrgar en un array los datos a registra
			'Nombre'=>$this->input->get('Nombre'),
            'Apellidos'=>$this->input->get('Apellidos'),
            'Sexo'=>$this->input->get('Sexo'),
            'Club'=>$this->input->get('Club'),
            'Email'=>$this->input->get('Email'),
            'Telefono'=>$this->input->get('Telefono'),
            'Token'=>$token
            );
        $this->db->insert('entrenador', $datosEntrenador);//mandar los datos a BD
		if($this->db->affected_rows() > 0){//verificar si se realizo el registro en la tabla entrenador
            //obtener el ID que le fue asignado al entrenador identificandolo por el token generado
            $query_user = $this->db->query('SELECT Id_Entrenador FROM entrenador WHERE Token="'.$token.'"');
            $id_P = $query_user->result();//asigar el resultado a una varible
            $id_x;//variable que guardara el ID del nuevo entrenador
            foreach($id_P as $id_persona){
                $id_x = $id_persona->Id_Entrenador;//asignar el ID a la varible
            }
            //Encriptar la contraseña utilizando el metodo whirlpool
            $pass_encrip = hash('whirlpool',$this->input->get('Contrasenia'));
            $rol = 'entrenador';//Constante del metodo Registra un entrenador
            $datosCuenta = array(//cargar en un array los datos de la nueva cuenta
                'Email'=>$this->input->get('Email'),
                'Contrasenia'=>$pass_encrip,
                'Rol'=>$rol,
                'Id_Persona'=>$id_x
                );
            $this->db->insert('cuenta', $datosCuenta);//insertar los datos a la tabla cuenta
            if($this->db->affected_rows() > 0){ //verificar Si se registro los datos a la tabla cuenta
                $DireccionEntrenador = array(//cargar en un array los datos a registrar
                    'Calle'=>$this->input->get('Calle'),
                    'Colonia'=>$this->input->get('Colonia'),
                    'N_E'=>$this->input->get('N_E'),
                    'N_I'=>$this->input->get('N_I'),
                    'Municipio'=>$this->input->get('Municipio'),
                    'Estado'=>$this->input->get('Estado'),
                    'Id_Persona'=>$id_x,
                    'Tabla_persona'=>"Entrenador",
                );
                $this->db->insert('direccion', $DireccionEntrenador);
                if($this->db->affected_rows() > 0){
                    $arr = array('titulo' => 'Busqueda de Email',
                        'Mensaje' => 'Registro Exitoso ahora puedes iniciar sesión',
                        'sugerencia' => 'Inicia Sesion'
                    );
            echo json_encode($arr);
                }else{//borrar los datos de cuenta y de la tabla entrenador
                    $Delete_Cuenta = $this->db->query('DELETE FROM playingtenis.cuenta WHERE  Id_Persona='.$id_x.'AND Rol="Entrenador"');
                    $Delete_Entrenador = $this->db->query('DELETE FROM playingtenis.entrenador WHERE Token="'.$token.'"');
                    
                    $arr = array('titulo' => 'Error de Registro',
                        'Mensaje' => 'Ocurrio un error intenta de nuevo',
                        'sugerencia' => 'Intenta de nuevo'
                    );
                    echo json_encode($arr);
                }
            }else{//si no se registro hay que eliminar el registro del entrenador
               $del_Entrenador = $this->db->query('DELETE FROM playingtenis.entrenador WHERE Token="'.$token.'"');
                $arr = array('titulo' => 'Error de Registro',
                    'Mensaje' => 'Ocurrio un error intenta de nuevo',
                    'sugerencia' => 'Intenta de nuevo'
                );
                echo json_encode($arr);
            }
		}else{
            $arr = array('titulo' => 'Error de Registro',
                'Mensaje' => 'Ocurrio un error intenta de nuevo',
                'sugerencia' => 'Intenta de nuevo'
            );
            echo json_encode($arr);
        }
    }


/*//REGISTRAR NUVO JUGADOR
http://192.168.0.104/APlayingTenis/API/NuevoJugador/58/NombreJ/ApellidosJ/ManoJ/1997-07-15/EmailJ/TelefonoJ/SexoJ/CalleJ/ColoniaJ/10/10/MunicipioJ/EstadoJ/NombreF/ApellidoF/SexoF/ParentescoF/Tel_casaF/Tel_CelularF/CalleF/ColoniaF/5/5/MunicipioF/EstadoF*/
    public function NuevoJugador(){
        function generateToken($length = 8) {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@%$#=-!"), 0, $length);
        }
        $token=generateToken();
        $ID_ENTRENADOR=$this->input->get('ID_ENTRENADOR');;
        $datosJugador = array(
            'Nombre' => $this->input->get('NombreJ'),
            'Apellidos' => $this->input->get('ApellidosJ'),
            'Mano' => $this->input->get('ManoJ'),
            'F_Nac' => $this->input->get('F_NacJ'),
            'Email' => $this->input->get('EmailJ'),
            'Telefono' => $this->input->get('TelefonoJ'),
            'Sexo' => $this->input->get('SexoJ'),
            'Token' => $token
        );
        $this->db->insert('jugador',$datosJugador);
        if($this->db->affected_rows()>0){
            $get_idJugador = $this->db->query('SELECT Id_Jugador FROM jugador WHERE Token="'.$token.'"');
            $id_P = $get_idJugador->result();
            $idjugador;
            foreach($id_P as $id_persona){
                $idjugador = $id_persona->Id_Jugador;
            }
            $entrenador_jugador= array(
                'Id_Entrenador'=>$this->input->get('ID_ENTRENADOR'),
                'Id_Jugador' => $idjugador
            );
            $direccion_jugador = array(
                'Calle' => $this->input->get('CalleJ'),
                'Colonia' => $this->input->get('ColoniaJ'),
                'N_E' => $this->input->get('N_EJ'),
                'N_I' => $this->input->get('N_IJ'),
                'Municipio' => $this->input->get('MunicipioJ'),
                'Estado' => $this->input->get('EstadoJ'),
                'Id_Persona' => $idjugador,
                'Tabla_persona' => "Jugador"
            );
            $this->db->insert('entrenador_jugador', $entrenador_jugador);
            $this->db->insert('direccion', $direccion_jugador);
            if($this->db->affected_rows()>0){
                $tokenFam = generateToken();
                
                $datosFamiliar = array(
                    'Nombre' => $this->input->get('NombreF'),
                    'Apellido' => $this->input->get('ApellidoF'),
                    'Sexo' => $this->input->get('SexoF'),
                    'Parentesco' => $this->input->get('ParentescoF'),
                    'Tel_casa' => $this->input->get('Tel_casaF'),
                    'Tel_Celular' => $this->input->get('Tel_CelularF'),
                    'Token' => $tokenFam
                );
                $this->db->insert('familiar',$datosFamiliar);
                if($this->db->affected_rows()>0){

                    $get_idJugador = $this->db->query('SELECT Id_Familiar FROM familiar WHERE Token="'.$tokenFam.'"');
                    $id_P = $get_idJugador->result();
                    $idfamiliar;
                    foreach($id_P as $id_persona){
                        $idfamiliar = $id_persona->Id_Familiar;
                    }
                    $jugador_familiar= array(
                        'Id_Jugador'=>$idjugador,
                        'Id_Familiar' => $idfamiliar
                    );
                    $direccion_familiar = array(
                        'Calle' => $this->input->get('CalleF'),
                        'Colonia' => $this->input->get('ColoniaF'),
                        'N_E' => $this->input->get('N_EF'),
                        'N_I' => $this->input->get('N_IF'),
                        'Municipio' => $this->input->get('MunicipioF'),
                        'Estado' => $this->input->get('EstadoF'),
                        'Id_Persona' => $idfamiliar,
                        'Tabla_persona' => "Familiar"
                    );
                    $this->db->insert('jugador_familiar', $jugador_familiar);
                    $this->db->insert('direccion', $direccion_familiar);

                    if($this->db->affected_rows()>0){
                        $arr = array('titulo' => 'Registro de Jugador',
                        'Mensaje' => 'Registro Exitoso',
                        'sugerencia' => 'Ninguna'
                        );
                        echo json_encode($arr);
                    }else{
                        //eiminar familiar, direccion jugador y jugador, mensaje de erro
                    }
                }else{
                    //eliminar dirección del jugador, eliminar jugador y mensaje de error
               }
            }else{
                //eliminar jugador y mensaje de error
            }
        }else{
            //mensaje de error
        }        
    }


    public function ListaJugadoresEntrenador($id_entrenador){
        $get_idJugador = $this->db->query('SELECT * FROM jugador,entrenador_jugador WHERE jugador.Id_Jugador='.'entrenador_jugador.Id_Jugador AND entrenador_jugador.Id_Entrenador='.$id_entrenador);
        $id_P = $get_idJugador->result();
        echo json_encode($get_idJugador->result());
    }

    public function getDireccion($id_jugador,$rol){
        $get_idJugador = $this->db->query("SELECT * FROM direccion WHERE Id_Persona = "
            .$id_jugador." AND Tabla_persona='".$rol."'");
        $Id_Direccion;$Calle;$Colonia;$N_E;$N_I;$Municipio;$Estado;$Id_Persona;$Tabla_persona;
        foreach($get_idJugador->result() as $dato){
            $Id_Direccion=$dato->Id_Direccion;
            $Calle=$dato->Calle;
            $Colonia=$dato->Colonia;
            $N_E=$dato->N_E;
            $N_I=$dato->N_I;
            $Municipio=$dato->Municipio;
            $Estado=$dato->Estado;
            $Id_Persona=$dato->Id_Persona;
            $Tabla_persona=$dato->Tabla_persona;
        }
        $json = array(
            'Id_Direccion'=>$Id_Direccion,
            'Calle'=>$Calle,
            'Colonia'=>$Colonia,
            'N_E'=>$N_E,
            'N_I'=>$N_I,
            'Municipio'=>$Municipio,
            'Estado'=>$Estado,
            'Id_Persona'=>$Id_Persona,
            'Tabla_persona'=>$Tabla_persona
        );

        echo json_encode($json);
    }

    public function getFamiliar($id_jugador){
        $get_idJugador = $this->db->query("SELECT * FROM familiar,jugador_familiar WHERE familiar.Id_Familiar=".
            "jugador_familiar.Id_Familiar AND jugador_familiar.Id_Jugador=".$id_jugador);
        $Id_Familiar;$Nombre;$Apellido;$Sexo;$Parentesco;$Tel_casa;$Tel_Celular;$Token;
        foreach($get_idJugador->result() as $dato){
            $Id_Familiar=$dato->Id_Familiar;
            $Nombre=$dato->Nombre;
            $Apellido=$dato->Apellido;
            $Sexo=$dato->Sexo;
            $Parentesco=$dato->Parentesco;
            $Tel_casa=$dato->Tel_casa;
            $Tel_Celular=$dato->Tel_Celular;
            $Token=$dato->Token;
        }
        $json = array(
            'Id_Familiar'=>$Id_Familiar,
            'Nombre'=>$Nombre,
            'Apellido'=>$Apellido,
            'Sexo'=>$Sexo,
            'Parentesco'=>$Parentesco,
            'Tel_casa'=>$Tel_casa,
            'Tel_Celular'=>$Tel_Celular,
            'Token'=>$Token
        );

        echo json_encode($json);
    }

    public function updateJugador(){
        $ID_JUGADOR=$this->input->get('id_jugador');

        $datosJugador = array(
            'Nombre' => $this->input->get('NombreJ'),
            'Apellidos' => $this->input->get('ApellidosJ'),
            'Mano' => $this->input->get('ManoJ'),
            'F_Nac' => $this->input->get('F_NacJ'),
            'Email' => $this->input->get('EmailJ'),
            'Telefono' => $this->input->get('TelefonoJ'),
            'Sexo' => $this->input->get('SexoJ'));

        $this->db->where('Id_Jugador', $ID_JUGADOR);
        $this->db->update('jugador', $datosJugador);
        
        if($this->db->affected_rows()>=0){
            $arr = array(
                'titulo' => 'Actualizacion del Jugador',
                'Mensaje' => 'Actualizacion Exitosa',
                'sugerencia' => 'Ninguna'
            );
            echo json_encode($arr);
        }else{
            $arr = array(
                'titulo' => 'Actualizacion del Jugador',
                'Mensaje' => 'Actualizacion Fallida',
                'sugerencia' => 'Intentar de nuevo'
            );
            echo json_encode($arr);
        }
    }


    public function updateDireccion(){
        $ID_DIRECCION=$this->input->get('id_direccion');
        $direccion = array(
            'Calle' => $this->input->get('Calle'),
            'Colonia' => $this->input->get('Colonia'),
            'N_E' => $this->input->get('N_E'),
            'N_I' => $this->input->get('N_I'),
            'Municipio' => $this->input->get('Municipio'),
            'Estado' => $this->input->get('Estado'));

        $this->db->where('Id_Direccion', $ID_DIRECCION);
        $this->db->update('direccion', $direccion);
        
        if($this->db->affected_rows()>=0){
            $arr = array(
                'titulo' => 'Actualizacion del Jugador',
                'Mensaje' => 'Actualizacion Exitosa',
                'sugerencia' => 'Ninguna'
            );
            echo json_encode($arr);
        }else{
            $arr = array(
                'titulo' => 'Actualizacion del Jugador',
                'Mensaje' => 'Actualizacion Fallida',
                'sugerencia' => 'Intentar de nuevo'
            );
            echo json_encode($arr);
        }
    }


    public function updateFamiliar(){
        $ID_FAMILIAR=$this->input->get('id_familiar');

        $datosFamiliar = array(
            'Nombre' => $this->input->get('Nombre'),
            'Apellido' => $this->input->get('Apellido'),
            'Sexo' => $this->input->get('Sexo'),
            'Parentesco' => $this->input->get('Parentesco'),
            'Tel_casa' => $this->input->get('Tel_casa'),
            'Tel_Celular' => $this->input->get('Tel_Celular'));

        $this->db->where('Id_Familiar', $ID_FAMILIAR);
        $this->db->update('familiar', $datosFamiliar);        
        if($this->db->affected_rows()>=0){
            $arr = array(
                'titulo' => 'Actualizacion del Jugador',
                'Mensaje' => 'Actualizacion Exitosa',
                'sugerencia' => 'Ninguna'
            );
            echo json_encode($arr);
        }else{
            $arr = array(
                'titulo' => 'Actualizacion del Jugador',
                'Mensaje' => 'Actualizacion Fallida',
                'sugerencia' => 'Intentar de nuevo'
            );
            echo json_encode($arr);
        }
    }

    public function EliminarJugador($id_eliminar){
        $id_familiar;
        $getIdFamiliar = $this->db->query("SELECT * FROM familiar,jugador_familiar WHERE familiar.Id_Familiar=jugador_familiar.Id_Familiar AND jugador_familiar.Id_Jugador=".$id_eliminar);
        
        foreach($getIdFamiliar->result() as $familia){
            $id_familiar = $familia->Id_Familiar;          
        }

        $this->db->where('Id_Jugador', $id_eliminar);
        $this->db->delete('jugador_familiar');
        if($this->db->affected_rows()>=0){
            $this->db->where('Id_Persona', $id_familiar);
            $this->db->where('Tabla_persona', 'Familiar');
            $this->db->delete('direccion');
            if($this->db->affected_rows()>=0){
                $this->db->where('Id_Persona', $id_eliminar);
                $this->db->where('Tabla_persona', 'Jugador');
                $this->db->delete('direccion');
                if($this->db->affected_rows()>=0){
                    $this->db->where('Id_Familiar', $id_familiar);
                    $this->db->delete('familiar');
                    if($this->db->affected_rows()>=0){

                        $this->db->where('Id_Jugador', $id_eliminar);
                        $this->db->delete('entrenador_jugador');

                        $this->db->where('Id_Jugador', $id_eliminar);
                        $this->db->delete('jugador');
                        if($this->db->affected_rows()>=0){
                            $arr = array(
                                'titulo' => 'Eliminar Datos',
                                'Mensaje' => 'Se ha eliminado los registros',
                                'sugerencia' => 'Intentar de nuevo'
                            );
                            echo json_encode($arr);
                        }else{
                            $arr = array(
                                'titulo' => 'Eliminar Datos',
                                'Mensaje' => 'Error',
                                'sugerencia' => 'Intentar de nuevo'
                            );
                            echo json_encode($arr); 
                        }
                    }else{
                        $arr = array(
                            'titulo' => 'Eliminar Datos',
                            'Mensaje' => 'Error',
                            'sugerencia' => 'Intentar de nuevo'
                    );
            echo json_encode($arr); 
                    }
                }else{
                    $arr = array(
                        'titulo' => 'Eliminar Datos',
                        'Mensaje' => 'Error',
                        'sugerencia' => 'Intentar de nuevo'
                    );
                    echo json_encode($arr);
                }
            }else{
                $arr = array(
                    'titulo' => 'Eliminar Datos',
                    'Mensaje' => 'Error',
                    'sugerencia' => 'Intentar de nuevo'
                );
                echo json_encode($arr); 
            }
        }else{
            $arr = array(
                'titulo' => 'Eliminar Datos',
                'Mensaje' => 'Error',
                'sugerencia' => 'Intentar de nuevo'
            );
            echo json_encode($arr);  
        }
    }

}?>