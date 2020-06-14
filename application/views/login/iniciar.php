<form action="<?php echo base_url('API/IniciarSesionEntrenador') ?>" method="POST">
		<div>
			<label>usuario o email</label>
			<div>
				<input type="text" name="usuario" required>
			</div>
		</div>
        <div>
			<label>Contrasenia</label>
			<div>
				<input type="text" name="Contrasenia" required>
			</div>
		</div>
		<div>
			<label>rol</label>
			<div>
				<input type="text" name="rol" required>
			</div>
		</div>
		<div>
			<label class="col-md-2 text-right"></label>
			<div>
				<input type="submit" name="btnSave" class="btn btn-primary" value="Save">
			</div>
		</div>
	</form>
	
