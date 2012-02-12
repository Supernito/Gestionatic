<?php
   include 'db.conf';
   include 'wrappers.php';
 
   cabecera("Editar un Cambio");
 
   mysql_connect(dbhost,dbuser,dbpass); 
   mysql_select_db(dbname);
   session_start();

   // Controlar aquí también que tenga permisos
   if ($_SESSION['logged']!=true){
      // No hay nadie logueado
      echo "Hay que estar logueado para ver las funcionalidades<BR>";
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

   // Comprobamos que viene con un id
   if(!isset($_GET[id])){
      echo "No se ha podido editar.<BR>";
      mysql_close();
      pie(); die();
   }

   // Script de comprovación
?>

   <script type="text/javascript">
      function validaFormulario(){
         var x = document.forms.formeditcambio.nombre.value;
         if(x==null || x==""){
            alert("Algunos campos no pueden estar vacios.");
            return false;               
         }else{
            return true;
         }
      }
	  
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
	  
	  function asegurar_r(id, idr) {
         if (confirm("Esta reunión será eliminada. ¿Está de acuerdo?")){
            location.href='editcambio.php?id='+id+'&r_elim='+idr;
            return true;
         }
         return false;
      }
	  
	  function asegurar_t(id, idt) {
         if (confirm("Esta tarea será eliminada. ¿Está de acuerdo?")){
            location.href='editcambio.php?id='+id+'&t_elim='+idt;
            return true;
         }
         return false;
      }
	  
	  function asegurar_i(id, idt) {
         if (confirm("Esta relación será eliminada. ¿Está de acuerdo?")){
            location.href='editcambio.php?id='+id+'&i_elim='+idt;
            return true;
         }
         return false;
      }

   </script>

<?php

   // Recuperamos los viejos datos
      
   $query = "SELECT cam.nombre nombre, cam.descripcion descripcion, 
                    cam.tipo_cambio tipo, cam.problema problema
            FROM ".dbname.".peticion_cambio cam
            WHERE cam.id = $_GET[id]";
   $res = mysql_query($query) or die(mysql_error());
   $old = mysql_fetch_array($res);
   
   $query = "SELECT id, nombre
            FROM ".dbname.".estado_peticion
            WHERE id=(SELECT max(id) FROM ".dbname.".estado_peticion 
			WHERE peticion_cambio = $_GET[id] group by peticion_cambio)";
   $res = mysql_query($query) or die(mysql_error());
   $old_est = mysql_fetch_array($res);

   // Eliminamos la reunion seleccionada
   if($_GET[r_elim] && is_numeric($_GET[r_elim])){
      $query = " DELETE FROM ".dbname.".reunion
                   WHERE id = $_GET[r_elim]";
      mysql_query($query) or die(mysql_error());
   }
   
   // Eliminamos la tarea seleccionada
   if($_GET[t_elim] && is_numeric($_GET[t_elim])){
      $query = " DELETE FROM ".dbname.".tarea
                   WHERE id = $_GET[t_elim]";
      mysql_query($query) or die(mysql_error());
   }
   
   // Eliminamos la relacion item-cambio seleccionada
   if($_GET[i_elim] && is_numeric($_GET[i_elim])){
      $query = " DELETE FROM ".dbname.".cambio_item
                   WHERE item = $_GET[i_elim] AND peticion_cambio = $_GET[id]";
      mysql_query($query) or die(mysql_error());
   }
   
   
   if (isset($_POST[enviar])){
      // Realizamos las actualizaciones

      // Nombre
      if ($old[nombre] != $_POST[nombre]){
         $query = "SELECT count(*) num FROM ".dbname.".peticion_cambio cam
                     LEFT JOIN ".dbname.".estado_peticion ep on (ep.peticion_cambio = cam.id)
                     WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_peticion
                                  WHERE peticion_cambio = cam.id group by peticion_cambio) AND
                     ep.nombre != 'eliminado' AND cam.nombre='$_POST[nombre]'";
         $res   = mysql_query($query) or die(mysql_error());
         $row   = mysql_fetch_array($res);
         $num   = $row['num'];

         if ($num=='0'){
            $query = "UPDATE ".dbname.".peticion_cambio SET nombre = '$_POST[nombre]' WHERE id = $_POST[id_cam]";
            $res = mysql_query($query) or die(mysql_error());
            $query = "INSERT INTO ".dbname.".estado_peticion
                         (nombre, descripcion, fecha, peticion_cambio)
                      VALUES ('$_POST[estado_pet]','Se ha cambiado el nombre de \"$old[nombre]\" a \"$_POST[nombre]\".',
                              NOW(), $_POST[id_cam])";
            $res = mysql_query($query) or die(mysql_error());
         } else {
            echo "Ya existe una peticion de cambio activa con este nombre.<BR>";
            echo "<button type='button' onClick=\"location.href='gcambios.php'\">Volver</button>";
            mysql_close();
            pie(); die();
         }
      }
	  
	  if($old_est[nombre] != $_POST[estado_pet]){
	     $query = "INSERT INTO ".dbname.".estado_peticion
                         (nombre, descripcion, fecha, peticion_cambio)
                      VALUES ('$_POST[estado_pet]','Se ha cambiado el estado de la peticion de cambio de \"$old_est[nombre]\" a \"$_POST[estado_pet]\".',
                              NOW(), $_POST[id_cam])";
         $res = mysql_query($query) or die(mysql_error());
	  
	  }

      // Descripción
      $descripcion = mysql_real_escape_string(trim(strip_tags(stripslashes($_POST[descripcion]))));
      $descripcion = str_replace('\\n', '<br>', $descripcion);
      if ($old[descripcion] != $descripcion){
         $query = "UPDATE ".dbname.".peticion_cambio SET descripcion = '$descripcion' WHERE id = $_POST[id_cam]";
         $res = mysql_query($query) or die(mysql_error());
         $query = "INSERT INTO ".dbname.".estado_peticion
                         (nombre, descripcion, fecha, peticion_cambio)
                   VALUES ('$_POST[estado_pet]','Se ha cambiado la descripción.',
                            NOW(), $_POST[id_cam])";
         $res = mysql_query($query) or die(mysql_error());
      }

      // Tipo de cambio
      if ($old[tipo] != $_POST[tcambio]){
         $query = "UPDATE ".dbname.".peticion_cambio SET tipo_cambio = '$_POST[tcambio]' WHERE id = $_POST[id_cam]";
         $res = mysql_query($query) or die(mysql_error());
		 $query = "SELECT nombre t_old, (SELECT nombre FROM ".dbname.".tipo_cambio WHERE id=$_POST[tcambio]) t_new
                     FROM ".dbname.".tipo_cambio WHERE id=$old[tipo]";
         $res = mysql_query($query) or die(mysql_error());
         $row = mysql_fetch_array($res);
         $query = "INSERT INTO ".dbname.".estado_peticion
                         (nombre, descripcion, fecha, peticion_cambio)
                   VALUES ('$_POST[estado_pet]','Se ha cambiado el problema de \"$row[t_old]\" a \"$row[t_new]\".',
                           NOW(), $_POST[id_cam])";
         $res = mysql_query($query) or die(mysql_error());
      }

      // Problema
      if ($old[problema] != $_POST[problema]){
         $query = "UPDATE ".dbname.".peticion_cambio SET problema = '$_POST[problema]' WHERE id = $_POST[id_cam]";
         $res = mysql_query($query) or die(mysql_error());
         $query = "SELECT nombre p_old, (SELECT nombre FROM ".dbname.".problema WHERE id=$_POST[problema]) p_new
                     FROM ".dbname.".problema WHERE id=$old[problema]";
         $res = mysql_query($query) or die(mysql_error());
         $row = mysql_fetch_array($res);
         $query = "INSERT INTO ".dbname.".estado_peticion
                         (nombre, descripcion, fecha, peticion_cambio)
                   VALUES ('$_POST[estado_pet]','Se ha cambiado el problema de \"$row[p_old]\" a \"$row[p_new]\".',
                           NOW(), $_POST[id_cam])";
         $res = mysql_query($query) or die(mysql_error());
      }
	  
	  //Nueva Reunion
	  if($_POST[nr_desc] != ""){
	     $r_fecha = isset($_REQUEST["nr_fecha"]) ? $_REQUEST["nr_fecha"] : "";
		// echo "mydate is: " . $r_fecha . "<br />";  <--- segun esto, funciona :S
         $query = "INSERT INTO ".dbname.".reunion
                         (descripcion, fecha, peticion_cambio)
                   VALUES ('$_POST[nr_desc]', '$r_fecha', $_POST[id_cam])";
		 $res = mysql_query($query) or die(mysql_error());
         $query = "INSERT INTO ".dbname.".estado_peticion
                      (nombre, descripcion, fecha, peticion_cambio)
                   VALUES ('$_POST[estado_pet]','Se ha añadido una reunión.', NOW(), $_POST[id_cam])";
         $res = mysql_query($query) or die(mysql_error());
	  }
	  
	  //Nueva Tarea
	  if($_POST[nt_desc] != ""){
	     $t_fecha = isset($_REQUEST["nt_fecha"]) ? $_REQUEST["nt_fecha"] : "";
		// echo "mydate is: " . $r_fecha . "<br />";  <--- segun esto, funciona :S
         $query = "INSERT INTO ".dbname.".tarea
                         (descripcion, estado, fecha, peticion_cambio)
                   VALUES ('$_POST[nt_desc]', 'pendiente', '$t_fecha', $_POST[id_cam])";
		 $res = mysql_query($query) or die(mysql_error());
         $query = "INSERT INTO ".dbname.".estado_peticion
                      (nombre, descripcion, fecha, peticion_cambio)
                   VALUES ('$_POST[estado_pet]','Se ha añadido una tarea.', NOW(), $_POST[id_cam])";
         $res = mysql_query($query) or die(mysql_error());
	  }
	  
	  //Items configuracion
	  $items = $_POST[items];
      for ($i=0;$i<count($items);$i++){
          $query = "INSERT INTO ".dbname.".cambio_item 
		                   (peticion_cambio, item) 
				    VALUES ('$_POST[id_cam]', '$items[$i]')";
          $res = mysql_query($query) or die(mysql_error());
		  
		  $query = "INSERT INTO ".dbname.".estado_peticion
                      (nombre, descripcion, fecha, peticion_cambio)
                   VALUES ('$_POST[estado_pet]','Se ha relacionado con un item de configuracion.', NOW(), $_POST[id_cam])";
         $res = mysql_query($query) or die(mysql_error());
	  }
      echo "Cambio modificado con éxito.<BR>";
      echo "<button type='button' onClick=\"location.href='gcambios.php'\">Volver</button>";

   } else {

      // Formulario
      echo "<form method='post' action='$PHP_SELF' name='formeditcambio' onsubmit='return validaFormulario();'>";
      echo "   <input type='hidden' name='id_cam'  value='$_GET[id]'>";
      echo "<H4>Editar el cambio \"$old[nombre]\":</H4>";
      echo "<table border='0' cellspacing='0' summary='Editar el cambio'>";
	  
	  
      echo "   <tr><td>Nombre(*)</td>
               <td> <p><input type='text' name='nombre' size='30' maxlength='30' value='$old[nombre]'> </p></td></tr>";
			   
			   
	  echo "   <tr><td>Estado</td><td> <p><select name='estado_pet'>";
      $query = "SELECT id, nombre from ".dbname.".est_pet";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row=mysql_fetch_array($res)){
         if ($old_est[nombre] == $row[nombre]){
            echo "      <option selected value='$row[nombre]'>$row[nombre]</option>";
         } else {
            echo "      <option value='$row[nombre]'>$row[nombre]</option></p>";
         }
      }
	  
	  
      $descripcion = str_replace('<br>', '\\n', $old[descripcion]);
      echo "   <tr><td>Descripción </td>
               <td> <p><textarea rows='5' name='descripcion' cols='28'>$descripcion</textarea></p> </td></tr>";
      echo "   <tr><td>Tipo de cambio </td><td> <p><select name='tcambio'>";
      $query = "SELECT id, nombre from ".dbname.".tipo_cambio";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row=mysql_fetch_array($res)){
         if ($old[tipo] == $row[id]){
            echo "      <option selected value='$row[id]'>$row[nombre]</option>";
         } else {
            echo "      <option value='$row[id]'>$row[nombre]</option></p>";
         }
      }
	  
	  
      echo "   <tr><td>Problema </td><td> <p><select name='problema'>";
      $query = "SELECT id, nombre from ".dbname.".problema";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row=mysql_fetch_array($res)){
         if ($old[problema] == $row[id]){
            echo "      <option selected value='$row[id]'>$row[nombre]</option>";
         } else {
            echo "      <option value='$row[id]'>$row[nombre]</option></p>";
         }
      }
	  
	  
	  // nueva reunion
	  echo "   <tr><td>Nueva reunión </td>
	           <td>";
			       //get class into the page
                   require_once('calendar/tc_calendar.php');

                   $myCalendar_r = new tc_calendar("nr_fecha", true);
	               $myCalendar_r->setIcon("calendar/images/iconCalendar.gif");
	               $myCalendar_r->setDate(date('d'), date('m'), date('Y'));
	               $myCalendar_r->setPath("calendar/");
	               $myCalendar_r->setYearInterval(1980, 2030);
	               $myCalendar_r->dateAllow('1980-01-01', '2030-21-31');

                   //output the calendar
                   $myCalendar_r->writeScript();	
	  echo "    </td></tr><tr><td></td><td><textarea rows='3' name='nr_desc' cols='28'></textarea></td></tr>";
	  
	  
	  //nueva tarea
	  echo "   <tr><td>Nueva tarea </td>
	           <td>";
                   $myCalendar_t = new tc_calendar("nt_fecha", true);
	               $myCalendar_t->setIcon("calendar/images/iconCalendar.gif");
	               $myCalendar_t->setDate(date('d'), date('m'), date('Y'));
	               $myCalendar_t->setPath("calendar/");
	               $myCalendar_t->setYearInterval(1980, 2030);
	               $myCalendar_t->dateAllow('1980-01-01', '2030-21-31');
	               $myCalendar_t->writeScript();	
	  echo "    </td></tr><tr><td></td><td><textarea rows='3' name='nt_desc' cols='28'></textarea></td></tr>";
	  
	  
	  //relacionar con items de configuracion
	  $query = "SELECT item_id.id, item_id.descripcion FROM ".dbname.".item_id
	            LEFT JOIN ".dbname.".cambio_item on (item_id.id = cambio_item.item AND cambio_item.peticion_cambio = $_GET[id])
				WHERE cambio_item.item IS NULL ";
      $res   = mysql_query($query) or die(mysql_error());
	  if(mysql_num_rows($res) != 0){
	     echo "   <tr><td>Items de configuracion(**) </td><td> <select name='items[]' multiple>";
         while ($row=mysql_fetch_array($res)){
            echo "      <option value='$row[id]'>$row[descripcion]</option>";
         }
         echo "   </select> </td></tr>";
	  }
	  
	  /*
	  *
	  *Aqui las cosas de reuniones, tareas e items de config.
	  *
	  */
	  
	  //Reuniones
	  //detalles
	  if($_GET[r_ver] && is_numeric($_GET[r_ver])){
	  $query = "SELECT descripcion, fecha
               FROM ".dbname.".reunion
               WHERE id = $_GET[r_ver]";
      $res = mysql_query($query) or die(mysql_error());
      $row = mysql_fetch_array($res);
	  
      
      echo "<tr><td>Detalles de la reunion \"$_GET[r_ver]\":</td><td><table border='0' cellspacing='0' summary='Detalles de la reunión'>";
      echo "<tr><td> <b>ID:</b>            </td><td> $_GET[r_ver]        </td></tr>";
      echo "<tr><td> <b>Descripción:</b>   </td><td> $row[descripcion] </td></tr>";
	  echo "<tr><td> <b>Fecha:</b>        </td><td> $row[fecha]      </td></tr>";
	  
	  echo "<tr><td><button type='button' onClick=\"location.href='editreunion.php?id=$_GET[r_ver]'\">Editar Reunión</button></td>";
      echo "<td><button type='button' onClick='return asegurar_r($_GET[id], $_GET[r_ver]);'>Borrar</button></td></tr>";
      echo "<tr><td><button type='button' onClick=\"location.href='editcambio.php?id=$_GET[id]'\">Ocultar</button></td></tr>";
	  echo "</table></td></tr>";
	  }
	  
	  //tabla de reuniones
	  echo "   <tr><td>Reuniones </td>";
	  echo "   <td>";		
	  $query = "SELECT id, descripcion,
                   fecha
               FROM ".dbname.".reunion
               WHERE peticion_cambio = $_GET[id]";
      $res   = mysql_query($query) or die(mysql_error());
	  if(mysql_num_rows($res) != 0){
	      echo "<table border='1' cellspacing='0'>";
          echo "<tr> <td>
                   <b><center>Id</center></b>
                </td> <td>
                   <b><center>Descripción</center></b>
                </td> <td>
                   <b><center>Fecha</center></b>
                </td> <td>
                   <b><center>Ver</center></b>
                </td> <td>
                   <b><center>Borrar</center></b>
                </td> </tr>";
	  
    	  while ($row = mysql_fetch_array($res)) {
	          echo "<tr onMouseOver='resaltaLinia(this)' onMouseOut='restauraLinia(this)'>";
	          echo "   <td><center>$row[id]</center></td>";
	          $descrip = $row[descripcion];
	          if (strlen($descrip) > LIM_CAR_DES) $descrip = substr($descrip,0,LIM_CAR_DES - 3)."...";
    	      echo "   <td><center>$descrip</center></td>";
	          echo "   <td><center>$row[fecha]</center></td>";
			  echo "   <td><center><a href='editcambio.php?id=$_GET[id]&r_ver=$row[id]'>
                  <img src='img/view.gif'   alt='Ver' title='Ver detalles'></a></center></td>";
	          if ($_GET[borrados] == 'true'){
		         echo "   <td><center>-</center></td>";
	          } else {
		         echo "   <td><center><a href='editcambio.php?id=$_GET[id]&r_elim=$row[id]' onclick='return asegurar();'>
                              <img src='img/delete.gif' alt='Borrar' title='Borrar'></a></center></td>";
	          }
	          echo "</tr>";
	      }
		  echo "   </td>";
          echo "</table></td></tr>";
      }
	  
	  
	  //Tareas
	  //detalles
	  if($_GET[t_ver] && is_numeric($_GET[t_ver])){
	  $query = "SELECT descripcion, estado, fecha
               FROM ".dbname.".tarea
               WHERE id = $_GET[t_ver]";
      $res = mysql_query($query) or die(mysql_error());
      $row = mysql_fetch_array($res);
	  
      
      echo "<tr><td>Detalles de la tarea \"$_GET[t_ver]\":</td><td><table border='0' cellspacing='0' summary='Detalles de la reunión'>";
      echo "<tr><td> <b>ID:</b>            </td><td> $_GET[t_ver]        </td></tr>";
      echo "<tr><td> <b>Descripción:</b>   </td><td> $row[descripcion] </td></tr>";
	  echo "<tr><td> <b>Descripción:</b>   </td><td> $row[estado] </td></tr>";
	  echo "<tr><td> <b>Fecha:</b>        </td><td> $row[fecha]      </td></tr>";
	  
	  echo "<tr><td><button type='button' onClick=\"location.href='edittarea.php?id=$_GET[t_ver]'\">Editar Tarea</button></td>";
      echo "<td><button type='button' onClick='return asegurar_t($_GET[id], $_GET[t_ver]);'>Borrar</button></td></tr>";
      echo "<tr><td><button type='button' onClick=\"location.href='editcambio.php?id=$_GET[id]'\">Ocultar</button></td></tr>";
	  echo "</table></td></tr>";
	  }
	  //tabla de tareas
	  echo "   <tr><td>Tareas </td>";
	  echo "   <td>";		
	  $query = "SELECT id, descripcion,
                   estado, fecha
               FROM ".dbname.".tarea
               WHERE peticion_cambio = $_GET[id]";
      $res   = mysql_query($query) or die(mysql_error());
	  if(mysql_num_rows($res) != 0){
	      echo "<table border='1' cellspacing='0'>";
          echo "<tr> <td>
                   <b><center>Id</center></b>
                </td> <td>
                   <b><center>Descripción</center></b>
                </td> <td>
                   <b><center>Estado</center></b>
                </td> <td>
                   <b><center>Fecha</center></b>
                </td> <td>
                   <b><center>Ver</center></b>
                </td> <td>
                   <b><center>Borrar</center></b>
                </td> </tr>";
	  
    	  while ($row = mysql_fetch_array($res)) {
	          echo "<tr onMouseOver='resaltaLinia(this)' onMouseOut='restauraLinia(this)'>";
	          echo "   <td><center>$row[id]</center></td>";
	          $descrip = $row[descripcion];
	          if (strlen($descrip) > LIM_CAR_DES) $descrip = substr($descrip,0,LIM_CAR_DES - 3)."...";
    	      echo "   <td><center>$descrip</center></td>";
	          echo "   <td><center>$row[estado]</center></td>";
			  echo "   <td><center>$row[fecha]</center></td>";
			  echo "   <td><center><a href='editcambio.php?id=$_GET[id]&t_ver=$row[id]'>
                  <img src='img/view.gif'   alt='Ver' title='Ver detalles'></a></center></td>";
	          if ($_GET[borrados] == 'true'){
		         echo "   <td><center>-</center></td>";
	          } else {
		         echo "   <td><center><a href='editcambio.php?id=$_GET[id]&t_elim=$row[id]' onclick='return asegurar();'>
                              <img src='img/delete.gif' alt='Borrar' title='Borrar'></a></center></td>";
	          }
	          echo "</tr>";
	      }
		  echo "   </td>";
          echo "</table></td>";
      }
	  
	  
	  //Items configuracion
	  //tabla de items
	  echo "   <tr><td>Items </td>";
	  echo "   <td>";		
	  $query = "SELECT item_id.id, item_id.descripcion, titem.nombre tipo
                FROM ".dbname.".cambio_item citem
				LEFT JOIN ".dbname.".item_id               on (citem.item = item_id.id)
				LEFT JOIN ".dbname.".tipo_item       titem on (titem.id = item_id.tipo_item)
                WHERE $_GET[id] = citem.peticion_cambio";
      $res   = mysql_query($query) or die(mysql_error());
	  if(mysql_num_rows($res) != 0){
	      echo "<table border='1' cellspacing='0'>";
          echo "<tr> <td>
                   <b><center>Id</center></b>
                </td> <td>
                   <b><center>Descripción</center></b>
                </td> <td>
                   <b><center>Tipo</center></b>
                </td> <td>
                   <b><center>Borrar</center></b>
                </td> </tr>";
	  
    	  while ($row = mysql_fetch_array($res)) {
	          echo "<tr onMouseOver='resaltaLinia(this)' onMouseOut='restauraLinia(this)'>";
	          echo "   <td><center>$row[id]</center></td>";
	          $descrip = $row[descripcion];
	          if (strlen($descrip) > LIM_CAR_DES) $descrip = substr($descrip,0,LIM_CAR_DES - 3)."...";
    	      echo "   <td><center>$descrip</center></td>";
	          echo "   <td><center>$row[tipo]</center></td>";
	          if ($_GET[borrados] == 'true'){
		         echo "   <td><center>-</center></td>";
	          } else {
		         echo "   <td><center><a href='editcambio.php?id=$_GET[id]&i_elim=$row[id]' onclick='return asegurar_i();'>
                              <img src='img/delete.gif' alt='Borrar' title='Borrar'></a></center></td>";
	          }
	          echo "</tr>";
	      }
		  echo "   </td>";
          echo "</table></td></tr>";
      }
	  
	 /*
	  *
	  *Fin de las cosas esas de reuniones, tareas e items.
	  *
	  */
	  
	  echo "</table>";
      echo "(*) Campo obliglatorio.<BR>";
	  echo "   (**) Usa CTRL para seleccionar/desseleccionar.<BR>";
      echo "<input type='submit' value='Enviar' name='enviar'>";
      echo "<button type='button' onClick=\"location.href='gcambios.php'\">Cancelar</button>";
      echo "</form>";
   }

   mysql_close();
   pie();
?>
