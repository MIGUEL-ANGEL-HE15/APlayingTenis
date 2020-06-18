<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class API_M extends CI_Model{
//INICIAR SESION
    public function IniciarSesionEntrenador(){
        $email_user = $this->input->get('user');
        $pass_encrip = hash('whirlpool',$this->input->get('password'));
        $query_email = $this->db->query('SELECT * FROM cuenta WHERE Email="'.$email_user.'" AND Contrasenia="'.$pass_encrip.'" AND Id_Rol=1');
        if($query_email->result()!= NULL){
            if($query_email->result_array()[0]['Email']===$email_user & $query_email->result_array()[0]['Contrasenia'] === $pass_encrip){
                $id=$query_email->result_array()[0]['Id_Persona'];
                $datos = $this->db->query('SELECT * FROM entrenador,direccion,cuenta WHERE entrenador.Id_Entrenador='.$id.' AND direccion.Id_Persona='.$id.' AND direccion.Id_Rol=1 AND cuenta.Id_Cuenta='.$query_email->result_array()[0]['Id_Cuenta']);
                echo json_encode($datos->result_array());
            }
        }else{
            $arr = array(
                'titulo' => 'Registro Cuenta',
                'Mensaje' => 'Usuario y/o Contraseña Incorrectos',
                'sugerencia' => 'Inicia Sesion'
            );
            echo json_encode($arr);
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


 //REGISTRAR UN NUEVO ENTRENADOR
    function InsertEntrenador(){
        $token=$this->GenerarToken();//obtener token
        $id_NuevoEntrenador;
        $pass_encrip = hash('whirlpool',$this->input->get('Contrasenia'));//encriptar pass
        $rol = 1;//numero del rol
        //array de los datos del entrenador
        $datosEntrenador = array(
            'Nombre'=>$this->input->get('Nombre'),
            'Apellidos'=>$this->input->get('Apellidos'),
            'Sexo'=>$this->input->get('Sexo'),
            'Club'=>$this->input->get('Club'),
            'Email'=>$this->input->get('Email'),
            'Telefono'=>$this->input->get('Telefono'),
            'Token'=>$token
            );
        $this->db->trans_begin();//comenzar la transacción
        $this->db->insert('entrenador', $datosEntrenador);//insertar datos del entrenador
        $query_user = $this->db->query('SELECT Id_Entrenador FROM entrenador WHERE Token="'.$token.'"');
        $id_NuevoEntrenador = $query_user->result_array()[0]['Id_Entrenador'];//obtener id del entrenador
        //array de los datos para la tabla cuenta
        $datosCuenta = array(
            'Email'=>$this->input->get('Email'),
            'Contrasenia'=>$pass_encrip,
            'Id_Rol'=>$rol,
            'Id_Persona'=>$id_NuevoEntrenador
        );
        $this->InsertDireccion($this->input->get('Calle'),$this->input->get('Colonia'),$this->input->get('N_E'),$this->input->get('N_I'),$this->input->get('Municipio'),$this->input->get('Estado'),
            $id_NuevoEntrenador,$rol,);
        $this->db->insert('cuenta', $datosCuenta);
        //VERIFICAR LA TRANSACCION
        if($this->db->trans_status()===FALSE){//verificar el estado de la transaccion
            $this->db->trans_rollback();//desahacer todo
        }else{
            $this->db->trans_commit();//terminar la transaccion y guardar
            $arr = array(
                'titulo' => 'Registro Cuenta',
                'Mensaje' => 'Registro Exitoso',
                'sugerencia' => 'Inicia Sesion'
            );
            echo json_encode($arr);//imprimir el mensaje
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

//OBTENER LA LISTA DE JUGADORES DE UN ENTRENADOR
    public function ListaJugadoresEntrenador($id_entrenador){
        $get_idJugador = $this->db->query('SELECT * FROM (SELECT @prmid_entrenador :='.$id_entrenador.' idEntrenador ) ALIAS, listajugadoresfamiliar;');
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