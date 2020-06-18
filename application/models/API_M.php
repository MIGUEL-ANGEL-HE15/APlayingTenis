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
//OBTENER LA LISTA DE JUGADORES DE UN ENTRENADOR
    public function ListaJugadoresEntrenador($id_entrenador){
        $get_idJugador = $this->db->query('SELECT * FROM (SELECT @prmid_entrenador :='.$id_entrenador.' idEntrenador ) ALIAS, listajugadoresfamiliar;');
        $id_P = $get_idJugador->result();
        echo json_encode($get_idJugador->result());
}?>
