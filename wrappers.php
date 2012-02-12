<?php
function cabecera($titulo){
   echo "<!DOCTYPE html PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
<html lang='es'>
   <head>
      <title>GESTIONATIC - $titulo -</title>
      <meta http-equiv='Content-Type' content='text/html; charset= iso-8859-1'>
      <meta name='description'        content='Web para la gestión de servicios basada en itil'>
      <meta name='robots'             content='NOINDEX'>
      <link rel ='stylesheet' type='text/css' href='/css/estilo.css'>

      <script type='text/javascript'>
         function Registro() {
            window.location.href='register.php';
         }
      </script>
      <script language='javascript' src='calendar/calendar.js'></script>
   </head>
   <body>
      <div id='cabecera'>
         <div id='titulo'>
            <h1><a class='link_titol' href='./'>GESTIONATIC</a></h1>
         </div>
         <div id='login'>";

   session_start();  
   if ($_SESSION['logged']!=true) {
      //si no está logeado imprimimos el formulario

      echo "<form method='post' action='login.php'>
      <input type='text'     name='username' size='5' maxlength='30' value='usuario' alt='Escribe tu nombre de usuario' title='Escribe tu nombre de usuario'>
      <input type='password' name='password' size='5' maxlength='30' value='clave' alt='Escribe tu clave' title='Escribe tu clave'>
      <input type='submit'   value='login'       name='login'>
      <input type='button'   value='registrarse' name='register' onClick='javscript:Registro();'> </form>";
   }else{
      //si está logeado le saludamos :)
      echo "hola, ",$_SESSION['username'], "    <a href='logout.php' class='resizable'>salir</a>";
   }
   echo "</div></div> <div id='contenido'>";
}    

function pie(){
   echo "<BR><div id='pie'><center><BR>";
   echo "<button type='button' onClick=\"location.href='./'\">
            Página Principal
         </button> ";
   echo "<button type='button' onClick=\"location.href='/download/SITGESTIONATICManualdeusuario.pdf'\">
            Manual de usuario
         </button> ";
   echo "<button type='button' onClick=\"location.href='/download/SITGESTIONATICManualdeinstalacion.pdf'\">
            Manual de instalación
         </button> ";
   echo "<button type='button' onClick=\"location.href='https://github.com/Supernito/Gestionatic'\">
            Descargar código fuente
         </button> ";
   echo "</center> </div> <!--PIE-->
      </div> <!--CONTENIDO-->
   </body>
</html>";

}
?>
