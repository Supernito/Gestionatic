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
      echo "<p>Bienvenido ".$_SESSION[username].", por favor, escoge una de las siguientes opciones disponibles</p><HR>";
//      echo "<p>Las siguientes son obligatorias</p>";
      $opciones = 0;
      $query = "SELECT * FROM ".dbname.".usuario WHERE username='$_SESSION[username]'";
      $res = mysql_query($query) or die(mysql_error());
      $row = mysql_fetch_array($res);
      if ($row[is_admin] == 'true' || $row[g_serv] == 'true'){
         echo "<a href='./gservicios.php'>Gesti�n de Servicios</a><BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true' || $row[g_inc] == 'true'){
         echo "<a href='./gincidencias.php'>Gesti�n de Incidencias</a><BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true' || $row[g_prob] == 'true'){
         echo "<a href='./gproblemas.php'>Gesti�n de Problemas</a><BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true' || $row[g_conf] == 'true'){
         echo "<a href='./'>Gesti�n de Configuraciones</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true' || $row[g_cambios] == 'true'){
         echo "<a href='./gcambios.php'>Gesti�n de Cambios</a><BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true' || $row[g_ver] == 'true'){
         echo "<a href='./'>Gesti�n de Versiones</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true' || $row[g_usuarios] == 'true'){
         echo "<a href='./gusuarios.php'>Gesti�n de Usuarios</a><BR>"; $opciones++;
      }
/*
      if ($row[is_admin] == 'true'){
         echo "<a href='./'>Gesti�n de de niveles de servicio</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true'){
         echo "<a href='./'>Gesti�n financiera</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true'){
         echo "<a href='./'>Gesti�n de la capacidad</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true'){
         echo "<a href='./'>Gesti�n de la continuidad del servicio</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true'){
         echo "<a href='./'>Gesti�n de la disponibilidad</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true'){
         echo "<a href='./'>Gesti�n de la seguridad</a>(pendiente)<BR>"; $opciones++;
      }
*/
      if ($row[is_admin] == 'true' || $row[alertas] == 'true'){
         echo "<HR>";
         echo "<H4>Alertas:</H4>";
         $alertas = 0;

         if ($alertas == 0){
            echo "<p>No hay ninguna alerta de la que preocuparse.</p>";
         }
      }
      if ($opciones == 0){
         echo "<p>No tienes ninguna opci�n disponible, contacta con un administrador.</p>";
      }
   }

   mysql_close();

  pie();
?>
