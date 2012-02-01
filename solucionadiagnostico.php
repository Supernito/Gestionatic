<?php
   include 'db.conf';
   include 'wrappers.php';
 
   cabecera("Agregar una solucion a un diagnostico");
	session_start(); 
   mysql_connect(dbhost,dbuser,dbpass); 
   mysql_select_db(dbname);
	$id_diag= $_GET['id'];
 
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

	$nombre=$_POST['nombre'];

	$query = mysql_query("SELECT id FROM $dbname.solucion WHERE nombre='$nombre'");
	if (mysql_numrows($query)==0){//no se ha encontrado la solución con ese nombre
		echo "No se ha añadido ninguna solución.<br>";
   	echo "<a href='./gincidencias.php'>Volver al inicio</a><br>";
		pie();
		die();
	}
   $row = mysql_fetch_array($query);
	$id_sol=$row['id'];
	$query = mysql_query("INSERT INTO $dbname.diag_sol (id_diag, id_sol)
								VALUES ('$id_diag', '$id_sol')");

   mysql_close();
   echo "Solución agregada con éxito al diagnóstico<br>";
   echo "<a href='./gincidencias.php'>Volver al inicio</a>";
 
   } else {
 
      // Formulario 
   	echo "<form method='post' action='$PHP_SELF'>
      	<p>Solución <select size='1' name='Solución'>
			<option selected value='Sin solución'>Sin solución</option>";
		$result=mysql_query("SELECT nombre FROM $dbname.solucion");
		while ($row=mysql_fetch_array($result)){
			$nombre=$row['nombre'];
			echo "<option value='$nombre'>$nombre</option>";
		}
		echo "</select></p>
      <p><input type='submit' value='enviar' name='enviar'>
   </form>";
 

	}
	
   pie();
?>
