<?php
   include 'db.conf';
   include 'wrappers.php';

   define (LIM_CAR_DES,'30'); // Carácteres a mostrar en Descripción
   $zona_horaria="+06:00";

   cabecera("Gestión de Problemas");

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

   // Miraramos los permisos para gestionar problemas. Los guardamos en $own
   $query = "SELECT is_admin,g_prob FROM ".dbname.".usuario WHERE username='$_SESSION[username]'";
   $res   = mysql_query($query) or die(mysql_error());
   $own   = mysql_fetch_array($res);

   // Si no tiene permisos morimos
   if ($own[is_admin] == 'false' && $own[g_prob] == 'false'){
      echo "No tienes permisos suficientes.";
      mysql_close;
      pie();die();
   }

   // Nuevo problema
   echo "<form action='nuevoproblema.php' method='POST'>";
   echo "   <input type='hidden' name='origen'  value='gproblemas.php'>";
   echo "   <input type='submit' class='button' name='nuevoProblema' value='Introducir nuevo problema'/>";
   echo "</form>";

   // Eliminamos el problema seleccionado
   if($_GET[elim] && is_numeric($_GET[elim])){
      // No se elimina, se pone en el estado eliminado
      $query = "INSERT INTO ".dbname.".estado_prob
                   (nombre, descripcion, username, fecha, problema)
                VALUES ('eliminado','Se ha borrado/solucionado el problema', '$_SESSION[username]', NOW(), $_GET[elim])";
      mysql_query($query) or die(mysql_error());
      echo "Se eliminó el problema con el identificador $_GET[elim] <BR>";
   }

   // Detalles del problema seleccionado
   if($_GET[ver] && is_numeric($_GET[ver])){
      // A diferéncia de l'altre aquí sortirien tots els estats que ha tingut
      $query = "SELECT prob.nombre nombre, prob.descripcion descripcion,
                   u.nombre urgencia, i.nombre impacto, p.nombre prioridad,
                   ep.nombre estado
               FROM ".dbname.".problema prob
               LEFT JOIN ".dbname.".urgencia_prob  u on (u.id        = prob.urgencia)
               LEFT JOIN ".dbname.".impacto_prob   i on (i.id        = prob.impacto)
               LEFT JOIN ".dbname.".prioridad_prob p on (p.id        = prob.prioridad)
               LEFT JOIN ".dbname.".estado_prob   ep on (ep.problema = prob.id)
               WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_prob
                               WHERE problema = prob.id group by problema) AND
                  prob.id = $_GET[ver]";
      $res = mysql_query($query) or die(mysql_error());
      $row = mysql_fetch_array($res);
      echo "<H4>Detalles del problema \"$row[nombre]\":</H4>";
      $estado = $row[estado];
      echo "<table border='0' cellspacing='0' summary='Detalles del problema'>";
      echo "<tr><td> <b>ID:</b>            </td><td> $_GET[ver]        </td></tr>";
      echo "<tr><td> <b>Nombre:</b>        </td><td> $row[nombre]      </td></tr>";
      echo "<tr><td> <b>Descripción:</b>   </td><td> $row[descripcion] </td></tr>";
      echo "<tr><td> <b>Urgencia:</b>      </td><td> $row[urgencia]    </td></tr>";
      echo "<tr><td> <b>Impacto:</b>       </td><td> $row[impacto]     </td></tr>";
      echo "<tr><td> <b>Prioridad:</b>     </td><td> $row[prioridad]   </td></tr>";
      echo "<tr><td> <b>Estado actual:</b> </td><td> $row[estado]      </td></tr>";
      echo "<tr><td> <b>Incidencias:</b>   </td><td> </td></tr>";
      $query = "SELECT id,nombre,descripcion
                FROM ".dbname.".incidencia
                WHERE problema=$_GET[ver]";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row = mysql_fetch_array($res)){
         echo "<tr><td> <a href='gincidencias.php?ver=$row[id]'>$row[nombre]</a> </td><td> $row[descripcion] </td></tr>";
      }
      echo "<tr><td> <b>Historial:</b>     </td><td> </td></tr>";
      $query = "SELECT descripcion, username, fecha
                FROM ".dbname.".estado_prob
                WHERE problema=$_GET[ver]";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row = mysql_fetch_array($res)){
         echo "<tr><td> ".date(dateadd("suma",$row[fecha],0,0,0,6,0,0))." </td><td> <i>$row[username]</i> --> $row[descripcion] </td></tr>";
      }
      echo "</table>";
      if ($estado != 'eliminado'){
         echo "<button type='button' onClick=\"location.href='editproblema.php?id=$_GET[ver]'\">Editar Problema</button>";
         echo "<button type='button' onClick='return asegurar2($_GET[ver]);'>Borrar</button>";
      }
      echo "<BR><button type='button' onClick=\"location.href='gproblemas.php'\">Ocultar</button>";
   }

   // Problemas
   if ($_GET[borrados] == 'true'){
      echo "<H4>Problemas Borrados/Solucionados:</H4>";
   } else {
      echo "<H4>Problemas Existentes:</H4>";
   }
   echo "<table border='1' cellspacing='0'>";
   echo "<tr> <td>
               <b><center>Id</center></b>
            </td> <td>
               <b><center>Nombre</center></b>
            </td> <td>
               <b><center>Descripción</center></b>
            </td> <td>
               <b><center>Urgencia</center></b>
            </td> <td>
               <b><center>Impacto</center></b>
            </td> <td>
               <b><center>Prioridad</center></b>
            </td> <td>
               <b><center>Estado</center></b>
            </td> <td>
               <b><center>Ver</center></b>
            </td> <td>
               <b><center>Borrar</center></b>
            </td> </tr>";

   if ($_GET[borrados] == 'true'){
      $query = "SELECT prob.id id, prob.nombre nombre, prob.descripcion descripcion,
                   u.nombre urgencia, i.nombre impacto, p.nombre prioridad,
                   ep.nombre estado
               FROM ".dbname.".problema prob
               LEFT JOIN ".dbname.".urgencia_prob  u on (u.id        = prob.urgencia)
               LEFT JOIN ".dbname.".impacto_prob   i on (i.id        = prob.impacto)
               LEFT JOIN ".dbname.".prioridad_prob p on (p.id        = prob.prioridad)
               LEFT JOIN ".dbname.".estado_prob   ep on (ep.problema = prob.id)
               WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_prob
                               WHERE problema = prob.id group by problema) AND
                  ep.nombre = 'eliminado'";
   } else {
      $query = "SELECT prob.id id, prob.nombre nombre, prob.descripcion descripcion,
                   u.nombre urgencia, i.nombre impacto, p.nombre prioridad,
                   ep.nombre estado
               FROM ".dbname.".problema prob
               LEFT JOIN ".dbname.".urgencia_prob  u on (u.id        = prob.urgencia)
               LEFT JOIN ".dbname.".impacto_prob   i on (i.id        = prob.impacto)
               LEFT JOIN ".dbname.".prioridad_prob p on (p.id        = prob.prioridad)
               LEFT JOIN ".dbname.".estado_prob   ep on (ep.problema = prob.id)
               WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_prob
                               WHERE problema = prob.id group by problema) AND
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
      echo "   <td><center>$row[urgencia]</center></td>";
      echo "   <td><center>$row[impacto]</center></td>";
      echo "   <td><center>$row[prioridad]</center></td>";
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

   // Botón para mostrar los problemas borrados/solucionados i viceversa
   if ($_GET[borrados] == 'true'){
      echo "<button type='button'
               onClick=\"location.href='gproblemas.php'\">
               Modo normal
            </button>";
   } else {
      echo "<button type='button'
               onClick=\"location.href='gproblemas.php?borrados=true'\">
               Mostrar problemas<BR>borrados/solucionados
            </button>";
   }

   mysql_close();
   pie();
?>
