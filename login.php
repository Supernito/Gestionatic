<?php
   //TODO control de errores
   include 'db.conf';
   include 'wrappers.php';

   cabecera("Logeado");

   session_start();  
   $login = $_POST['username'];
   $password = $_POST['password'];

   mysql_connect(dbhost,dbuser,dbpass); 
   mysql_select_db(dbname); 
   $q='false';
   $q = mysql_query("SELECT password FROM $dbname.usuario WHERE username='$login'");
   $db_pass = mysql_fetch_array($q);

   if(md5($password) == $db_pass['password']){
      $_SESSION['username']=$login;
      $_SESSION['logged']='true';
      echo "Correcto, est&aacutes logeado";

// Posar aquí ses posibles accions a fer (Anar a sa seva pagina personal o ficar peça
// (també tornar a s'inici però això ha de sortir tant si se loguea com si no))

   }else{
      echo "Usuario o contrase&ntildea incorrectos<br>";
   }
   echo "<BR><a href='./'>Volver al inicio</a>";

   pie();
?>
