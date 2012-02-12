<?php
   include 'db.conf';
   include 'wrappers.php';
 
   cabecera("Editar un elemento de configuración");
 
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
   $query = "SELECT is_admin,g_conf FROM ".dbname.".usuario WHERE username='$_SESSION[username]'";
   $res   = mysql_query($query) or die(mysql_error());
   $own   = mysql_fetch_array($res);

   // Si no tiene permisos morimos
   if ($own[is_admin] == 'false' && $own[g_conf] == 'false'){
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
         var x = document.forms.formedititem.nombre.value;
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
	  
	  /*function asegurar_r(id, idr) {
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
      }*/

   </script>

<?php

   // Recuperamos los viejos datos
      
   $query = "SELECT cam.id id, cam.descripcion descripcion, cam.padre padre
                
            FROM ".dbname.".item_id cam
            WHERE cam.id = $_GET[id]";
   $res = mysql_query($query) or die(mysql_error());
   $old = mysql_fetch_array($res);
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   $query = "SELECT id, nombre
            FROM ".dbname.".estado_item
            WHERE id=(SELECT max(id) FROM ".dbname.".estado_item 
			WHERE item = $_GET[id] group by item)";
   $res = mysql_query($query) or die(mysql_error());
   $old_est = mysql_fetch_array($res);

  /* // Eliminamos la reunion seleccionada
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
   }*/
   
   
   if (isset($_POST[enviar])){
      // Realizamos las actualizaciones

      // Nombre
     /* if ($old[nombre] != $_POST[nombre]){
         $query = "SELECT count(*) num FROM ".dbname.".peticion_cambio cam
                     LEFT JOIN ".dbname.".estado_peticion ep on (ep.peticion_cambio = cam.id)
                     WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_peticion
                                  WHERE peticion_cambio = cam.id group by peticion_cambio) AND
                     ep.nombre != 'eliminado' AND cam.nombre='$_POST[nombre]'";
         $res   = mysql_query($query) or die(mysql_error());
         $row   = mysql_fetch_array($res);
         $num   = $row['num'];

         if ($num=='0'){
            $query = "UPDATE ".dbname.".peticion_cambio SET nombre = '$_POST[nombre]' WHERE id = $_POST[id_con]";
            $res = mysql_query($query) or die(mysql_error());
            $query = "INSERT INTO ".dbname.".estado_peticion
                         (nombre, descripcion, fecha, peticion_cambio)
                      VALUES ('$_POST[estado_pet]','Se ha cambiado el nombre de \"$old[nombre]\" a \"$_POST[nombre]\".',
                              NOW(), $_POST[id_con])";
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
                              NOW(), $_POST[id_con])";
         $res = mysql_query($query) or die(mysql_error());
	  
	  }*/

      // Descripción
      $descripcion = mysql_real_escape_string(trim(strip_tags(stripslashes($_POST[descripcion]))));
      $descripcion = str_replace('\\n', '<br>', $descripcion);
      if ($old[descripcion] != $descripcion){
         $query = "UPDATE ".dbname.".item_id SET descripcion = '$descripcion' WHERE id = $_POST[id_con]";
         $res = mysql_query($query) or die(mysql_error());
         $query = "INSERT INTO ".dbname.".estado_item
                         (nombre, descripcion, fecha, item)
                   VALUES ('$_POST[estado_pet]','Se ha cambiado la descripción.',
                            NOW(), $_POST[id_con])";
         $res = mysql_query($query) or die(mysql_error());
      }

      // Tipo
      if ($old[tipo] != $_POST[titem]){
         $query = "UPDATE ".dbname.".item_id SET tipo_item = '$_POST[titem]' WHERE id = $_POST[id_con]";
         $res = mysql_query($query) or die(mysql_error());
		 $query = "SELECT nombre t_old, (SELECT nombre FROM ".dbname.".tipo_item WHERE id=$_POST[titem]) t_new
                     FROM ".dbname.".tipo_item WHERE id=$old[tipo]";
         $res = mysql_query($query) or die(mysql_error());
         $row = mysql_fetch_array($res);
         $query = "INSERT INTO ".dbname.".estado_item
                         (nombre, descripcion, fecha, item)
                   VALUES ('$_POST[estado_pet]','Se ha cambiado el problema de \"$row[t_old]\" a \"$row[t_new]\".',
                           NOW(), $_POST[id_con])";
         $res = mysql_query($query) or die(mysql_error());
      }

      
	  
	  
	  
	  
	  
	  
	  
	  //Servicios
	  $servicios = $_POST[servicios];
      for ($i=0;$i<count($servicios);$i++){
          $query = "INSERT INTO ".dbname.".serv_item 
		                   (servicio, item) 
				    VALUES ('$_POST[id_con]', '$servicios[$i]')";
          $res = mysql_query($query) or die(mysql_error());
		  
		  $query = "INSERT INTO ".dbname.".serv_item
                      (nombre, descripcion, fecha, item_id)
                   VALUES ('$_POST[estado_item]','Se ha relacionado con un servicio.', NOW(), $_POST[id_con])";
         $res = mysql_query($query) or die(mysql_error());
	  }
      echo "Elemento modificado con éxito.<BR>";
      echo "<button type='button' onClick=\"location.href='gconfiguracion.php'\">Volver</button>";

	  
	  
	  
	  
	  
	  
	  
   } else {

      // Formulario
      echo "<form method='post' action='$PHP_SELF' name='formedititem' onsubmit='return validaFormulario();'>";
      echo "   <input type='hidden' name='id_cam'  value='$_GET[id]'>";
      echo "<H4>Editar el elemento \"$old[id]\":</H4>";
      echo "<table border='0' cellspacing='0' summary='Editar el elemento'>";
	  
	  
      //echo "   <tr><td>Nombre(*)</td>
      //         <td> <p><input type='text' name='nombre' size='30' maxlength='30' value='$old[nombre]'> </p></td></tr>";

	  
	  
      $descripcion = str_replace('<br>', '\\n', $old[descripcion]);
      echo "   <tr><td>Descripción (*)</td>
               <td> <p><textarea rows='5' name='descripcion' cols='28'>$descripcion</textarea></p> </td></tr>";
      echo "   <tr><td>Tipo </td><td> <p><select name='titem'>";
      $query = "SELECT id, nombre from ".dbname.".tipo_item";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row=mysql_fetch_array($res)){
         if ($old[tipo] == $row[id]){
            echo "      <option selected value='$row[id]'>$row[nombre]</option>";
         } else {
            echo "      <option value='$row[id]'>$row[nombre]</option></p>";
         }
      }
	 			   
			   
	  echo "   <tr><td>Estado</td><td> <p><select name='estado_pet'>";
      $query = "SELECT id, nombre from ".dbname.".est_it";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row=mysql_fetch_array($res)){
         if ($old_est[nombre] == $row[nombre]){
            echo "      <option selected value='$row[nombre]'>$row[nombre]</option>";
         } else {
            echo "      <option value='$row[nombre]'>$row[nombre]</option></p>";
         }
      } 
	  

	    echo "</table>";
	  

	  //relacionar con servicios
	  $query = "SELECT servicio.id, servicio.nombre FROM ".dbname.".servicio
	            LEFT JOIN ".dbname.".serv_item on (servicio.id = serv_item.item AND serv_item.item = $_GET[id])
				WHERE serv_item.servicio IS NULL ";
      $res   = mysql_query($query) or die(mysql_error());
	  if(mysql_num_rows($res) != 0){
	     echo "   <tr><td>Servicios(**) </td><td> <select name='servicios[]' multiple>";
         while ($row=mysql_fetch_array($res)){
            echo "      <option value='$row[id]'>$row[nombre]</option>";
         }
         echo "   </select> </td></tr>";
	  }

	  
	  

	  

	  
	  echo "</table>";
      echo "(*) Campo obliglatorio.<BR>";
	  echo "   (**) Usa CTRL para seleccionar/desseleccionar.<BR>";
      echo "<input type='submit' value='Enviar' name='enviar'>";
      echo "<button type='button' onClick=\"location.href='gconfiguracion.php'\">Cancelar</button>";
      echo "</form>";
   }

   mysql_close();
   pie();
?>
