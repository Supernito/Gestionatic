<?php
   include 'db.conf';
   include 'wrappers.php';

   cabecera("Nueva Peticion de cambio");

   mysql_connect(dbhost,dbuser,dbpass);
   mysql_select_db(dbname);
   session_start();

   // Si no está logueado o no tiene permiso terminamos
   if ($_SESSION['logged']!=true){
      echo "No tienes permisos para ver esto.<BR>";
      mysql_close();pie();die();
   }

   // Miraramos los permisos para gestionar problemas. Los guardamos en $own
   $query = "SELECT is_admin,g_cambios,g_prob FROM ".dbname.".usuario WHERE username='$_SESSION[username]'";
   $res   = mysql_query($query) or die(mysql_error());
   $own   = mysql_fetch_array($res);

   // Si no tiene permisos morimos
   if ($own[is_admin] == 'false' && $own[g_cambios] == 'false' && $own[g_prob]){
      echo "No tienes permisos suficientes.<BR>";
      mysql_close;pie();die();
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
      $tipo_cambio = $_POST['tipo_cambio'];
	  $problema    = $_POST['problema'];

      // Comprovación que el nombre no esté ya en la BBDD (y si está que esté borrado)
      $query = "SELECT count(*) num FROM ".dbname.".peticion_cambio cam
                  LEFT JOIN ".dbname.".estado_peticion ep on (ep.peticion_cambio = cam.id)
                  WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_peticion
                               WHERE peticion_cambio = cam.id group by peticion_cambio) AND
                  ep.nombre != 'eliminado' AND cam.nombre='$nombre'";
      $res   = mysql_query($query) or die(mysql_error());
      $row   = mysql_fetch_array($res);
      $num   = $row['num'];

      if ($num!='0'){
         echo "Ya existe una petición de cambio activa con este nombre.<BR>";
         if (isset($_POST['origen'])){
            echo "<button type='button' onClick=\"location.href='".$_POST['origen']."'\">Volver</button>";
         } else {
            echo "<button type='button' onClick=\"location.href='gcambios.php'  \">Ir a Gestión de cambios  </button>";
            echo "<button type='button' onClick=\"location.href='gproblemas.php'\">Ir a Gestión de problemas</button>";
         }
         echo "<BR>";mysql_close();pie();die();
      }

      $query = "INSERT INTO  ".dbname.".peticion_cambio
                   (nombre, descripcion, tipo_cambio, problema)
                VALUES ('$nombre', '$descripcion', '$tipo_cambio', '$problema')";
      $res = mysql_query($query) or die(mysql_error());
      $id = mysql_insert_id ();
      $query = "INSERT INTO ".dbname.".estado_peticion
                   (nombre, descripcion, fecha, peticion_cambio)
                VALUES ('creado','Se ha creado la peticion de cambio', NOW(), $id)";
      $res = mysql_query($query) or die(mysql_error());
      echo "Peticion de cambio creada con éxito.<BR>";

      if (isset($_POST['origen'])){
         echo "<button type='button' onClick=\"location.href='".$_POST['origen']."'\">Volver</button>";
      } else {
         echo "<button type='button' onClick=\"location.href='gproblemas.php'  \">Ir a Gestión de problemas  </button>";
         echo "<button type='button' onClick=\"location.href='gincidencias.php'\">Ir a Gestión de incidencias</button>";
      }
      echo "<BR>";

   } else {

     // Formulario

      echo "<H4>Introduzca los datos de la nueva peticion de cambio:</H4>";
      if (isset($_POST['problema'])){
         $query = "SELECT nombre from ".dbname.".problema where id=".$_POST['problema'];
         $res   = mysql_query($query) or die(mysql_error());
         $row   = mysql_fetch_array($res);
         echo "Nota: La peticion de cambio estará relacionada con el problema \"".$row['nombre']."\" <BR>";
      }

      echo "<form method='post' action='$PHP_SELF' name='formnuevocambio' onsubmit='return validaFormulario();'>";
      echo "<table border='0' cellspacing='0' summary='Formulario nueva peticion de cambio'>";
      echo "   <tr><td>Nombre(*) </td><td> <input type='text' name='nombre' size='30' maxlength='30' value=''> </td></tr>";
      echo "   <tr><td>Descripción </td><td> <textarea rows='5' name='descripcion' cols='28'></textarea> </td></tr>";
      echo "   <tr><td>Tipo </td><td> <select name='tipo_cambio'>";
      $query = "SELECT id, nombre from ".dbname.".tipo_cambio";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row=mysql_fetch_array($res)){
         echo "      <option value='".$row['id']."'>".$row['nombre']."</option>";
      }
      echo "   </select> </td></tr>";
      echo "   <tr><td>Problema </td><td> <select name='problema'>";
	  echo "   <option></option>";
      $query = "SELECT id, nombre from ".dbname.".problema";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row=mysql_fetch_array($res)){
         echo "      <option value='".$row['id']."'>".$row['nombre']."</option>";
      }
      echo "   </select> </td></tr></table>";

      echo "   (*)  Campo obligatorio.<BR>";
      echo "   <input type='submit' value='Enviar' name='enviar'>";
      if (isset($_POST['origen'])){
         echo "   <input  type=hidden name='origen'      value=$_POST[origen]>";
         echo "   <button type=button onClick=\"location.href='$_POST[origen]'\">Cancelar</button>";
      }
      echo "</form>";

   }

   mysql_close();
   pie();
?>