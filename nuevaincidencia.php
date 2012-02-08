<?php
   include 'db.conf';
   include 'wrappers.php';

   cabecera("Nueva incidencia");

if(isset($_POST['enviar']) && $_SESSION['logged']==true) {

   session_start(); 
   mysql_connect(dbhost,dbuser,dbpass); 
   mysql_select_db(dbname);

   // Miraramos los permisos para gestionar incidencias. Los guardamos en $own
   $query = "SELECT is_admin,g_inc FROM ".dbname.".usuario WHERE username='$_SESSION[username]'";
   $res   = mysql_query($query) or die(mysql_error());
   $own   = mysql_fetch_array($res);

   // Si no tiene permisos morimos
   if ($own[is_admin] == 'false' && $own[g_inc] == 'false'){
      echo "No tienes permisos suficientes.";
      mysql_close;
      pie();die();
   }

   $nombre=$_POST['nombre'];
   $descripcion = mysql_real_escape_string(trim(strip_tags(stripslashes($_POST['descripcion']))));
   $descripcion = str_replace('\\n', '<br>', $descripcion);
   $estado=$_POST['estado'];
   $urgencia=$_POST['urgencia'];
   $nivelescalado=$_POST['nivelescalado'];
   $responsable=$_POST['responsable'];
   $aplicacion=$_POST['aplicacion'];
   $diagnostico=$_POST['diagnostico'];
	$servicio=$_POST['servicio'];
	//comprobamos que el diagnostico exista

       $res = mysql_query("INSERT INTO  $dbname.incidencia (nombre, descripcion, fecha, estado, urgencia, nivelescalado, responsable, aplicacion, diagnostico, servicio)
               VALUES ('$nombre',  '$descripcion',  NOW(), '$estado', '$urgencia',  '$nivelescalado', '$responsable', '$aplicacion', '$diagnostico', '$servicio');") or die(mysql_error());
      mysql_close();
      echo "Incidencia insertada con éxito<br>";
      echo "<a href='./gincidencias.php'>Volver al inicio</a>";

   } else {

      // Formulario para meter piezas
?>
   <form method="post" action="<?php echo $PHP_SELF; ?>" name="formnuevaincidencia" >
      <p>Nombre <input type='text' name='nombre' size='30' value=''></p>
      <p>Descripción <textarea rows="5" name="descripcion" cols="28"></textarea></p>
      <p>Estado <select size="1" name="estado">
			<option selected value="abierta">Abierta</option>
			<option value="escalada">Escalada</option>
			<option value="cerrada">Cerrada</option>
		</select></p>
		<p>Urgencia <select size="1" name="urgencia">
			<option selected value="baja">Baja</option>
			<option value="media">Media</option>
			<option value="alta">Alta</option>
			<option value="critica">Crítica</option>
		</select></p>
		<p>Nivel de escalado <select size="1" name="nivelescalado">
			<option selected value="service desk">Service Desk</option>
			<option value="administracion de redes">Administración de redes</option>
			<option value="desarrolladores y analistas">Desarrolladores y analistas</option>
			<option value="proveedor">Proveedor</option>
		</select></p>
		<p>Aplicacion <input type='text' name='aplicacion' size='30' value=''></p>
		<p>Responsable <select size="1" name="responsable">
			<option selected value="Sin responsable">Sin responsable</option>
			<?php
				mysql_connect(dbhost,dbuser,dbpass); 
   			mysql_select_db(dbname);
				$result=mysql_query("SELECT username FROM $dbname.usuario");
				while ($row=mysql_fetch_array($result)){
					$nombre=$row['username'];
					echo "<option value='$nombre'>$nombre</option>";
				}
				echo "</select></p>";
				echo "<p>Diagnóstico <select size='1' name='daignostico'>
					<option selected value='Sin diagnóstico'>Sin diagnóstico</option>";
				
				$result=mysql_query("SELECT nombre FROM $dbname.diagnostico") or die(mysql_error());
				while ($row=mysql_fetch_array($result)){
					$nombre=$row['nombre'];
					echo "<option value='$nombre'>$nombre</option>";
				}
			?>
		</select></p>
			<p>Servicio <select size="1" name="servicio">
			<option selected value="Sin servicio">Sin servicio</option>
			<?php
				mysql_connect(dbhost,dbuser,dbpass); 
   			mysql_select_db(dbname);
				$result=mysql_query("SELECT id,nombre FROM $dbname.servicio");
				while ($row=mysql_fetch_array($result)){
					$servicio=$row['nombre'];
					$idserv=$row['id'];
					echo "<option value='$idserv'>$servicio</option>";
				}
				echo "</select></p>";
			?>
		</select></p>
      <p><input type='submit' value='enviar' name='enviar'>
		<button type=button onClick="location.href='gincidencias.php'">Cancelar</button>
   </form>
   

<?php
	}
   pie();
?>

	
