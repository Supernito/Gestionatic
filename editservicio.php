<?php
   include 'db.conf';
   include 'wrappers.php';
 
   cabecera("Editar un servicio");
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

	$id= $_POST['servicio'];

	$query = mysql_query("SELECT * FROM $dbname.servicio WHERE id='$id'");
   $row = mysql_fetch_array($query);
	$nombre=$_POST['nombre'];
	if ($nombre!=$row['nombre']){
		$res = mysql_query("UPDATE $dbname.servicio SET nombre = '$nombre' WHERE id = '$id';");
	}
	$descripcion = mysql_real_escape_string(trim(strip_tags(stripslashes($_POST['descripcion']))));
   $descripcion = str_replace('\\n', '<br>', $descripcion);
	if ($descripcion!=$row['descripcion']){
		$res = mysql_query("UPDATE $dbname.servicio SET descripcion = '$descripcion' WHERE id = '$id';");
	}
   mysql_close();
   echo "Servicio editado con éxito<br>";
   echo "<a href='./gservicios.php'>Volver al inicio</a>";
 
   } else {
 
      // Formulario para editar servicio
     $id= $_POST['servicio'];

   $query = mysql_query("SELECT * FROM $dbname.servicio WHERE id='$id'");
   $row = mysql_fetch_array($query);

   $nombre=$row['nombre'];
   $descripcion = $row['descripcion'];
   

   echo "<form method='post' action='$PHP_SELF'>
      <p>Nombre <input type='text' name='nombre' size='30' value='$nombre'></p>
      <p>Descripción <textarea rows='5' name='descripcion' cols='28'>$descripcion</textarea></p>
		<input type='hidden' name='servicio'  value='$id'>
      <p><input type='submit' value='enviar' name='enviar'>
		<button type=button onClick=\"location.href='gservicios.php'\">Cancelar</button></form>";
	}
   pie();
?>