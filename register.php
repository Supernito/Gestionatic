<?php
   include 'db.conf';
   include 'wrappers.php';

   cabecera("Registro");

   //Codigo cuando se registran
   if(isset($_POST['validar_registro'])){
      // Han enviado el formulario

      session_start();
      $nick=$_POST['username'];
      $mail=$_POST['email'];
      $pass=$_POST['password'];
      $pass=md5($pass);
      mysql_connect(dbhost,dbuser,dbpass); 
      mysql_select_db(dbname);

      //control de que el usuario no exista	
      $q = mysql_query("SELECT username FROM $dbname.usuario WHERE username='$nick'");
      if (mysql_num_rows($q)!=0){
         echo "El nombre de usuario ya existe <a href='./register.php'>Volver</a>";
      } else {
         //fin de control de que el usuario ya existe
         //fin de control de errores, introducimos el usuario
         mysql_query("INSERT INTO $dbname.usuario
            (`username` ,`password` ,`email` ,`is_admin`)
            VALUES ('$nick', '$pass',  '$mail',  'false');");
         echo "Registro completado con éxito<br>";
         echo "<a href='./'>Volver al inicio</a>";
      }
      
   } else {

      // Formulario para registrarse
?>

   <script type="text/javascript">
      msgerror="";
      function validaEmail(){
      var x = document.forms.formregistro.email.value;
      if(x!=null && x!=""){
         var regexp=/^[a-z0-9._%-]+@[a-z0-9.-]+\.[a-z]{2,4}$/;
         if (!x.match(regexp)){
            msgerror+="\n    - Dirección de e-mail no valida.";
            return false;
         }
            }
         if(x==null | x==""){
            msgerror+="\n    - El email no puede estar vacío.";
            return false;
         }
         return true;
      }

      function validaFormulario(){
         var validacionEmail=validaEmail();
         if(!validacionEmail){
            alert("Se han producido los siguientes errores:"+msgerror);
            msgerror="";
            return false;               
         }else{
            return true;
         }
      }
   </script>

   <script type="text/javascript">
      var RecaptchaOptions = {theme : 'white'};
   </script>

   <form METHOD='post' ACTION='register.php' name='formregistro' onsubmit='return validaFormulario();'>
      <p>usuario <input type='text' name='username' size='30' value=''></p>
      <p>email <input type='text' name='email' size='50' value=''></p>
      <p>password <input type='password' name='password' size='20' value=''></p>
      <p><input type='submit' value='Registrarse' name='validar_registro'>
   </form>

<?php

   }

   pie();

?>
