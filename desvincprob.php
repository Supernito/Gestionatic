<?php
 
   include 'db.conf';
   include 'wrappers.php';

   cabecera("Eliminar solucion");

   session_start(); 
   mysql_connect(dbhost,dbuser,dbpass); 
   mysql_select_db(dbname);
   $desvinc= $_POST['incidencia'];
   $loggedu = $_SESSION['username'];
   $query = mysql_query("SELECT is_admin FROM $dbname.usuario WHERE username='$loggedu'");
   $row = mysql_fetch_array($query);
   $isadmin = $row['is_admin'];
   if ($isadmin){
      mysql_query("UPDATE $dbname.incidencia SET problema = NULL WHERE id = '$desvinc';");
      echo "Incidencia desvinculada del problema";
		echo "<button type='button' onClick=\"location.href='gincidencias.php'\">Volver</button>";
   }else{
      echo "Algo ha ido mal, ¿puede que no seas administrador?";
   }
	

   pie();
?>
