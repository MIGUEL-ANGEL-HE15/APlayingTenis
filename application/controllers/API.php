<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class API extends CI_Controller {

	function __construct(){
		parent:: __construct();
		$this->load->model('API_M', 'm');
    }
	/* ========== VISTAS PARA PRUEBAS EN PC CON FORMULARIOS ======================= */
	public function index()
	{
		$this->load->view('login/iniciar');
	}

    public function IniciarSesionEntrenadorVista()
	{
		$this->load->view('login/iniciar');
	}

    public function NuevoEntrenadorVista()
	{
		$this->load->view('entrenador/Nuevo');
    }
    
    public function EditarEntrenadorVista()
	{
		$this->load->view('entrenador/EditarCuenta');
	}


    /* =============== METODOS QUE SE USARAN EN LA API DE LA APLICACIÓN =============== */
	public function IniciarSesionEntrenador(){
		$datos['usuario'] = $this->m->IniciarSesionEntrenador();
		//echo json_encode($datos);
    }
    
    public function NuevoEntrenador(){
        if($this->m->ValidarEmail()){
        	$arr = array('titulo' => 'Busqueda de Email',
        		'Mensaje' => 'El email ya esta registrado a otra cuenta',
        		'sugerencia' => 'Intenta con otro email'
        	);
            echo json_encode($arr);
        }else{
            $this->m->NuevoEntrenador();
        }
	}

	public function NuevoJugador(){

        $this->m->NuevoJugador();
	}


	public function editarJugadorFamiliar($NombreJ,$ApellidosJ,$ManoJ,$F_NacJ,$EmailJ,$TelefonoJ,$SexoJ,$CalleJ,$ColoniaJ,$N_EJ,$N_IJ,$MunicipioJ,$EstadoJ,$NombreF,$ApellidoF,$SexoF,$ParentescoF,$Tel_casaF,$Tel_CelularF,$CalleF,$ColoniaF,$N_EF,$N_IF,$MunicipioF,$EstadoF,$ID_JUGADOR,$ID_FAMILIAR){
		
		$this->m->EditarJugador($NombreJ,$ApellidosJ,$ManoJ,$F_NacJ,$EmailJ,$TelefonoJ,$SexoJ,$CalleJ,$ColoniaJ,$N_EJ,$N_IJ,$MunicipioJ,$EstadoJ,$NombreF,$ApellidoF,$SexoF,$ParentescoF,$Tel_casaF,$Tel_CelularF,$CalleF,$ColoniaF,$N_EF,$N_IF,$MunicipioF,$EstadoF,$ID_JUGADOR,$ID_FAMILIAR);
	}

	public function ListaJugadores($id_entrenador){
		$this->m->ListaJugadoresEntrenador($id_entrenador);
	}

	public function obetenerDireccion($id_jugador,$rol){
		$this->m->getDireccion($id_jugador,$rol);
	}

	public function obetenerFamiliar($id_jugador){
		$this->m->getFamiliar($id_jugador);
	}

	public function updateJugador(){
		$this->m->updateJugador();	
	}

	public function updateDireccion(){
		$this->m->updateDireccion();	
	}

	public function updateFamiliar(){
		$this->m->updateFamiliar();	
	}

	public function delete($id_eliminar){
		$this->m->EliminarJugador($id_eliminar);	
	}



}


?>