<?php
   include 'db.conf';
   include 'wrappers.php';
 
   cabecera("Agregar una solucion a un diagnostico");
	session_start(); 
   mysql_connect(dbhost,dbuser,dbpass); 
   mysql_select_db(dbname);
	$id_diag= $_GET['id'];
 
if(isset($_POST['enviar']) && $_SESSION['logged']==true) {
	$nombre=$_POST['nombre'];

	$query = mysql_query("SELECT id FROM $dbname.solucion WHERE nombre='$nombre'");
	if (mysql_numrows($query)==0){//no se ha encontrado la soluci�n con ese nombre
		echo "No se ha a�adido ninguna soluci�n.<br>";
   	echo "<a href='./gincidencias.php'>Volver al inicio</a><br>";
		pie();
		die();
	}
   $row = mysql_fetch_array($query);
	$id_sol=$row['id'];
	$query = mysql_query("INSERT INTO $dbname.diag_sol (id_diag, id_sol)
								VALUES ('$id_diag', '$id_sol')");

   mysql_close();
   echo "Soluci�n agregada con �xito al diagn�stico<br>";
   echo "<a href='./gincidencias.php'>Volver al inicio</a>";
 
   } else {
 
      // Formulario 
   	echo "<form method='post' action='$PHP_SELF'>
      	<p>Soluci�n <select size='1' name='Soluci�n'>
			<option selected value='Sin soluci�n'>Sin soluci�n</option>";
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
