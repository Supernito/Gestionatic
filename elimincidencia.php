<?php
 
   include 'db.conf';
   include 'wrappers.php';

   cabecera("Eliminar incidencia");

   session_start(); 
   mysql_connect(dbhost,dbuser,dbpass); 
   mysql_select_db(dbname);
   $id= $_POST['idincidencia'];
   $loggedu = $_SESSION['username'];
   $query = mysql_query("SELECT is_admin FROM $dbname.usuario WHERE username='$loggedu'");
   $row = mysql_fetch_array($query);
   $isadmin = $row['is_admin'];
   if ($isadmin=='true'){
      mysql_query("DELETE FROM $dbname.incidencia WHERE id=$id");
      echo "Incidencia eliminada, <a href='./gincidencias.php'>Volver al inicio</a>";
   }else{
      echo "no hagas eso, ¬¬";
   }

   pie();
?>
