<?php
   include 'db.conf';
   include 'wrappers.php';
 
   cabecera("Editar un elemento de configuraci�n");
 
   mysql_connect(dbhost,dbuser,dbpass); 
   mysql_select_db(dbname);
   session_start();

   // Controlar aqu� tambi�n que tenga permisos
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

   // Script de comprovaci�n
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
         return confirm("Esta entrada ser� eliminada. �Est� de acuerdo?");
      }
	  
	  function asegurar_s(id, idr) {
         if (confirm("Se eliminar� la relaci�n con este servicio. �Est� de acuerdo?")){
            location.href='editcambio.php?id='+id+'&s_elim='+idr;
            return true;
         }
         return false;
      }
	  /*
	  function asegurar_t(id, idt) {
         if (confirm("Esta tarea ser� eliminada. �Est� de acuerdo?")){
            location.href='editcambio.php?id='+id+'&t_elim='+idt;
            return true;
         }
         return false;
      }*/

   </script>

<?php

   // Recuperamos los viejos datos
      
   $query = "SELECT cam.id id, cam.descripcion descripcion, cam.padre padre, cam.tipo_item tipo
                
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
   
   
   if (isset($_POST[enviar])){
      // Realizamos las actualizaciones

      // Descripci�n
      $descripcion = mysql_real_escape_string(trim(strip_tags(stripslashes($_POST[descripcion]))));
      $descripcion = str_replace('\\n', '<br>', $descripcion);
      if ($old[descripcion] != $descripcion){
         $query = "UPDATE ".dbname.".item_id SET descripcion = '$descripcion' WHERE id = $_POST[id_con]";
         $res = mysql_query($query) or die(mysql_error());
         $query = "INSERT INTO ".dbname.".estado_item
                         (nombre, descripcion, fecha, item)
                   VALUES ('$_POST[estado_item]','Se ha cambiado la descripci�n.',
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
                   VALUES ('$_POST[estado_item]','Se ha cambiado el tipo de \"$row[t_old]\" a \"$row[t_new]\".', NOW(), $_POST[id_con])";
         $res = mysql_query($query) or die(mysql_error());
      }
	  

	  //Servicios
	  $servicios = $_POST[servicios];
      for ($i=0;$i<count($servicios);$i++){
          $query = "INSERT INTO ".dbname.".serv_item 
		                   (item, servicio) 
				    VALUES ('$_POST[id_con]', '$servicios[$i]')";
          $res = mysql_query($query) or die(mysql_error());
		  
		  $query = "INSERT INTO ".dbname.".estado_item
                      (nombre, descripcion, fecha, item)
                   VALUES ('$_POST[estado_item]','Se ha relacionado con un servicio.', NOW(), $_POST[id_con])";
         $res = mysql_query($query) or die(mysql_error());
	  }
  
        // Descripci�n
      $dpadre = $_POST[padre];

      if ($old[padre] != $dpadre){
         $query = "UPDATE ".dbname.".item_id SET padre = '$dpadre' WHERE id = $_POST[id_con]";
         $res = mysql_query($query) or die(mysql_error());
         $query = "INSERT INTO ".dbname.".estado_item
                         (nombre, descripcion, fecha, item)
                   VALUES ('$_POST[estado_item]','Se ha asignado un nuevo padre.',
                            NOW(), $_POST[id_con])";
         $res = mysql_query($query) or die(mysql_error());
      }
  

	  
	  echo "Elemento de configuraci�n actualizado.<BR>";
      echo "<button type='button' onClick=\"location.href='gconfiguracion.php'\">Volver</button>";
	  
	  
   } else {
   
      // Eliminamos la relacion item-servicio seleccionada
   if($_GET[s_elim] && is_numeric($_GET[s_elim])){
      $query = " DELETE FROM ".dbname.".serv_item
                   WHERE servicio = $_GET[s_elim] AND item = $_GET[id]";
      mysql_query($query) or die(mysql_error());
   }
   
   
   
   

      // Formulario
      echo "<form method='post' action='$PHP_SELF' name='formedititem' onsubmit='return validaFormulario();'>";
      echo "   <input type='hidden' name='id_con'  value='$_GET[id]'>";
      echo "<H4>Editar el elemento \"$old[id]\":</H4>";
      echo "<table border='0' cellspacing='0' summary='Editar el elemento'>";
	  
	  
      //echo "   <tr><td>Nombre(*)</td>
      //         <td> <p><input type='text' name='nombre' size='30' maxlength='30' value='$old[nombre]'> </p></td></tr>";

	  
	  
      $descripcion = str_replace('<br>', '\\n', $old[descripcion]);
      echo "   <tr><td>Descripci�n (*)</td>
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
	 			   
			   
	  echo "   <tr><td>Estado</td><td> <p><select name='estado_item'>";
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
	  
	  //tabla de servicios
	  $query = "SELECT servicio.id, servicio.nombre
                FROM ".dbname.".serv_item sitem
				LEFT JOIN ".dbname.".servicio 			on (sitem.servicio = servicio.id)
                WHERE $_GET[id] = sitem.item";	
      $res   = mysql_query($query) or die(mysql_error());
	  
	  
	  
	  if(mysql_num_rows($res) != 0){
		  echo "<H4></H4>";
		  echo "   <tr><td>Servicios </td>";
		  echo "   <td>";
	      echo "<table border='1' cellspacing='0'>";
          echo "<tr> <td>
                   <b><center>Id</center></b>
                </td> <td>
                   <b><center>Nombre</center></b>
                </td> <td>
                   <b><center>Borrar</center></b>
                </td> </tr>";
	  
    	  while ($row = mysql_fetch_array($res)) {
	          echo "<tr onMouseOver='resaltaLinia(this)' onMouseOut='restauraLinia(this)'>";
	          echo "   <td><center>$row[id]</center></td>";
	          $nom = $row[nombre];
	          if (strlen($nom) > LIM_CAR_DES) $nom = substr($nom,0,LIM_CAR_DES - 3)."...";
    	      echo "   <td><center>$nom</center></td>";
			  

	          if ($_GET[borrados] == 'true'){
		         echo "   <td><center>-</center></td>";
	          } else {
		         echo "   <td><center><a href='editconfiguracion.php?id=$_GET[id]&s_elim=$row[id]' onclick='return asegurar_s();'>
                              <img src='img/delete.gif' alt='Borrar' title='Borrar'></a></center></td>";
	          }
	          echo "</tr>";
	      }
		  echo "   </td>";
          echo "</table></td></tr>";
      }else{
		echo "<H4></H4>";
	    echo "   <tr><td>No est� asociado a ning�n servicio</td>";
	    echo "   <td>";
	  }
	  

	        echo "   <tr><td>Padre </td><td> <select name='padre'>";
      $query = "SELECT id, descripcion from ".dbname.".item_id";
				  
      $res   = mysql_query($query) or die(mysql_error());
	  //Opcion sin padre	  
		 echo "      <option value=0>Sin padre</option>";
		 
      while ($row=mysql_fetch_array($res)){
		if(($row['descripcion'] != 'eliminado')){
			echo "      <option value='".$row['id']."'>".$row['descripcion']."</option>";
		 }
      } 
	  
	  
	    echo "   </select> </td></tr>";
      echo "   </select> </td></tr></table>";
	 
	  //echo "</table>";
	  
	  echo "<H4></H4>";
      echo "(*) Campo obliglatorio.<BR>";
	  echo "   (**) Usa CTRL para seleccionar/desseleccionar.<BR>";
      echo "<input type='submit' value='Enviar' name='enviar'>";
      echo "<button type='button' onClick=\"location.href='gconfiguracion.php'\">Cancelar</button>";
      echo "</form>";
   }

   mysql_close();
   pie();
?>
