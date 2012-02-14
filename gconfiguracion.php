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
   
      // Miraramos los permisos para gestionar versiones
   $query = "SELECT is_admin,g_conf FROM ".dbname.".usuario WHERE username='$_SESSION[username]'";
   $res   = mysql_query($query) or die(mysql_error());
   $own2   = mysql_fetch_array($res);
   

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
   
      // Eliminamos versión seleccionada
   if($_GET[elim_ver] && is_numeric($_GET[elim_ver])){
       $query = " DELETE FROM ".dbname.".version
                   WHERE id = $_GET[elim_ver]";
      mysql_query($query) or die(mysql_error());
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
	 // echo "<tr><td> <b>Padre:</b> 		   </td><td> $row[padre]      </td></tr>";
	  		if  ($row[padre] == '0'){
			  echo "<tr><td> <b>Padre:</b></td><td>Ninguno</td></tr>";
		} else {
			 //echo "<tr><td> <b>Padre:</b> 		   </td><td> $row[padre]      </td></tr>";
			 //echo "   <td><center><a href='gconfiguracion.php?ver=$row[padre]'>$row[padre]</a></center></td>";
			 echo "<tr><td> <b>Padre:</b> 		   </td><td><a href='gconfiguracion.php?ver=$row[padre]'>$row[padre]</a></td></tr>";
		}
	  //Tabla de hijos
	  $query = "SELECT itemo.id
                FROM ".dbname.".item_id itemo
                WHERE $_GET[ver] = itemo.padre";	
      $res   = mysql_query($query) or die(mysql_error());
	//	echo "<H4></H4>";
		  echo "   <tr><td><b>Hijos: </b> </td>";
	  if(mysql_num_rows($res) != 0){
		  
		  echo "   <td>";
		  echo "<tr><td></td></tr>";
	      echo "<table border='1' cellspacing='0'>";
          echo "<tr> <td>
                   <b><center>Id</center></b>
                </td><td>
                   <b><center>Ver</center></b>
                </td></tr>";
	  
    	  while ($row = mysql_fetch_array($res)) {
	          echo "<tr onMouseOver='resaltaLinia(this)' onMouseOut='restauraLinia(this)'>";
	          echo "   <td><center>$row[id]</center></td>";
	          //$nom = $row[nombre];
	          //if (strlen($nom) > LIM_CAR_DES) $nom = substr($nom,0,LIM_CAR_DES - 3)."...";
			   echo "   <td><center><a href='gconfiguracion.php?ver=$row[id]'>
                  <img src='img/view.gif'   alt='Ver' title='Ver detalles'></a></center></td>";
	          echo "</tr>";
	      }
		  echo "   </td>";
          echo "</table></td></tr>";
      }else{
		echo "<H4></H4>";
	    echo "   <tr><td>No tiene hijos</td>";
	    echo "   <td>";
	  }
	echo "</table>";  
	  
    //tabla de servicios
	  $query = "SELECT servicio.id, servicio.nombre
                FROM ".dbname.".serv_item sitem
				LEFT JOIN ".dbname.".servicio 			on (sitem.servicio = servicio.id)
                WHERE $_GET[ver] = sitem.item";	
      $res   = mysql_query($query) or die(mysql_error());
	  
	  
		echo "<H4></H4>";
		  echo "   <tr><td><b>Servicios afectados: </b> </td>";
	  if(mysql_num_rows($res) != 0){
		  
		  echo "   <td>";
		  echo "<tr><td></td></tr>";
	      echo "<table border='1' cellspacing='0'>";
          echo "<tr> <td>
                   <b><center>Id</center></b>
                </td> <td>
                   <b><center>Nombre</center></b>
                </td> <td>
                   <b><center>Ver</center></b>
                </td></tr>";
	  
    	  while ($row = mysql_fetch_array($res)) {
	          echo "<tr onMouseOver='resaltaLinia(this)' onMouseOut='restauraLinia(this)'>";
	          echo "   <td><center>$row[id]</center></td>";
	          $nom = $row[nombre];
	          if (strlen($nom) > LIM_CAR_DES) $nom = substr($nom,0,LIM_CAR_DES - 3)."...";
    	      echo "   <td><center>$nom</center></td>";
			   echo "   <td><center><a href='gservicios.php?ver=$row[id]'>
                  <img src='img/view.gif'   alt='Ver' title='Ver detalles'></a></center></td>";
	          echo "</tr>";
	      }
		  echo "   </td>";
          echo "</table></td></tr>";
      }else{
		echo "<H4></H4>";
	    echo "   <tr><td>No está asociado a ningún servicio</td>";
	    echo "   <td>";
	  }
	  
	  echo "</table>";
	  
  //Tabla de versiones
  if ($own[is_admin] == 'true' || $own[g_configuracion] == 'true'){
	  $query = "SELECT version.id, version.nombre, version.descripcion, version.codigo
                FROM ".dbname.".version
                WHERE $_GET[ver] = version.item";	
      $res   = mysql_query($query) or die(mysql_error());
	  
	   if(mysql_num_rows($res) != 0){
		  echo "<H4></H4>";
		  echo "   <tr><td><b>Versiones: </b> </td>";
		  echo "   <td>";
		  echo "<tr><td></td></tr>";
		  echo "   <td>";
	      echo "<table border='1' cellspacing='0'>";
          echo "<tr> <td>
                   <b><center>Nombre</center></b>
                </td> <td>
                   <b><center>Descripción</center></b>
                </td> <td>
                   <b><center>Estado</center></b>
                </td><td>
               <b><center>Editar</center></b>
            </td> <td>
               <b><center>Borrar</center></b>
            </td></tr>";
	  
    	  while ($row = mysql_fetch_array($res)) {
	          echo "<tr onMouseOver='resaltaLinia(this)' onMouseOut='restauraLinia(this)'>";
	          //echo "   <td><center>$row[n]</center></td>";
	          $nom = $row[nombre];
	          if (strlen($nom) > LIM_CAR_DES) $nom = substr($nom,0,LIM_CAR_DES - 3)."...";
    	      echo "   <td><center>$nom</center></td>";
			  $des = $row[descripcion];
			  if (strlen($des) > LIM_CAR_DES) $des = substr($des,0,LIM_CAR_DES - 3)."...";
    	      echo "   <td><center>$des</center></td>";
			  echo "   <td><center>$row[codigo]</center></td>";
			  
			  echo "   <td><center><a href='editversion.php?id=$row[id]'>
                  <img src='img/edit.gif'   alt='Edit' title='Editar'></a></center></td>";
			  echo "   <td><center><a href='gconfiguracion.php?elim_ver=$row[id]' onclick='return asegurar();'>
                     <img src='img/delete.gif' alt='Borrar' title='Borrar'></a></center></td>";
					 
					 
	          echo "</tr>";
	      }
		  echo "   </td>";
          echo "</table></td></tr>";
      }else{
	   echo "<H4></H4>";
		 echo "<tr><td> <b>Versiones:</b></td><td>No existen versiones de este elemento</td></tr>";
		 echo "<H4></H4>";
		  echo "   </td>";
	  }
	  

	  
	echo "<button type='button' onClick=\"location.href='nuevaversion.php?id=$_GET[ver]'\">Añadir versión</button>";
	 echo "<H4></H4>";
		  echo "   </td>";
}
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
		if  ($padre == '0'){
			 echo "   <td><center></center></td>";
		} else {
			echo "   <td><center><a href='gconfiguracion.php?ver=$padre'>$padre</a></center></td>";
		}
	  
	  
	  
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
