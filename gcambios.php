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
         if (confirm("Este problema será eliminado. ¿Está de acuerdo?")){
            location.href='gproblemas.php?elim='+id;
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

   // Nueva peticion de cambio
   echo "<form action='nuevocambio.php' method='POST'>";
   echo "   <input type='hidden' name='origen'  value='gcambios.php'>";
   echo "   <input type='submit' class='button' name='nuevoCambio' value='Introducir nueva petición de cambio'/>";
   echo "</form>";

   // Eliminamos la peticion de cambio seleccionada
   if($_GET[elim] && is_numeric($_GET[elim])){
      // No se elimina, se pone en el estado eliminado
      $query = "INSERT INTO ".dbname.".estado_peticion
                   (nombre, descripcion, fecha, peticion_cambio)
                VALUES ('eliminado','Se ha borrado la peticion de cambio', '$_SESSION[username]', NOW(), $_GET[elim])";
      mysql_query($query) or die(mysql_error());
      echo "Se eliminó la peticion de cambio con el identificador $_GET[elim] <BR>";
   }

   // Detalles de la peticion de cambio seleccionada
   if($_GET[ver] && is_numeric($_GET[ver])){
	  $query = "SELECT cam.nombre nombre, cam.descripcion desc, tc.nombre tipo,  ec.nombre estado
               FROM ".dbname.".peticion_cambio cam
               LEFT JOIN ".dbname.".tipo_cambio     tc on (tc.id              = cam.tipo_cambio)
               LEFT JOIN ".dbname.".estado_peticion ec on (ec.peticion_cambio = cam.id)
               WHERE ec.id = (SELECT max(id) FROM ".dbname.".peticion_cambio
                                             WHERE peticion_cambio = cam.id group by peticion_cambio) AND
                              cam.id = $_GET[ver]";
      $res = mysql_query($query) or die(mysql_error());
      $row = mysql_fetch_array($res);
      echo "<H4>Detalles de la petici&oacuten de cambio \"$row[nombre]\":</H4>";
      $estado = $row[estado];
      echo "<table border='0' cellspacing='0' summary='Detalles del problema'>";
      echo "<tr><td> <b>ID:</b>            </td><td> $_GET[ver]        </td></tr>";
      echo "<tr><td> <b>Nombre:</b>        </td><td> $row[nombre]      </td></tr>";
	  echo "<tr><td> <b>Tipo:</b>          </td><td> $row[tipo] </td></tr>";
      echo "<tr><td> <b>Descripción:</b>   </td><td> $row[descripcion] </td></tr>";
      echo "<tr><td> <b>Estado actual:</b> </td><td> $row[estado]      </td></tr>";
      echo "<tr><td> <b>Problemas que la generan:</b>     </td><td> </td></tr>";
      $query = "SELECT nombre
                FROM ".dbname.".problema
                WHERE id = $_GET[ver]";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row = mysql_fetch_array($res)){
         echo "<tr><td> <i>$row[nombre]</i></tr>";
      }
	  
	  echo "<tr><td> <b>Items a los que afecta:</b>     </td><td> </td></tr>";
      $query = "SELECT item.id id, titem.nombre tipo
                FROM ".dbname.".item_id item
				LEFT JOIN ".dbname.".tipo_item titem on (tipo.id = item.tipo_item)
                WHERE item.id = $_GET[ver]";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row = mysql_fetch_array($res)){
         echo "<tr><td> $row[tipo] %row[id]</tr>";
      }
	  
	  echo "<tr><td> <b>Reuniones en las que se ha tratado:</b>     </td><td> </td></tr>";
      $query = "SELECT descripcion, fecha
                FROM ".dbname.".reunion
                WHERE peticion_cambio = $_GET[ver]";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row = mysql_fetch_array($res)){
         echo "<tr><td> ".date(dateadd("suma",$row[fecha],0,0,0,6,0,0))." </td><td> $row[descripcion]</tr>";
      }
	  
	  echo "<tr><td> <b>Tareas necesarias:</b>     </td><td> </td></tr>";
      $query = "SELECT descripcion, fecha, estado
                FROM ".dbname.".tarea
                WHERE peticion_cambio = $_GET[ver]";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row = mysql_fetch_array($res)){
         echo "<tr><td> Fecha l&iacutemite: ".date(dateadd("suma",$row[fecha],0,0,0,6,0,0))." </td><td> $row[estad] $row[descripcion]</tr>";
      }
	  
	  echo "<tr><td> <b>Historial:</b>     </td><td> </td></tr>";
      $query = "SELECT nombre, descripcion, fecha,
                FROM ".dbname.".estado_peticion
                WHERE peticion_cambio = $_GET[ver]";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row = mysql_fetch_array($res)){
         echo "<tr><td> ".date(dateadd("suma",$row[fecha],0,0,0,6,0,0))." </td><td>  $row[nombre] $row[descripcion]</tr>";
      }
      echo "</table>";
      if ($estado != 'eliminado'){
         echo "<button type='button' onClick=\"location.href='editproblema.php?id=$_GET[ver]'\">Editar Problema</button>";
         echo "<button type='button' onClick='return asegurar2($_GET[ver]);'>Borrar</button>";
      }
      echo "<BR><button type='button' onClick=\"location.href='gproblemas.php'\">Ocultar</button>";
   }

   // Peticiones de cambio
   if ($_GET[borrados] == 'true'){
      echo "<H4><H4>Peticiones de cambio borradas/finalizadas:</H4>";
   } else {
      echo "<H4><H4>Peticiones de cambio pendientes:</H4>";
   }
   echo "<table border='1' cellspacing='0'>";
   echo "<tr> <td>
               <b><center>Id</center></b>
            </td> <td>
               <b><center>Nombre</center></b>
            </td> <td>
               <b><center>Tipo</center></b>
            </td> <td>
               <b><center>Descripción</center></b>
            </td> <td>
               <b><center>Estado</center></b>
            </td> <td>
               <b><center>Ver</center></b>
            </td> <td>
               <b><center>Borrar</center></b>
            </td> </tr>";

   if ($_GET[borrados] == 'true'){
      $query = "SELECT cam.id id, cam.nombre nombre, cam.descripcion descripcion,
                   tc.nombre tipo, ec.nombre estado
               FROM ".dbname.".peticion_cambio cam
               LEFT JOIN ".dbname.".tipo_cambio     tc on (tc.id              = cam.tipo_cambio)
               LEFT JOIN ".dbname.".estado_peticion ec on (ec.peticion_cambio = cam.id)
               WHERE ec.id = (SELECT max(id) FROM ".dbname.".peticion_cambio
                                             WHERE peticion_cambio = cam.id group by peticion_cambio) AND
                  ec.nombre = 'eliminado'";
   } else {
      $query = "SELECT cam.id id, cam.nombre nombre, cam.descripcion descripcion,
                   tc.nombre tipo, ec.nombre estado
               FROM ".dbname.".peticion_cambio cam
               LEFT JOIN ".dbname.".tipo_cambio     tc on (tc.id              = cam.tipo_cambio)
               LEFT JOIN ".dbname.".estado_peticion ec on (ec.peticion_cambio = cam.id)
               WHERE ec.id = (SELECT max(id) FROM ".dbname.".peticion_cambio
                                             WHERE peticion_cambio = cam.id group by peticion_cambio) AND
                  ec.nombre != 'eliminado'";
   }
   $res   = mysql_query($query) or die(mysql_error());
   while ($row = mysql_fetch_array($res)) {
      echo "<tr onMouseOver='resaltaLinia(this)' onMouseOut='restauraLinia(this)'>";
      echo "   <td><center>$row[id]</center></td>";
      echo "   <td><center>$row[nombre]</center></td>";
	  echo "   <td><center>$row[tipo]</center></td>";
      $descrip = $row[descripcion];
      if (strlen($descrip) > LIM_CAR_DES) $descrip = substr($descrip,0,LIM_CAR_DES - 3)."...";
      echo "   <td><center>$descrip</center></td>";
      echo "   <td><center>$row[estado]</center></td>";
      echo "   <td><center><a href='gproblemas.php?ver=$row[id]'>
                  <img src='img/view.gif'   alt='Ver' title='Ver detalles'></a></center></td>";
      if ($_GET[borrados] == 'true'){
         echo "   <td><center>-</center></td>";
      } else {
         echo "   <td><center><a href='gproblemas.php?elim=$row[id]' onclick='return asegurar();'>
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