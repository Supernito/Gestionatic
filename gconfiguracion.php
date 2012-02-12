<?php
   include 'db.conf';
   include 'wrappers.php';

   define (LIM_CAR_DES,'30'); // Carácteres a mostrar en Descripción
   $zona_horaria="+06:00";

   cabecera("Gestión de Configuración");

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
         if (confirm("Este elemento de configuración será eliminado. ¿Está de acuerdo?")){
            location.href='gconfiguracion.php?elim='+id;
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
   $query = "SELECT is_admin,g_conf FROM ".dbname.".usuario WHERE username='$_SESSION[username]'";
   $res   = mysql_query($query) or die(mysql_error());
   $own   = mysql_fetch_array($res);

   // Si no tiene permisos morimos
   if ($own[is_admin] == 'false' && $own[g_configuracion] == 'false'){
      echo "No tienes permisos suficientes.";
      mysql_close;
      pie();die();
   }

   // Nuevo elemento de configuración
   echo "<form action='nuevaconfiguracion.php' method='POST'>";
   echo "   <input type='hidden' name='origen'  value='gconfiguracion.php'>";
   echo "   <input type='submit' class='button' name='nuevoItem' value='Introducir nuevo elemento de configuración'/>";
   echo "</form>";

   // Eliminamos el elemento de configuración seleccionado
   if($_GET[elim] && is_numeric($_GET[elim])){
      // No se elimina, se pone en el estado eliminado
      $query = "INSERT INTO ".dbname.".estado_item
                   (nombre, descripcion, fecha, item)
                VALUES ('eliminado','Se ha eliminado el elemento de configuración', NOW(), $_GET[elim])";
      mysql_query($query) or die(mysql_error());
      echo "Se eliminó el elemento de configuración $_GET[elim] <BR>";
   }

   // Detalles del item seleccionado
   if($_GET[ver] && is_numeric($_GET[ver])){
      // A diferéncia de l'altre aquí sortirien tots els estats que ha tingut
      $query = "SELECT cam.id id, cam.descripcion descripcion, cam.padre padre,
                   tcam.nombre tipo, ep.nombre estado
               FROM ".dbname.".item_id cam
               LEFT JOIN ".dbname.".tipo_item     tcam on (tcam.id = cam.tipo_item)
               LEFT JOIN ".dbname.".estado_item   ep on (ep.item = cam.id)
               WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_item
                               WHERE item = cam.id group by item) AND
                  cam.id = $_GET[ver]";
      $res = mysql_query($query) or die(mysql_error());
      $row = mysql_fetch_array($res);
      echo "<H4>Detalles del elemento de configuración \"$row[id]\":</H4>";
      $estado = $row[estado];
      echo "<table border='0' cellspacing='0' summary='Detalles del elemento'>";
      echo "<tr><td> <b>ID:</b>            </td><td> $_GET[ver]        </td></tr>";
      //echo "<tr><td> <b>Nombre:</b>        </td><td> $row[nombre]      </td></tr>";
      echo "<tr><td> <b>Descripción:</b>   </td><td> $row[descripcion] </td></tr>";
      echo "<tr><td> <b>Tipo:</b>          </td><td> $row[tipo]    </td></tr>";
      echo "<tr><td> <b>Estado actual:</b> </td><td> $row[estado]      </td></tr>";
	  echo "<tr><td> <b>Padre:</b> 		   </td><td> $row[padre]      </td></tr>";
	 /* if (isset($row[problema])){
          echo "<tr><td> <b>Problema:</b> </td><td> $row[problema]      </td></tr>";
	  } else {
	      echo "<tr><td> <b>Problema:</b> </td><td> ninguno  </td></tr>";
	  }*/
	  
	  //Aprender a imprimir extras**************************************************************************************************************************************
	  
      /*echo "<tr><td> <b>Reuniones:</b>     </td></tr>";
      $query = "SELECT descripcion, fecha
                FROM ".dbname.".reunion
                WHERE peticion_cambio=$_GET[ver]";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row = mysql_fetch_array($res)){
         echo "<tr><td> </td><td> $row[fecha] </td><td> $row[descripcion] </td></tr>";
      }
      
      echo "<tr><td> <b>Tareas:</b>     </td></tr>";
      $query = "SELECT descripcion, estado, fecha
                FROM ".dbname.".tarea
                WHERE peticion_cambio=$_GET[ver]";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row = mysql_fetch_array($res)){
         echo "<tr><td></td><td> $row[fecha] </td> <td> $row[descripcion] </td> <td> <i>$row[estado]</i> </td></tr>";
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
      }*/
	  echo "</table>";
      echo "<table border='0' cellspacing='0' summary='Historial del elemento'>";
      echo "<tr><td> <b>Historial:</b>     </td><td> </td></tr>";
      $query = "SELECT descripcion, fecha
                FROM ".dbname.".estado_item
                WHERE item=$_GET[ver]";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row = mysql_fetch_array($res)){
         echo "<tr><td> ".date(dateadd("suma",$row[fecha],0,0,0,6,0,0))." </td><td> $row[descripcion] </td></tr>";
      }
      
      echo "</table>";
      if ($estado != 'eliminado'){
         echo "<button type='button' onClick=\"location.href='editconfiguracion.php?id=$_GET[ver]'\">Editar elemento</button>";
         echo "<button type='button' onClick='return asegurar2($_GET[ver]);'>Borrar</button>";
      }
      echo "<BR><button type='button' onClick=\"location.href='gconfiguracion.php'\">Ocultar</button>";
   }

   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   // Elementos de configuración
   if ($_GET[borrados] == 'true'){
      echo "<H4><H4>Elemenots borrados:</H4>";
   } else {
      echo "<H4><H4>Elementos de configuración:</H4>";
   }
   echo "<table border='1' cellspacing='0'>";
   echo "<tr> <td>
               <b><center>Id</center></b>
            </td><td>
               <b><center>Descripción</center></b>
            </td> <td>
               <b><center>Tipo</center></b>
            </td> <td>
               <b><center>Estado</center></b>
            </td> <td>
               <b><center>Padre</center></b>
            </td> <td>
               <b><center>Ver</center></b>
            </td> <td>
               <b><center>Borrar</center></b>
            </td> </tr>";

   if ($_GET[borrados] == 'true'){
      $query = "SELECT cam.id id, cam.descripcion descripcion, cam.padre padre,
                   tcam.nombre tipo, ep.nombre estado
               FROM ".dbname.".item_id cam
               LEFT JOIN ".dbname.".tipo_item     tcam on (tcam.id        = cam.tipo_item)
               LEFT JOIN ".dbname.".estado_item   ep on (ep.item = cam.id)
               WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_item
                               WHERE item = cam.id group by item) AND
                  ep.nombre = 'eliminado'";
   } else {
      $query = "SELECT cam.id id, cam.descripcion descripcion, cam.padre padre,
                   tcam.nombre tipo, ep.nombre estado
               FROM ".dbname.".item_id cam
               LEFT JOIN ".dbname.".tipo_item     tcam on (tcam.id        = cam.tipo_item)
               LEFT JOIN ".dbname.".estado_item   ep on (ep.item = cam.id)
               WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_item
                               WHERE item = cam.id group by item) AND
                  ep.nombre != 'eliminado'";
   }
   $res   = mysql_query($query) or die(mysql_error());
   while ($row = mysql_fetch_array($res)) {
      echo "<tr onMouseOver='resaltaLinia(this)' onMouseOut='restauraLinia(this)'>";
      echo "   <td><center>$row[id]</center></td>";
     // echo "   <td><center>$row[nombre]</center></td>";
      $descrip = $row[descripcion];
	  $padre = $row[padre];
      if (strlen($descrip) > LIM_CAR_DES) $descrip = substr($descrip,0,LIM_CAR_DES - 3)."...";
      echo "   <td><center>$descrip</center></td>";
      echo "   <td><center>$row[tipo]</center></td>";
      echo "   <td><center>$row[estado]</center></td>";
	  //echo "   <td><center>$padre</center></td>";
	  echo "   <td><center><a href='gconfiguracion.php?ver=$padre'>$padre</a></center></td>";
	  
	  
	  
	  
      echo "   <td><center><a href='gconfiguracion.php?ver=$row[id]'>
                  <img src='img/view.gif'   alt='Ver' title='Ver detalles'></a></center></td>";
      if ($_GET[borrados] == 'true'){
         echo "   <td><center>-</center></td>";
      } else {
         echo "   <td><center><a href='gconfiguracion.php?elim=$row[id]' onclick='return asegurar();'>
                     <img src='img/delete.gif' alt='Borrar' title='Borrar'></a></center></td>";
      }
      echo "</tr>";
   }
   echo "</table>";

   
   
   
   
   
   
   
   
   
   
   
   // Botón para mostrar las peticiones borradas/finalizadas y viceversa
   if ($_GET[borrados] == 'true'){
      echo "<button type='button'
               onClick=\"location.href='gconfiguracion.php'\">
               Modo normal
            </button>";
   } else {
      echo "<button type='button'
               onClick=\"location.href='gconfiguracion.php?borrados=true'\">
               Mostrar elementos eliminados
            </button>";
   }

   mysql_close();
   pie();
?>
