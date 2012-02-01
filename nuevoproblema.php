<?php
   include 'db.conf';
   include 'wrappers.php';

   cabecera("Nuevo Problema");

   mysql_connect(dbhost,dbuser,dbpass);
   mysql_select_db(dbname);
   session_start();

   // Si no está logueado o no tiene permiso terminamos
   if ($_SESSION['logged']!=true){
      echo "No tienes permisos para ver esto.<BR>";
      mysql_close();
      pie(); die();
   }

   // Miraramos los permisos para gestionar incidencias. Los guardamos en $own
   $query = "SELECT is_admin,g_inc,g_prob FROM ".dbname.".usuario WHERE username='$_SESSION[username]'";
   $res   = mysql_query($query) or die(mysql_error());
   $own   = mysql_fetch_array($res);

   // Si no tiene permisos morimos
   if ($own[is_admin] == 'false' && $own[g_inc] == 'false' && $own[g_prob]){
      echo "No tienes permisos suficientes.";
      mysql_close;
      pie();die();
   }

   // Script de comprovación
?>

   <script type="text/javascript">
      function validaFormulario(){
         var x = document.forms.formnuevoproblema.nombre.value;
         if(x==null || x==""){
            alert("Algunos campos no pueden estar vacios.");
            return false;               
         }else{
            return true;
         }
      }
   </script>

<?php

   if(isset($_POST['enviar'])) {

      $nombre      = $_POST['nombre'];
      $descripcion = mysql_real_escape_string(trim(strip_tags(stripslashes($_POST['descripcion']))));
      $descripcion = str_replace('\\n', '<br>', $descripcion);
      $urgencia    = $_POST['urgencia'];
      $impacto     = $_POST['impacto'];
      $prioridad   = $_POST['prioridad'];

      // Comprovación que el nombre no esté ya en la BBDD (y si está que esté borrado)

      $query = "SELECT count(*) num FROM ".dbname.".problema prob
                  LEFT JOIN ".dbname.".estado_prob ep on (ep.problema = prob.id)
                  WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_prob
                               WHERE problema = prob.id group by problema) AND
                  ep.nombre != 'eliminado' AND prob.nombre='$nombre'";
      $res   = mysql_query($query) or die(mysql_error());
      $row   = mysql_fetch_array($res);
      $num   = $row['num'];

      if ($num=='0'){
         if (!isset($_POST['incidencia'])){
            $query = "INSERT INTO  ".dbname.".problema
                         (nombre, descripcion, urgencia, impacto, prioridad)
                      VALUES ('$nombre', '$descripcion', '$urgencia', '$impacto', '$prioridad')";
         } else {
            $incidencia = $_POST['incidencia'];
            $query = "INSERT INTO  ".dbname.".problema
                         (nombre, descripcion, urgencia, impacto, prioridad, incidencia)
                      VALUES ('$nombre', '$descripcion', '$urgencia', '$impacto', '$prioridad', '$incidencia')";
         }
         $res = mysql_query($query) or die(mysql_error());
         $id = mysql_insert_id ();
         $query = "INSERT INTO ".dbname.".estado_prob
                      (nombre, descripcion, username, fecha, problema)
                   VALUES ('creado','Se ha creado el problema', '$_SESSION[username]', NOW(), $id)";
         $res = mysql_query($query) or die(mysql_error());
         echo "Problema insertado con éxito.<BR>";
      } else {
         echo "Ya existe un problema activo con este nombre.<BR>";
      }

      if (isset($_POST['origen'])){
         echo "<button type='button' onClick=\"location.href='".$_POST['origen']."'\">Volver</button>";
      } else {
         echo "<button type='button' onClick=\"location.href='gproblemas.php'  \">Ir a Gestión de problemas  </button>";
         echo "<button type='button' onClick=\"location.href='gincidencias.php'\">Ir a Gestión de incidencias</button>";
      }
      echo "<BR>";

   } else {

      // Formulario

      echo "<H4>Introduzca los datos de un nuevo problema:</H4>";
      if (isset($_POST['incidencia'])){
         $query = "SELECT nombre from ".dbname.".incidencia where id=".$_POST['incidencia'];
         $res   = mysql_query($query) or die(mysql_error());
         $row   = mysql_fetch_array($res);
         echo "Nota: El problema estarà relacionado con la incidencia \"".$row['nombre']."\" <BR>";
      }

      echo "<form method='post' action='$PHP_SELF' name='formnuevoproblema' onsubmit='return validaFormulario();'>";
      echo "<table border='0' cellspacing='0' summary='Formulario nuevo problema'>";
      echo "   <tr><td>Nombre(*) </td><td> <input type='text' name='nombre' size='30' maxlength='30' value=''> </td></tr>";
      echo "   <tr><td>Descripción </td><td> <textarea rows='5' name='descripcion' cols='28'></textarea> </td></tr>";
      echo "   <tr><td>Urgencia </td><td> <select name='urgencia'>";
      $query = "SELECT id, nombre from ".dbname.".urgencia_prob";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row=mysql_fetch_array($res)){
         echo "      <option value='".$row['id']."'>".$row['nombre']."</option>";
      }
      echo "   </select> </td></tr>";
      echo "   <tr><td>Impacto </td><td> <select name='impacto'>";
      $query = "SELECT id, nombre from ".dbname.".impacto_prob";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row=mysql_fetch_array($res)){
         echo "      <option value='".$row['id']."'>".$row['nombre']."</option>";
      }
      echo "   </select> </td></tr>";
      echo "   <tr><td>Prioridad </td><td> <select name='prioridad'>";
      $query = "SELECT id, nombre from ".dbname.".prioridad_prob";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row=mysql_fetch_array($res)){
         echo "      <option value='".$row['id']."'>".$row['nombre']."</option>";
      }
      echo "   </select> </td></tr></table>";
      echo "   (*) Campo obligatorio <BR>";
      echo "   <input type='submit' value='Enviar' name='enviar'>";
      if (isset($_POST['origen'])){
         echo "   <input  type=hidden name='incidencia'  value=$_POST[incidencia]>";
         echo "   <input  type=hidden name='origen'      value=$_POST[origen]>";
         echo "   <button type=button onClick=\"location.href='$_POST[origen]'\">Cancelar</button>";
      }
      echo "</form>";

   }

   mysql_close();
   pie();
?>
