<?php
   include 'db.conf';
   include 'wrappers.php';

   cabecera("Gestión de Usuarios");

   // Script para cambiar el color de las filas
?>

   <script type="text/javascript">
      var tmp;
      function resaltaLinia(row) {
         tmp = row.style.backgroundColor
         row.style.backgroundColor = "#a0a0a0";
      }
      function restauraLinia(row){
         row.style.backgroundColor = tmp;
      }
      function asegurar() {
         return confirm("Este usuario será eliminado permanentemente. ¿Está de acuerdo?");
      }
   </script>

<?php

   mysql_connect(dbhost,dbuser,dbpass); 
   mysql_select_db(dbname);
   session_start();

   if ($_SESSION['logged']!=true){
      // No hay nadie logueado
      echo "Hay que estar logueado para poder acceder a las funcionalidades.";
      mysql_close;
      pie();die();
   }

   // Miraramos los permisos para gestionar usuarios. Los guardamos en $own
   $query = "SELECT is_admin,g_usuarios FROM ".dbname.".usuario WHERE username='$_SESSION[username]'";
   $res   = mysql_query($query) or die(mysql_error());
   $own   = mysql_fetch_array($res);

   // Si no tiene permisos morimos
   if ($own[is_admin] == 'false' && $own[g_usuarios] == 'false'){
      echo "No tienes permisos suficientes.";
      mysql_close;
      pie();die();
   }

   // Admin.
   if (isset($_GET[c0])){
      $query = "SELECT is_admin,username FROM ".dbname.".usuario WHERE id=$_GET[c0]";
      $res   = mysql_query($query) or die(mysql_error());
      $row   = mysql_fetch_array($res);
      if ($row[username] == $_SESSION[username] ||
         ($own[is_admin] == 'false' && $row[username] != $_SESSION[username])){
         echo "No puedes cambiar este permiso.<BR>";
      } else {
         if ($row[is_admin] == 'true'){
            $query = "UPDATE ".dbname.".usuario SET is_admin='false' where id=$_GET[c0]";
            echo "El usuario \"$row[username]\" ya no es administrador.<BR>";
         } else {
            $query = "UPDATE ".dbname.".usuario SET is_admin='true'  where id=$_GET[c0]";
            echo "El usuario \"$row[username]\" ahora es administrador.<BR>";
         }
         $res = mysql_query($query) or die(mysql_error());
      }
   }

   // Alertas
   if (isset($_GET[c1])){
      $query = "SELECT alertas,username FROM ".dbname.".usuario WHERE id=$_GET[c1]";
      $res   = mysql_query($query) or die(mysql_error());
      $row   = mysql_fetch_array($res);
      if ($row[alertas] == 'true'){
         $query = "UPDATE ".dbname.".usuario SET alertas='false' where id=$_GET[c1]";
         echo "El usuario \"$row[username]\" ya no verá las alertas.<BR>";
      } else {
         $query = "UPDATE ".dbname.".usuario SET alertas='true'  where id=$_GET[c1]";
         echo "El usuario \"$row[username]\" ahora verá las alertas.<BR>";
      }
      $res = mysql_query($query) or die(mysql_error());
   }

   // G.Usuarios
   if (isset($_GET[c2])){
      $query = "SELECT is_admin,g_usuarios,username FROM ".dbname.".usuario WHERE id=$_GET[c2]";
      $res   = mysql_query($query) or die(mysql_error());
      $row   = mysql_fetch_array($res);
      if ($own[is_admin] == 'false' && $own[g_usuarios] == 'true' && $row[username] == $_SESSION[username]){
         echo "No puedes cambiar este permiso.<BR>";
      } else {
         if ($row[g_usuarios] == 'true'){
            $query = "UPDATE ".dbname.".usuario SET g_usuarios='false' where id=$_GET[c2]";
            echo "El usuario \"$row[username]\" ya no gestionará los usuarios.<BR>";
         } else {
            $query = "UPDATE ".dbname.".usuario SET g_usuarios='true'  where id=$_GET[c2]";
            echo "El usuario \"$row[username]\" ahora gestionará los usuarios.<BR>";
         }
      }
      $res = mysql_query($query) or die(mysql_error());
   }

   // G.Serv.
   if (isset($_GET[c3])){
      $query = "SELECT g_serv,username FROM ".dbname.".usuario WHERE id=$_GET[c3]";
      $res   = mysql_query($query) or die(mysql_error());
      $row   = mysql_fetch_array($res);
      if ($row[g_serv] == 'true'){
         $query = "UPDATE ".dbname.".usuario SET g_serv='false' where id=$_GET[c3]";
         echo "El usuario \"$row[username]\" ya no gestionará los servicios.<BR>";
      } else {
         $query = "UPDATE ".dbname.".usuario SET g_serv='true'  where id=$_GET[c3]";
         echo "El usuario \"$row[username]\" ahora gestionará los servicios.<BR>";
      }
      $res = mysql_query($query) or die(mysql_error());
   }

   // G.Inc.
   if (isset($_GET[c4])){
      $query = "SELECT g_inc,username FROM ".dbname.".usuario WHERE id=$_GET[c4]";
      $res   = mysql_query($query) or die(mysql_error());
      $row   = mysql_fetch_array($res);
      if ($row[g_inc] == 'true'){
         $query = "UPDATE ".dbname.".usuario SET g_inc='false' where id=$_GET[c4]";
         echo "El usuario \"$row[username]\" ya no gestionará las incidencias.<BR>";
      } else {
         $query = "UPDATE ".dbname.".usuario SET g_inc='true'  where id=$_GET[c4]";
         echo "El usuario \"$row[username]\" ahora gestionara las incidencias.<BR>";
      }
      $res = mysql_query($query) or die(mysql_error());
   }

   // Elevar inc.
   if (isset($_GET[c5])){
      $query = "SELECT elevar_inc,username FROM ".dbname.".usuario WHERE id=$_GET[c5]";
      $res   = mysql_query($query) or die(mysql_error());
      $row   = mysql_fetch_array($res);
      if ($row[elevar_inc] == 'true'){
         $query = "UPDATE ".dbname.".usuario SET elevar_inc='false' where id=$_GET[c5]";
         echo "El usuario \"$row[username]\" ya no podrá elevar las incidencias a problemas.<BR>";
      } else {
         $query = "UPDATE ".dbname.".usuario SET elevar_inc='true'  where id=$_GET[c5]";
         echo "El usuario \"$row[username]\" ahora podrá elevar las incidencias a problemas.<BR>";
      }
      $res = mysql_query($query) or die(mysql_error());
   }

   // G.Prob.
   if (isset($_GET[c6])){
      $query = "SELECT g_prob,username FROM ".dbname.".usuario WHERE id=$_GET[c6]";
      $res   = mysql_query($query) or die(mysql_error());
      $row   = mysql_fetch_array($res);
      if ($row[g_prob] == 'true'){
         $query = "UPDATE ".dbname.".usuario SET g_prob='false' where id=$_GET[c6]";
         echo "El usuario \"$row[username]\" ya no gestionará los problemas.<BR>";
      } else {
         $query = "UPDATE ".dbname.".usuario SET g_prob='true'  where id=$_GET[c6]";
         echo "El usuario \"$row[username]\" ahora gestionará los problemas.<BR>";
      }
      $res = mysql_query($query) or die(mysql_error());
   }

   // G.Conf.
   if (isset($_GET[c7])){
      $query = "SELECT g_conf,username FROM ".dbname.".usuario WHERE id=$_GET[c7]";
      $res   = mysql_query($query) or die(mysql_error());
      $row   = mysql_fetch_array($res);
      if ($row[g_conf] == 'true'){
         $query = "UPDATE ".dbname.".usuario SET g_conf='false' where id=$_GET[c7]";
         echo "El usuario \"$row[username]\" ya no gestionará la configuración<BR>";
      } else {
         $query = "UPDATE ".dbname.".usuario SET g_conf='true'  where id=$_GET[c7]";
         echo "El usuario \"$row[username]\" ahora gestionará la configuración.<BR>";
      }
      $res = mysql_query($query) or die(mysql_error());
   }

   // G.Cambios
   if (isset($_GET[c8])){
      $query = "SELECT g_cambios,username FROM ".dbname.".usuario WHERE id=$_GET[c8]";
      $res   = mysql_query($query) or die(mysql_error());
      $row   = mysql_fetch_array($res);
      if ($row[g_cambios] == 'true'){
         $query = "UPDATE ".dbname.".usuario SET g_cambios='false' where id=$_GET[c8]";
         echo "El usuario \"$row[username]\" ya no gestionará los cambios.<BR>";
      } else {
         $query = "UPDATE ".dbname.".usuario SET g_cambios='true'  where id=$_GET[c8]";
         echo "El usuario \"$row[username]\" ahora gestionará los cambios.<BR>";
      }
      $res = mysql_query($query) or die(mysql_error());
   }

   // G.Ver.
   if (isset($_GET[c9])){
      $query = "SELECT g_ver,username FROM ".dbname.".usuario WHERE id=$_GET[c9]";
      $res   = mysql_query($query) or die(mysql_error());
      $row   = mysql_fetch_array($res);
      if ($row[g_ver] == 'true'){
         $query = "UPDATE ".dbname.".usuario SET g_ver='false' where id=$_GET[c9]";
         echo "El usuario \"$row[username]\" ya no gestionará las versiones.<BR>";
      } else {
         $query = "UPDATE ".dbname.".usuario SET g_ver='true'  where id=$_GET[c9]";
         echo "El usuario \"$row[username]\" ahora gestionará las versiones.<BR>";
      }
      $res = mysql_query($query) or die(mysql_error());
   }

   // Para cambiar la contraseña (Paso 1)
   if (isset($_GET[P1])){
      $query = "SELECT username FROM ".dbname.".usuario WHERE id=$_GET[P1]";
      $res   = mysql_query($query) or die(mysql_error());
      $row   = mysql_fetch_array($res);
      echo "<H4>Cambiar clave para el usuario \"$row[username]\":</H4>";
      echo "<form method='post' action='/gusuarios.php' name='GUpassForm'>
               <input type='hidden'   name='P2' value='$_GET[P1]'>
               Escribe la nueva contraseña
               <input type='password' name='newPass' size='30' value=''>
               <input type='submit'   name='Cambiar' value='Cambiar'>
            </form>";
   }

   // Para cambiar la contraseña (Paso 2)
   if (isset($_POST[P2])){
      $pass  = md5($_POST[newPass]);
      $query = "UPDATE ".dbname.".usuario SET password='$pass' where id=$_POST[P2]";
      $res   = mysql_query($query) or die(mysql_error());
      echo "Contraseña cambiada con éxito<BR>";
   }

   // Borrar un usuario
   if (isset($_GET[d])){
      $query = "SELECT username FROM ".dbname.".usuario WHERE id=$_GET[d]";
      $res   = mysql_query($query) or die(mysql_error());
      $row   = mysql_fetch_array($res);
      if ($row[username] == $_SESSION[username]){
         echo "Será mejor que no te borres a ti mismo.<BR>";
      } else {
         $query = "DELETE FROM ".dbname.".usuario WHERE id=$_GET[d]";
         $res   = mysql_query($query) or die(mysql_error());
         echo "El usuario \"$row[username]\" ha sido borrado (Se mantienen sus acciones).<BR>";
      }
   }

   // USUARIOS (habilitar para otros permisos)
   echo "<H4>Usuarios Existentes:</H4>";
   echo "<table border='1' cellspacing='0' summary='Control de usuarios'>";
   echo "<tr>
            <td>
               <b><center>Id</center></b>
            </td>
            <td>
               <b><center>Nombre</center></b>
            </td>
            <td>
               <b><center>Correo</center></b>
            </td>
            <td>
               <b><center>Admin.</center></b>
            </td>
            <td>
               <b><center>Alertas</center></b>
            </td>
            <td>
               <b><center>G.Usuarios</center></b>
            </td>
            <td>
               <b><center>G.Serv.</center></b>
            </td>
            <td>
               <b><center>G.Inc.</center></b>
            </td>
            <td>
               <b><center>Elevar Inc.</center></b>
            </td>
            <td>
               <b><center>G.Prob.</center></b>
            </td>
            <td>
               <b><center>G.Conf.</center></b>
            </td>
            <td>
               <b><center>G.Cambios</center></b>
            </td>
            <td>
               <b><center>G.Ver.</center></b>
            </td>
            <td>
               <b><center>Clave</center></b>
            </td>
            <td>
               <b><center>Borrar</center></b>
            </td>
         </tr>";

   $query = "SELECT * FROM ".dbname.".usuario order by id";
   $res   = mysql_query($query) or die(mysql_error());
   while ($row = mysql_fetch_array($res)) {
      echo "<tr onMouseOver='resaltaLinia(this)' onMouseOut='restauraLinia(this)'>";
      // Id
      echo "   <td><center>$row[id]</center></td>";
      // Nombre
      echo "   <td><center>$row[username]</center></td>";
      // Correo
      echo "   <td><center>$row[email]</center></td>";
      // Admin
      echo "   <td><center><a href='/gusuarios.php?c0=$row[id]'>";
      echo "      <img src='img/$row[is_admin].gif' alt='Cambiar' title='Cambiar'></a></center></td>";
      // Alertas
      echo "   <td><center><a href='/gusuarios.php?c1=$row[id]'>";
      echo "      <img src='img/$row[alertas].gif' alt='Cambiar' title='Cambiar'></a></center></td>";
      // G.Usuarios
      echo "   <td><center><a href='/gusuarios.php?c2=$row[id]'>";
      echo "      <img src='img/$row[g_usuarios].gif' alt='Cambiar' title='Cambiar'></a></center></td>";
      // G.Serv.
      echo "   <td><center><a href='/gusuarios.php?c3=$row[id]'>";
      echo "      <img src='img/$row[g_serv].gif' alt='Cambiar' title='Cambiar'></a></center></td>";
      // G.Inc.
      echo "   <td><center><a href='/gusuarios.php?c4=$row[id]'>";
      echo "      <img src='img/$row[g_inc].gif' alt='Cambiar' title='Cambiar'></a></center></td>";
      // Elevar Inc.
      echo "   <td><center><a href='/gusuarios.php?c5=$row[id]'>";
      echo "      <img src='img/$row[elevar_inc].gif' alt='Cambiar' title='Cambiar'></a></center></td>";
      // G.Prob.
      echo "   <td><center><a href='/gusuarios.php?c6=$row[id]'>";
      echo "      <img src='img/$row[g_prob].gif' alt='Cambiar' title='Cambiar'></a></center></td>";
      // G.Conf.
      echo "   <td><center><a href='/gusuarios.php?c7=$row[id]'>";
      echo "      <img src='img/$row[g_conf].gif' alt='Cambiar' title='Cambiar'></a></center></td>";
      // G.Cambios
      echo "   <td><center><a href='/gusuarios.php?c8=$row[id]'>";
      echo "      <img src='img/$row[g_cambios].gif' alt='Cambiar' title='Cambiar'></a></center></td>";
      // G.Ver.
      echo "   <td><center><a href='/gusuarios.php?c9=$row[id]'>";
      echo "      <img src='img/$row[g_ver].gif' alt='Cambiar' title='Cambiar'></a></center></td>";
      // Clave
      echo "   <td><center><a href='/gusuarios.php?P1=$row[id]'>nueva</a></center></td>";
      // Borrar
      echo "   <td><center><a href='/gusuarios.php?d=$row[id]' onclick='return asegurar();'>
                  <img src='img/delete.gif' alt='Borrar' title='Borrar'></a></center></td>";
      echo "</tr>";
   }
   echo "</table>";
   echo "Nota: Si se es administrador, se poseen todos los demás permisos de forma implícita.<BR>";

   mysql_close();

  pie();
?>
