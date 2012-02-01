<?php
   include 'db.conf';
   include 'wrappers.php';

   cabecera("Logeado");

   session_start();  
   $login = $_POST['username'];
   $password = $_POST['password'];

   mysql_connect(dbhost,dbuser,dbpass); 
   mysql_select_db(dbname);
   $query = "SELECT * FROM ".dbname.".usuario WHERE username='$login'";
   $res = mysql_query($query) or die(mysql_error());
   $row = mysql_fetch_array($res);

   if(md5($password) == $row['password']){
      $_SESSION['username']=$row[username];
      $_SESSION['logged']='true';
      echo "Correcto, estás logeado.";
   }else{
      echo "<h4>Usuario o contraseña incorrectos</h4>";
   }
   echo "<BR><a href='./'>Volver al inicio</a>";

   pie();
?>
