<?php
   include 'db.conf';
   include 'wrappers.php';
 
   cabecera("Editar una Tarea");
 
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

   </script>

<?php

   // Recuperamos los viejos datos
      
   $query = "SELECT  descripcion, estado, fecha
            FROM ".dbname.".tarea
            WHERE id = $_GET[id]";
   $res = mysql_query($query) or die(mysql_error());
   $old = mysql_fetch_array($res);
   
   
   if (isset($_POST[enviar])){
      // Realizamos las actualizaciones
      // Estado
	  if($old_est[nombre] != $_POST[estado_pet]){
	     $query = "UPDATE ".dbname.".tarea SET estado = '$estado_tar' WHERE id = $_POST[id_tar]";
         $res = mysql_query($query) or die(mysql_error());
	  
	  }

      // Descripción
      $descripcion = mysql_real_escape_string(trim(strip_tags(stripslashes($_POST[descripcion]))));
      $descripcion = str_replace('\\n', '<br>', $descripcion);
      if ($old[descripcion] != $descripcion){
         $query = "UPDATE ".dbname.".tarea SET descripcion = '$descripcion' WHERE id = $_POST[id_tar]";
         $res = mysql_query($query) or die(mysql_error());
      }

      // Fecha
	  $t_fecha = isset($_REQUEST["nt_fecha"]) ? $_REQUEST["nt_fecha"] : "";
      if ($old[fecha] != $t_fecha){
         $query = "UPDATE ".dbname.".tarea SET fecha = '$t_fecha' WHERE id = $_POST[id_tar]";
         $res = mysql_query($query) or die(mysql_error());
      }

      echo "Cambio modificado con éxito.<BR>";
      echo "<button type='button' onClick=\"location.href='gcambios.php'\">Volver</button>";

   } else {

      // Formulario
      echo "<form method='post' action='$PHP_SELF' name='formedittarea' onsubmit='return validaFormulario();'>";
      echo "   <input type='hidden' name='id_tar'  value='$_GET[id]'>";
      echo "<H4>Editar la tarea \"$old[descripcion]\":</H4>";
      echo "<table border='0' cellspacing='0' summary='Editar el cambio'>";
			   
			   
	  echo "   <tr><td>Estado</td><td> <p><select name='estado_tar'>";
      $query = "SELECT id, nombre from ".dbname.".est_tar";
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
			   
	  //Fecha
	  echo "   <tr><td>Fecha </td>
	           <td>";
			       //get class into the page
                   require_once('calendar/tc_calendar.php');
                   $myCalendar_t = new tc_calendar("nt_fecha", true);
	               $myCalendar_t->setIcon("calendar/images/iconCalendar.gif");
	               $myCalendar_t->setDate(date('d'), date('m'), date('Y'));
	               $myCalendar_t->setPath("calendar/");
	               $myCalendar_t->setYearInterval(1980, 2030);
	               $myCalendar_t->dateAllow('1980-01-01', '2030-21-31');
	               $myCalendar_t->writeScript();

	  echo "</table>";
      echo "<input type='submit' value='Enviar' name='enviar'>";
      echo "<button type='button' onClick=\"location.href='gcambios.php'\">Cancelar</button>";
      echo "</form>";
   }

   mysql_close();
   pie();
?>