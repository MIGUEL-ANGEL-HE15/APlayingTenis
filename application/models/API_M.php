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

}?>
