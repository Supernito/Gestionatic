<?php
   include 'db.conf';
   include 'wrappers.php';

   cabecera("..::PAGINA PRINCIPAL::..");

   mysql_connect(dbhost,dbuser,dbpass); 
   mysql_select_db(dbname);
   session_start();

   if ($_SESSION['logged']!=true){
      // No hay nadie logueado
      echo "<p>Hay que estar logueado para poder acceder a las funcionalidades</p>";
   } else {
      // Hay un usuario logueado
      echo "<p>Bienvenido ".$_SESSION[username].", por favor, escoge una de las siguientes opciones (este formato es temporal y no todos los usuarios tendras permiso para ver todas las opciones)</p><HR>";
      echo "<p>Las siguientes son obligatorias</p>";
      echo "<a href='./'>Gesti�n de Servicios</a>(pendiente)<BR>";
      echo "<a href='./gincidencias.php'>Gesti�n de Incidencias</a><BR>";
      echo "<a href='./gproblemas.php'>Gesti�n de Problemas</a><BR>";
      echo "<a href='./'>Gesti�n de Configuraciones</a>(pendiente)<BR>";
      echo "<a href='./'>Gesti�n de Cambios</a>(pendiente)<BR>";
      echo "<a href='./'>Gesti�n de Versiones</a>(pendiente)<BR>";
      echo "<HR><p>Las siguientes son optativas</p>";
      echo "<a href='./'>Gesti�n de de niveles de servicio</a>(pendiente)<BR>";
      echo "<a href='./'>Gesti�n financiera</a>(pendiente)<BR>";
      echo "<a href='./'>Gesti�n de la capacidad</a>(pendiente)<BR>";
      echo "<a href='./'>Gesti�n de la continuidad del servicio</a>(pendiente)<BR>";
      echo "<a href='./'>Gesti�n de la disponibilidad</a>(pendiente)<BR>";
      echo "<a href='./'>Gesti�n de la seguridad</a>(pendiente)<BR>";
   }

   mysql_close();

  pie();
?>
