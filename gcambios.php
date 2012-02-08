<?php
   include 'db.conf';
   include 'wrappers.php';

   define (LIM_CAR_DES,'30'); // Carácteres a mostrar en Descripción
   $zona_horaria="+06:00";

   cabecera("Gestión de Cambios");

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
         if (confirm("Esta peticion de cambio será eliminada. ¿Está de acuerdo?")){
            location.href='gcambios.php?elim='+id;
            return true;
         }
         return false;
      }

   </script>

<?php

   mysql_connect(dbhost,dbuser,dbpass);
   mysql_select_db(dbname);
   session_start();

   // Función para poner mejor la fecha/hora
   function dateadd($operacion, $date, $dd=0, $mm=0, $yy=0, $hh=0, $mn=0, $ss=0){
      if($operacion=="resta"){
         $date_r = getdate(strtotime($date));
         $resultado = date("d-m-Y H:i:s", mktime(($date_r["hours"]-$hh),
                           ($date_r["minutes"]-$mn),($date_r["seconds"]-$ss),
                           ($date_r["mon"]-$mm),($date_r["mday"]-$dd),($date_r["year"]-$yy)));
         return $resultado; }
      else {
         $date_r = getdate(strtotime($date));
         $resultado = date("d-m-Y H:i:s", mktime(($date_r["hours"]+$hh),
                           ($date_r["minutes"]+$mn), ($date_r["seconds"]+$ss),
                           ($date_r["mon"]+$mm),($date_r["mday"]+$dd),($date_r["year"]+$yy)));
         return $resultado;
      }
   }

   // Controlar aquí también que tenga permisos
   if ($_SESSION['logged']!=true){
      // No hay nadie logueado
      echo "<p>Hay que estar logueado para ver las funcionalidades</p>";
      mysql_close();
      pie(); die();
   }

   // Miraramos los permisos para gestionar problemas. Los guardamos en $own
   $query = "SELECT is_admin,g_cambios FROM ".dbname.".usuario WHERE username='$_SESSION[username]'";
   $res   = mysql_query($query) or die(mysql_error());
   $own   = mysql_fetch_array($res);

   // Si no tiene permisos morimos
   if ($own[is_admin] == 'false' && $own[g_cambios] == 'false'){
      echo "No tienes permisos suficientes.";
      mysql_close;
      pie();die();
   }

   // Nueva peticion de cambio
   echo "<form action='nuevocambio.php' method='POST'>";
   echo "   <input type='hidden' name='origen'  value='gcambios.php'>";
   echo "   <input type='submit' class='button' name='nuevaPeticion' value='Introducir nueva peticion de cambio'/>";
   echo "</form>";

   // Eliminamos la peticion de cambio seleccionada
   if($_GET[elim] && is_numeric($_GET[elim])){
      // No se elimina, se pone en el estado eliminado
      $query = "INSERT INTO ".dbname.".estado_peticion
                   (nombre, descripcion, fecha, peticion_cambio)
                VALUES ('eliminado','Se ha eliminado el cambio', NOW(), $_GET[elim])";
      mysql_query($query) or die(mysql_error());
      echo "Se eliminó el cambio con el identificador $_GET[elim] <BR>";
   }

   // Detalles del cambio seleccionado
   if($_GET[ver] && is_numeric($_GET[ver])){
      // A diferéncia de l'altre aquí sortirien tots els estats que ha tingut
      $query = "SELECT cam.id id, cam.nombre nombre, cam.descripcion descripcion,
                   tcam.nombre tipo, prob.nombre problema, ep.nombre estado
               FROM ".dbname.".peticion_cambio cam
               LEFT JOIN ".dbname.".tipo_cambio     tcam on (tcam.id = cam.tipo_cambio)
			   LEFT JOIN ".dbname.".problema        prob on (prob.id = cam.problema)
               LEFT JOIN ".dbname.".estado_peticion   ep on (ep.peticion_cambio = cam.id)
               WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_peticion
                               WHERE peticion_cambio = cam.id group by peticion_cambio) AND
                  cam.id = $_GET[ver]";
      $res = mysql_query($query) or die(mysql_error());
      $row = mysql_fetch_array($res);
      echo "<H4>Detalles del cambio \"$row[nombre]\":</H4>";
      $estado = $row[estado];
      echo "<table border='0' cellspacing='0' summary='Detalles del problema'>";
      echo "<tr><td> <b>ID:</b>            </td><td> $_GET[ver]        </td></tr>";
      echo "<tr><td> <b>Nombre:</b>        </td><td> $row[nombre]      </td></tr>";
      echo "<tr><td> <b>Descripción:</b>   </td><td> $row[descripcion] </td></tr>";
      echo "<tr><td> <b>Tipo:</b>          </td><td> $row[tipo]    </td></tr>";
      echo "<tr><td> <b>Estado actual:</b> </td><td> $row[estado]      </td></tr>";
	  if (isset($row[problema])){
          echo "<tr><td> <b>Problema:</b> </td><td> $row[problema]      </td></tr>";
	  } else {
	      echo "<tr><td> <b>Problema:</b> </td><td> ninguno  </td></tr>";
	  }
      echo "<tr><td> <b>Reuniones:</b>     </td><td> </td></tr>";
      $query = "SELECT descripcion, fecha
                FROM ".dbname.".reunion
                WHERE peticion_cambio=$_GET[ver]";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row = mysql_fetch_array($res)){
         echo "<tr><td> ".date(dateadd("suma",$row[fecha],0,0,0,6,0,0))." </td><td> $row[descripcion] </td></tr>";
      }
      
      echo "<tr><td> <b>Tareas:</b>     </td><td> </td></tr>";
      $query = "SELECT descripcion, estado, fecha
                FROM ".dbname.".tarea
                WHERE peticion_cambio=$_GET[ver]";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row = mysql_fetch_array($res)){
         echo "<tr><td> ".date(dateadd("suma",$row[fecha],0,0,0,6,0,0))." </td><td> <i>$row[estado]</i> </td><td> $row[descripcion] </td></tr>";
      }

      echo "<tr><td> <b>Items:</b>     </td><td> </td></tr>";
      $query = "SELECT item_id.descripcion, titem.nombre tipo
                FROM ".dbname.".item_id
				LEFT JOIN ".dbname.".cambio_item     citem on (citem.peticion_cambio = $_GET[ver])
				LEFT JOIN ".dbname.".tipo_item       titem on (titem.id = item_id.tipo_item)
                WHERE $_GET[ver] = citem.peticion_cambio AND item_id.id = citem.item AND item_id.padre IS NULL";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row = mysql_fetch_array($res)){
         echo "<tr><td> $row[tipo] </td><td>  $row[desc] </td></tr>";
      }

      echo "<tr><td> <b>Historial:</b>     </td><td> </td></tr>";
      $query = "SELECT descripcion, fecha
                FROM ".dbname.".estado_peticion
                WHERE peticion_cambio=$_GET[ver]";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row = mysql_fetch_array($res)){
         echo "<tr><td> ".date(dateadd("suma",$row[fecha],0,0,0,6,0,0))." </td><td> $row[descripcion] </td></tr>";
      }
      
      echo "</table>";
      if ($estado != 'eliminado'){
         echo "<button type='button' onClick=\"location.href='editcambio.php?id=$_GET[ver]'\">Editar Cambio</button>";
         echo "<button type='button' onClick='return asegurar2($_GET[ver]);'>Borrar</button>";
      }
      echo "<BR><button type='button' onClick=\"location.href='gcambios.php'\">Ocultar</button>";
   }

   // Peticiones de cambio
   if ($_GET[borrados] == 'true'){
      echo "<H4><H4>Cambios Borrados/Completados:</H4>";
   } else {
      echo "<H4><H4>Cambios Pendientes:</H4>";
   }
   echo "<table border='1' cellspacing='0'>";
   echo "<tr> <td>
               <b><center>Id</center></b>
            </td> <td>
               <b><center>Nombre</center></b>
            </td> <td>
               <b><center>Descripción</center></b>
            </td> <td>
               <b><center>Tipo</center></b>
            </td> <td>
               <b><center>Estado</center></b>
            </td> <td>
               <b><center>Ver</center></b>
            </td> <td>
               <b><center>Borrar</center></b>
            </td> </tr>";

   if ($_GET[borrados] == 'true'){
      $query = "SELECT cam.id id, cam.nombre nombre, cam.descripcion descripcion,
                   tcam.nombre tipo, ep.nombre estado
               FROM ".dbname.".peticion_cambio cam
               LEFT JOIN ".dbname.".tipo_cambio     tcam on (tcam.id        = cam.tipo_cambio)
               LEFT JOIN ".dbname.".estado_peticion   ep on (ep.peticion_cambio = cam.id)
               WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_peticion
                               WHERE peticion_cambio = cam.id group by peticion_cambio) AND
                  ep.nombre = 'eliminado'";
   } else {
      $query = "SELECT cam.id id, cam.nombre nombre, cam.descripcion descripcion,
                   tcam.nombre tipo, ep.nombre estado
               FROM ".dbname.".peticion_cambio cam
               LEFT JOIN ".dbname.".tipo_cambio     tcam on (tcam.id        = cam.tipo_cambio)
               LEFT JOIN ".dbname.".estado_peticion   ep on (ep.peticion_cambio = cam.id)
               WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_peticion
                               WHERE peticion_cambio = cam.id group by peticion_cambio) AND
                  ep.nombre != 'eliminado'";
   }
   $res   = mysql_query($query) or die(mysql_error());
   while ($row = mysql_fetch_array($res)) {
      echo "<tr onMouseOver='resaltaLinia(this)' onMouseOut='restauraLinia(this)'>";
      echo "   <td><center>$row[id]</center></td>";
      echo "   <td><center>$row[nombre]</center></td>";
      $descrip = $row[descripcion];
      if (strlen($descrip) > LIM_CAR_DES) $descrip = substr($descrip,0,LIM_CAR_DES - 3)."...";
      echo "   <td><center>$descrip</center></td>";
      echo "   <td><center>$row[tipo]</center></td>";
      echo "   <td><center>$row[estado]</center></td>";
      echo "   <td><center><a href='gcambios.php?ver=$row[id]'>
                  <img src='img/view.gif'   alt='Ver' title='Ver detalles'></a></center></td>";
      if ($_GET[borrados] == 'true'){
         echo "   <td><center>-</center></td>";
      } else {
         echo "   <td><center><a href='gcambios.php?elim=$row[id]' onclick='return asegurar();'>
                     <img src='img/delete.gif' alt='Borrar' title='Borrar'></a></center></td>";
      }
      echo "</tr>";
   }
   echo "</table>";

   // Botón para mostrar las peticiones borradas/finalizadas y viceversa
   if ($_GET[borrados] == 'true'){
      echo "<button type='button'
               onClick=\"location.href='gcambios.php'\">
               Modo normal
            </button>";
   } else {
      echo "<button type='button'
               onClick=\"location.href='gcambios.php?borrados=true'\">
               Mostrar peticiones de cambio<BR>borradas/finalizadas
            </button>";
   }

   mysql_close();
   pie();
?>