<?php
   include 'db.conf';
   include 'wrappers.php';
 
   cabecera("Editar un Problema");
 
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
   $query = "SELECT is_admin,g_prob FROM ".dbname.".usuario WHERE username='$_SESSION[username]'";
   $res   = mysql_query($query) or die(mysql_error());
   $own   = mysql_fetch_array($res);

   // Si no tiene permisos morimos
   if ($own[is_admin] == 'false' && $own[g_prob] == 'false'){
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
      
   $query = "SELECT prob.nombre nombre, prob.descripcion descripcion,
                prob.urgencia urgencia, prob.impacto impacto,
                prob.prioridad prioridad, inc.nombre incidencia
            FROM ".dbname.".problema prob
            LEFT JOIN ".dbname.".incidencia   inc on (inc.id = prob.incidencia)
            WHERE prob.id = $_GET[id]";
   $res = mysql_query($query) or die(mysql_error());
   $old = mysql_fetch_array($res);

   if (isset($_POST[enviar])){
      // Realizamos las actualizaciones

      // Nombre
      if ($old[nombre] != $_POST[nombre]){
         $query = "SELECT count(*) num FROM ".dbname.".problema prob
                     LEFT JOIN ".dbname.".estado_prob ep on (ep.problema = prob.id)
                     WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_prob
                                  WHERE problema = prob.id group by problema) AND
                     ep.nombre != 'eliminado' AND prob.nombre='$_POST[nombre]'";
         $res   = mysql_query($query) or die(mysql_error());
         $row   = mysql_fetch_array($res);
         $num   = $row['num'];

         if ($num=='0'){
            $query = "UPDATE ".dbname.".problema SET nombre = '$_POST[nombre]' WHERE id = $_POST[id_prob]";
            $res = mysql_query($query) or die(mysql_error());
            $query = "INSERT INTO ".dbname.".estado_prob
                         (nombre, descripcion, username, fecha, problema)
                      VALUES ('modificado','Se ha cambiado el nombre de \"$old[nombre]\" a \"$_POST[nombre]\".',
                              '$_SESSION[username]', NOW(), $_POST[id_prob])";
            $res = mysql_query($query) or die(mysql_error());
         } else {
            echo "Ya existe un problema activo con este nombre.<BR>";
            echo "<button type='button' onClick=\"location.href='gproblemas.php'\">Volver</button>";
            mysql_close();
            pie(); die();
         }
      }

      // Descripción
      $descripcion = mysql_real_escape_string(trim(strip_tags(stripslashes($_POST[descripcion]))));
      $descripcion = str_replace('\\n', '<br>', $descripcion);
      if ($old[descripcion] != $descripcion){
         $query = "UPDATE ".dbname.".problema SET descripcion = '$descripcion' WHERE id = $_POST[id_prob]";
         $res = mysql_query($query) or die(mysql_error());
         $query = "INSERT INTO ".dbname.".estado_prob
                      (nombre, descripcion, username, fecha, problema)
                   VALUES ('modificado','Se ha cambiado la descripción.',
                           '$_SESSION[username]', NOW(), $_POST[id_prob])";
         $res = mysql_query($query) or die(mysql_error());
      }

      // Urgencia
      if ($old[urgencia] != $_POST[urgencia]){
         $query = "UPDATE ".dbname.".problema SET urgencia = '$_POST[urgencia]' WHERE id = $_POST[id_prob]";
         $res = mysql_query($query) or die(mysql_error());
         $query = "SELECT nombre u_old, (SELECT nombre FROM ".dbname.".urgencia_prob WHERE id=$_POST[urgencia]) u_new
                     FROM ".dbname.".urgencia_prob WHERE id=$old[urgencia]";
         $res = mysql_query($query) or die(mysql_error());
         $row = mysql_fetch_array($res);
         $query = "INSERT INTO ".dbname.".estado_prob
                      (nombre, descripcion, username, fecha, problema)
                   VALUES ('modificado','Se ha cambiado la urgencia de \"$row[u_old]\" a \"$row[u_new]\".',
                           '$_SESSION[username]', NOW(), $_POST[id_prob])";
         $res = mysql_query($query) or die(mysql_error());
      }

      // Impacto
      if ($old[impacto] != $_POST[impacto]){
         $query = "UPDATE ".dbname.".problema SET impacto = '$_POST[impacto]' WHERE id = $_POST[id_prob]";
         $res = mysql_query($query) or die(mysql_error());
         $query = "SELECT nombre i_old, (SELECT nombre FROM ".dbname.".impacto_prob WHERE id=$_POST[impacto]) i_new
                     FROM ".dbname.".impacto_prob WHERE id=$old[impacto]";
         $res = mysql_query($query) or die(mysql_error());
         $row = mysql_fetch_array($res);
         $query = "INSERT INTO ".dbname.".estado_prob
                      (nombre, descripcion, username, fecha, problema)
                   VALUES ('modificado','Se ha cambiado el impacto de \"$row[i_old]\" a \"$row[i_new]\".',
                           '$_SESSION[username]', NOW(), $_POST[id_prob])";
         $res = mysql_query($query) or die(mysql_error());
      }

      // Prioridad
      if ($old[prioridad] != $_POST[prioridad]){
         $query = "UPDATE ".dbname.".problema SET prioridad = '$_POST[prioridad]' WHERE id = $_POST[id_prob]";
         $res = mysql_query($query) or die(mysql_error());
         $query = "SELECT nombre p_old, (SELECT nombre FROM ".dbname.".prioridad_prob WHERE id=$_POST[prioridad]) p_new
                     FROM ".dbname.".prioridad_prob WHERE id=$old[prioridad]";
         $res = mysql_query($query) or die(mysql_error());
         $row = mysql_fetch_array($res);
         $query = "INSERT INTO ".dbname.".estado_prob
                      (nombre, descripcion, username, fecha, problema)
                   VALUES ('modificado','Se ha cambiado la prioridad de \"$row[p_old]\" a \"$row[p_new]\".',
                           '$_SESSION[username]', NOW(), $_POST[id_prob])";
         $res = mysql_query($query) or die(mysql_error());
      }

      echo "Problema modificado con éxito.<BR>";
      echo "<button type='button' onClick=\"location.href='gproblemas.php'\">Volver</button>";

   } else {

      // Formulario
      echo "<form method='post' action='$PHP_SELF' name='formeditproblema' onsubmit='return validaFormulario();'>";
      echo "   <input type='hidden' name='id_prob'  value='$_GET[id]'>";
      echo "<H4>Editar el problema \"$old[nombre]\":</H4>";
      echo "<table border='0' cellspacing='0' summary='Editar el problema'>";
      echo "   <tr><td>Nombre(*) </td>
               <td> <input type='text' name='nombre' size='30' maxlength='30' value='$old[nombre]'> </td></tr>";
      $descripcion = str_replace('<br>', '\\n', $old[descripcion]);
      echo "   <tr><td>Descripción </td>
               <td> <textarea rows='5' name='descripcion' cols='28'>$descripcion</textarea> </td></tr>";
      echo "   <tr><td>Urgencia </td><td> <select name='urgencia'>";
      $query = "SELECT id, nombre from ".dbname.".urgencia_prob";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row=mysql_fetch_array($res)){
         if ($old[urgencia] == $row[id]){
            echo "      <option selected value='$row[id]'>$row[nombre]</option>";
         } else {
            echo "      <option value='$row[id]'>$row[nombre]</option>";
         }
      }
      echo "   <tr><td>Impacto </td><td> <select name='impacto'>";
      $query = "SELECT id, nombre from ".dbname.".impacto_prob";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row=mysql_fetch_array($res)){
         if ($old[impacto] == $row[id]){
            echo "      <option selected value='$row[id]'>$row[nombre]</option>";
         } else {
            echo "      <option value='$row[id]'>$row[nombre]</option>";
         }
      }
      echo "   <tr><td>Prioridad </td><td> <select name='prioridad'>";
      $query = "SELECT id, nombre from ".dbname.".prioridad_prob";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row=mysql_fetch_array($res)){
         if ($old[prioridad] == $row[id]){
            echo "      <option selected value='$row[id]'>$row[nombre]</option>";
         } else {
            echo "      <option value='$row[id]'>$row[nombre]</option>";
         }
      }
      echo "</table><input type='submit' value='Enviar' name='enviar'>";
      echo "<button type='button' onClick=\"location.href='gproblemas.php'\">Cancelar</button>";
      echo "</form>";
   }

   mysql_close();
   pie();
?>
