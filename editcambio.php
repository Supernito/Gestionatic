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
         var x = document.forms.formeditproblema.nombre.value;
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
      
   $query = "SELECT cam.nombre nombre, cam.descripcion descripcion, 
                    cam.tipo_cambio tipo, cam.problema problema
            FROM ".dbname.".peticion_cambio cam
            WHERE cam.id = $_GET[id]";
   $res = mysql_query($query) or die(mysql_error());
   $old = mysql_fetch_array($res);

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
                      VALUES ('modificado','Se ha cambiado el nombre de \"$old[nombre]\" a \"$_POST[nombre]\".',
                              NOW(), $_POST[id_cam])";
            $res = mysql_query($query) or die(mysql_error());
         } else {
            echo "Ya existe una peticion de cambio activa con este nombre.<BR>";
            echo "<button type='button' onClick=\"location.href='gcambios.php'\">Volver</button>";
            mysql_close();
            pie(); die();
         }
     }

      // Descripción
      $descripcion = mysql_real_escape_string(trim(strip_tags(stripslashes($_POST[descripcion]))));
      $descripcion = str_replace('\\n', '<br>', $descripcion);
      if ($old[descripcion] != $descripcion){
         $query = "UPDATE ".dbname.".peticion_cambio SET descripcion = '$descripcion' WHERE id = $_POST[id_cam]";
         $res = mysql_query($query) or die(mysql_error());
         $query = "INSERT INTO ".dbname.".estado_peticion
                         (nombre, descripcion, fecha, peticion_cambio)
                   VALUES ('modificado','Se ha cambiado la descripción.',
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
                   VALUES ('modificado','Se ha cambiado el problema de \"$row[t_old]\" a \"$row[t_new]\".',
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
                   VALUES ('modificado','Se ha cambiado el problema de \"$row[p_old]\" a \"$row[p_new]\".',
                           NOW(), $_POST[id_cam])";
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
      echo "   <tr><td>Nombre(*) </td>
               <td> <input type='text' name='nombre' size='30' maxlength='30' value='$old[nombre]'> </td></tr>";
      $descripcion = str_replace('<br>', '\\n', $old[descripcion]);
      echo "   <tr><td>Descripción </td>
               <td> <textarea rows='5' name='descripcion' cols='28'>$descripcion</textarea> </td></tr>";
      echo "   <tr><td>Tipo de cambio </td><td> <select name='tcambio'>";
      $query = "SELECT id, nombre from ".dbname.".tipo_cambio";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row=mysql_fetch_array($res)){
         if ($old[tipo] == $row[id]){
            echo "      <option selected value='$row[id]'>$row[nombre]</option>";
         } else {
            echo "      <option value='$row[id]'>$row[nombre]</option>";
         }
      }
      echo "   <tr><td>Problema </td><td> <select name='problema'>";
      $query = "SELECT id, nombre from ".dbname.".problema";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row=mysql_fetch_array($res)){
         if ($old[problema] == $row[id]){
            echo "      <option selected value='$row[id]'>$row[nombre]</option>";
         } else {
            echo "      <option value='$row[id]'>$row[nombre]</option>";
         }
      }
      echo "</table>";
      echo "(*) Campo obliglatorio.<BR>";
      echo "<input type='submit' value='Enviar' name='enviar'>";
      echo "<button type='button' onClick=\"location.href='gcambios.php'\">Cancelar</button>";
      echo "</form>";
   }

   mysql_close();
   pie();
?>
