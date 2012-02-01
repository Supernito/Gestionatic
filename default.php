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
         echo "<a href='./'>Gestión de Servicios</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true' || $row[g_inc] == 'true'){
         echo "<a href='./gincidencias.php'>Gestión de Incidencias</a><BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true' || $row[g_prob] == 'true'){
         echo "<a href='./gproblemas.php'>Gestión de Problemas</a><BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true' || $row[g_conf] == 'true'){
         echo "<a href='./'>Gestión de Configuraciones</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true' || $row[g_cambios] == 'true'){
         echo "<a href='./'>Gestión de Cambios</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true' || $row[g_ver] == 'true'){
         echo "<a href='./'>Gestión de Versiones</a>(pendiente)<BR>"; $opciones++;
      }
//      echo "<HR><p>Las siguientes son optativas</p>";
      if ($row[is_admin] == 'true' || $row[g_usuarios] == 'true'){
         echo "<a href='./gusuarios.php'>Gestión de de usuarios</a><BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true'){
         echo "<a href='./'>Gestión de de niveles de servicio</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true'){
         echo "<a href='./'>Gestión financiera</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true'){
         echo "<a href='./'>Gestión de la capacidad</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true'){
         echo "<a href='./'>Gestión de la continuidad del servicio</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true'){
         echo "<a href='./'>Gestión de la disponibilidad</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true'){
         echo "<a href='./'>Gestión de la seguridad</a>(pendiente)<BR>"; $opciones++;
      }
      if ($opciones == 0){
         echo "<p>No tienes ninguna opción disponible, contacta con un administrador.</p>";
      }
   }

   mysql_close();

  pie();
?>
