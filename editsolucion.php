<?php
   include 'db.conf';
   include 'wrappers.php';
 
   cabecera("Editar una solucion");
	session_start(); 
   mysql_connect(dbhost,dbuser,dbpass); 
   mysql_select_db(dbname);
 
if(isset($_POST['enviar']) && $_SESSION['logged']==true) {

	$id= $_GET['id'];

	$query = mysql_query("SELECT * FROM $dbname.solucion WHERE id='$id'");
   $row = mysql_fetch_array($query);
	$nombre=$_POST['nombre'];
	if ($nombre!=$row['nombre']){
		$res = mysql_query("UPDATE $dbname.solucion SET nombre = '$nombre' WHERE id = '$id';");
	}
	$descripcion = mysql_real_escape_string(trim(strip_tags(stripslashes($_POST['descripcion']))));
   $descripcion = str_replace('\\n', '<br>', $descripcion);
	if ($descripcion!=$row['descripcion']){
		$res = mysql_query("UPDATE $dbname.solucion SET descripcion = '$descripcion' WHERE id = '$id';");
	}
	
   mysql_close();
   echo "Soluci&oacuten editada con &eacutexito";
   echo "<a href='./gincidencias.php'>Volver al inicio</a>";
 
   } else {
 
      // Formulario para meter piezas
	$id= $_GET['id'];

	$query = mysql_query("SELECT * FROM $dbname.solucion WHERE id='$id'");
   $row = mysql_fetch_array($query);

	$nombre=$row['nombre'];
   $descripcion = $row['descripcion'];

   echo "<form method='post' action='$PHP_SELF'>
      <p>Nombre <input type='text' name='nombre' size='30' value='$nombre'></p>
      <p>Descripci&oacute;n <textarea rows='5' name='descripcion' cols='28'>$descripcion</textarea></p>
      <p><input type='submit' value='enviar' name='enviar'>
		<button type=button onClick=\"location.href='soluciones.php'\">Cancelar</button></form>";

	}
   pie();
?>
