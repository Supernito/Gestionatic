<?php
   include 'db.conf';
   include 'wrappers.php';

   define (LIM_CAR_DES,'30'); // Carácteres a mostrar en Descripción

   cabecera("Gestión de Servicios");

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
         return confirm("Esta entrada será eliminada. ¿Está de acuerdo?");
      }

      function asegurar2(id) {
         if (confirm("Este servicio será eliminado. ¿Está de acuerdo?")){
            location.href='gservicios.php?elim='+id;
            return true;
         }
         return false;
      }

   </script>

<?php

   mysql_connect(dbhost,dbuser,dbpass);
   mysql_select_db(dbname);
   session_start();

   // Controlar aquí también que tenga permisos
   if ($_SESSION['logged']!=true){
      // No hay nadie logueado
      echo "<p>Hay que estar logueado para ver las funcionalidades</p>";
      mysql_close();
      pie(); die();
   }

   // Miraramos los permisos para gestionar servicios. Los guardamos en $own
   $query = "SELECT is_admin,g_serv FROM ".dbname.".usuario WHERE username='$_SESSION[username]'";
   $res   = mysql_query($query) or die(mysql_error());
   $own   = mysql_fetch_array($res);

   // Si no tiene permisos morimos
   if ($own[is_admin] == 'false' && $own[g_serv] == 'false'){
      echo "No tienes permisos suficientes.";
      mysql_close;
      pie();die();
   }

   // Nuevo servicio
   echo "<form action='nuevoservicio.php' method='POST'>";
   echo "   <input type='hidden' name='origen'  value='gservicios.php'>";
   echo "   <input type='submit' class='button' name='nuevoServicio' value='Introducir nuevo servicio'/>";
   echo "</form>";

   // Eliminamos el servicio seleccionado
   if($_GET[elim] && is_numeric($_GET[elim])){
      $query = "DELETE FROM ".dbname.".servicio WHERE id = $_GET[elim]";
      mysql_query($query) or die(mysql_error());
      echo "Se eliminó el servicio con el identificador $_GET[elim] <BR>";
   }

   // Detalles del servicio seleccionado
   if($_GET[ver] && is_numeric($_GET[ver])){
      $query = "SELECT id, nombre, descripcion
               FROM ".dbname.".servicio WHERE id = $_GET[ver]";
      $res = mysql_query($query) or die(mysql_error());
      $row = mysql_fetch_array($res);
      echo "<H4>Detalles del servicio \"$row[nombre]\":</H4>";
      $estado = $row[estado];
      echo "<table border='0' cellspacing='0' summary='Detalles del servicio'>";
      echo "<tr><td> <b>ID:</b>            </td><td> $_GET[ver]        </td></tr>";
      echo "<tr><td> <b>Nombre:</b>        </td><td> $row[nombre]      </td></tr>";
      echo "<tr><td> <b>Descripción:</b>   </td><td> $row[descripcion] </td></tr>";
      echo "</table>";
/*	  
      echo "<td><form method='post' action='editservicio.php'>
					<input type='hidden' name='servicio' value='$id'>
					<input type='submit' class='button' name='editservicio' value='Editar servicio'/>
					</form></td>";
*/
      echo "<button type='button' onClick='return asegurar2($_GET[ver]);'>Borrar</button>";
   }

   // Servicios
   echo "<H4>Servicios Existentes:</H4>";
   echo "<table border='1' cellspacing='0'>";
   echo "<tr> <td>
               <b><center>Id</center></b>
            </td> <td>
               <b><center>Nombre</center></b>
            </td> <td>
               <b><center>Descripción</center></b>
            </td> <td>
               <b><center>Ver</center></b>
            </td> <td>
               <b><center>Borrar</center></b>
            </td> </tr>";

   $query = "SELECT id, nombre, descripcion FROM ".dbname.".servicio";
   $res   = mysql_query($query) or die(mysql_error());
   while ($row = mysql_fetch_array($res)) {
      echo "<tr onMouseOver='resaltaLinia(this)' onMouseOut='restauraLinia(this)'>";
      echo "   <td><center>$row[id]</center></td>";
      echo "   <td><center>$row[nombre]</center></td>";
      $descrip = $row[descripcion];
      if (strlen($descrip) > LIM_CAR_DES) $descrip = substr($descrip,0,LIM_CAR_DES - 3)."...";
      echo "   <td><center>$descrip</center></td>";
      echo "   <td><center><a href='gservicios.php?ver=$row[id]'>
                  <img src='img/view.gif'   alt='Ver' title='Ver detalles'></a></center></td>";
      echo "   <td><center><a href='gservicios.php?elim=$row[id]' onclick='return asegurar();'>
                     <img src='img/delete.gif' alt='Borrar' title='Borrar'></a></center></td>";
      echo "</tr>";
   }
   echo "</table>";

   mysql_close();
   pie();
?>
