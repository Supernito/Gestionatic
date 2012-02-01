<?php
   include 'db.conf';
   include 'wrappers.php';
 
   cabecera("Editar una incidencia");
	session_start(); 
   mysql_connect(dbhost,dbuser,dbpass); 
   mysql_select_db(dbname);
 
if(isset($_POST['enviar']) && $_SESSION['logged']==true) {

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

	$id= $_POST['idincidencia'];

	$query = mysql_query("SELECT * FROM $dbname.incidencia WHERE id='$id'");
   $row = mysql_fetch_array($query);
	$nombre=$_POST['nombre'];
	if ($nombre!=$row['nombre']){
		$res = mysql_query("UPDATE $dbname.incidencia SET nombre = '$nombre' WHERE id = '$id';");
	}
	$descripcion = mysql_real_escape_string(trim(strip_tags(stripslashes($_POST['descripcion']))));
   $descripcion = str_replace('\\n', '<br>', $descripcion);
	if ($descripcion!=$row['descripcion']){
		$res = mysql_query("UPDATE $dbname.incidencia SET descripcion = '$descripcion' WHERE id = '$id';");
	}
	$estado=$_POST['estado'];
	if ($estado!=$row['estado']){
		$res = mysql_query("UPDATE $dbname.incidencia SET estado = '$estado' WHERE id = '$id';");
	}
	$urgencia=$_POST['urgencia'];
	if ($urgencia!=$row['urgencia']){
		$res = mysql_query("UPDATE $dbname.incidencia SET urgencia = '$urgencia' WHERE id = '$id';");
	}
	$nivelescalado=$_POST['nivelescalado'];
	if ($nivelescalado!=$row['nivelescalado']){
		$res = mysql_query("UPDATE $dbname.incidencia SET nivelescalado = '$nivelescalado' WHERE id = '$id';");
	}
	$responsable=$_POST['responsable'];
	if ($responsable!=$row['responsable']){
		$res = mysql_query("UPDATE $dbname.incidencia SET responsable = '$responsable' WHERE id = '$id';");
	}
	$aplicacion=$_POST['aplicacion'];
	if ($aplicacion!=$row['aplicacion']){
		$res = mysql_query("UPDATE $dbname.incidencia SET aplicacion = '$aplicacion' WHERE id = '$id';");
	}
	$diagnostico=$_POST['diagnostico'];
	if ($diagnostico!=$row['diagnostico']){
		$res = mysql_query("UPDATE $dbname.incidencia SET diagnostico = '$diagnostico' WHERE id = '$id';");
	}
 
   mysql_close();
   echo "Incidencia editada con éxito<br>";
   echo "<a href='./gincidencias.php'>Volver al inicio</a>";
 
   } else {
 
      // Formulario para editar incidencia
   $id= $_POST['idincidencia'];

   $query = mysql_query("SELECT * FROM $dbname.incidencia WHERE id='$id'");
   $row = mysql_fetch_array($query);

   $nombre=$row['nombre'];
   $descripcion = $row['descripcion'];
   $estado=$row['estado'];
   $urgencia=$row['urgencia'];
   $nivelescalado=$row['nivelescalado'];
   $responsable=$row['responsable'];
   $aplicacion=$row['aplicacion'];
   $diagnostico=$row['diagnostico'];

   echo "<form method='post' action='$PHP_SELF'>
      <p>Nombre <input type='text' name='nombre' size='30' value='$nombre'></p>
      <p>Descripción <textarea rows='5' name='descripcion' cols='28'>$descripcion</textarea></p>
      <p>Estado <select size='1' name='estado'>
			<option selected value='abierta'>Abierta</option>
			<option value='escalada'>Escalada</option>
			<option value='cerrada'>Cerrada</option>
		</select></p>
		<p>Urgencia <select size='1' name='urgencia'>
			<option selected value='baja'>Baja</option>
			<option value='media'>Media</option>
			<option value='alta'>Alta</option>
			<option value='critica'>Crítica</option>
		</select></p>
		<p>Nivel de escalado <select size='1' name='nivelescalado'>
			<option selected value='service desk'>Service Desk</option>
			<option value='administracion de redes'>Administración de redes</option>
			<option value='desarrolladores y analistas'>Desarrolladores y analistas</option>
			<option value='proveedor'>Proveedor</option>
		</select></p>
		<p>aplicacion <input type='text' name='aplicacion' size='30' value='$aplicacion'></p>
		<p>Responsable <select size='1' name='responsable'>
			<option selected value='Sin responsable'>Sin responsable</option>";
			$result=mysql_query("SELECT username FROM $dbname.usuario") or die(mysql_error());
			while ($row=mysql_fetch_array($result)){
				$nombre=$row['username'];
				echo "<option value='$nombre'>$nombre</option>";
			}
		echo "</select></p>
		<p>diagnostico <select size='1' name='diagnostico'>
			<option selected value='Sin diagnostico'>Sin diagnóstico</option>";
			$result=mysql_query("SELECT nombre FROM $dbname.diagnostico") or die(mysql_error());
			while ($row=mysql_fetch_array($result)){
				$nombre=$row['nombre'];
				echo "<option value='$nombre'>$nombre</option>";
			}
		echo "</select></p>
		<input type='hidden' name='idincidencia'  value='$id'>
      <p><input type='submit' value='enviar' name='enviar'>
		<button type=button onClick=\"location.href='gincidencias.php'\">Cancelar</button></form>";
	}
   pie();
?>
