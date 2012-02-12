<?php
   include 'db.conf';
   include 'wrappers.php';
 
   cabecera("Editar una Reunion");
 
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
      
   $query = "SELECT  descripcion, fecha
            FROM ".dbname.".reunion
            WHERE id = $_GET[id]";
   $res = mysql_query($query) or die(mysql_error());
   $old = mysql_fetch_array($res);
   
   
   if (isset($_POST[enviar])){
      // Realizamos las actualizaciones

      // Descripción
      $descripcion = mysql_real_escape_string(trim(strip_tags(stripslashes($_POST[descripcion]))));
      $descripcion = str_replace('\\n', '<br>', $descripcion);
      if ($old[descripcion] != $descripcion){
         $query = "UPDATE ".dbname.".reunion SET descripcion = '$descripcion' WHERE id = $_POST[id_reu]";
         $res = mysql_query($query) or die(mysql_error());
      }

      // Fecha
	  $r_fecha = isset($_REQUEST["nr_fecha"]) ? $_REQUEST["nr_fecha"] : "";
      if ($old[fecha] != $r_fecha){
         $query = "UPDATE ".dbname.".reunion SET fecha = '$r_fecha' WHERE id = $_POST[id_reu]";
         $res = mysql_query($query) or die(mysql_error());
      }

      echo "Cambio modificado con éxito.<BR>";
      echo "<button type='button' onClick=\"location.href='gcambios.php'\">Volver</button>";

   } else {

      // Formulario
      echo "<form method='post' action='$PHP_SELF' name='formeditreunion' onsubmit='return validaFormulario();'>";
      echo "   <input type='hidden' name='id_reu'  value='$_GET[id]'>";
      echo "<H4>Editar la reunion \"$old[descripcion]\":</H4>";
      echo "<table border='0' cellspacing='0' summary='Editar el cambio'>";
	  
      $descripcion = str_replace('<br>', '\\n', $old[descripcion]);
      echo "   <tr><td>Descripción </td>
               <td> <p><textarea rows='5' name='descripcion' cols='28'>$descripcion</textarea></p> </td></tr>";
			   
	  //Fecha
	  echo "   <tr><td>Fecha </td>
	           <td>";
			       //get class into the page
                   require_once('calendar/tc_calendar.php');
                   $myCalendar_t = new tc_calendar("nr_fecha", true);
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