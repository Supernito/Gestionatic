<?php
   include 'db.conf';
   include 'wrappers.php';

   cabecera("Nueva versión");

   mysql_connect(dbhost,dbuser,dbpass);
   mysql_select_db(dbname);
   session_start();

   // Si no está logueado o no tiene permiso terminamos
   if ($_SESSION['logged']!=true){
      echo "No tienes permisos para ver esto.<BR>";
      mysql_close();pie();die();
   }

   // Miraramos los permisos para gestionar versiones. Los guardamos en $own
   $query = "SELECT is_admin,g_ver FROM ".dbname.".usuario WHERE username='$_SESSION[username]'";
   $res   = mysql_query($query) or die(mysql_error());
   $own   = mysql_fetch_array($res);

   // Si no tiene permisos morimos
   if ($own[is_admin] == 'false' && $own[g_conf] == 'false' && $own[g_prob]){
      echo "No tienes permisos suficientes.<BR>";
      mysql_close;pie();die();
   }

   
   // Comprobamos que viene con un id
   if(!isset($_GET[id])){
      echo "No se ha podido editar.<BR>";
      mysql_close();
      pie(); die();
   }
?>









   <script type="text/javascript">
      function validaFormulario(){
         var x = document.forms.formnuevoitem.nombre.value;
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
	  $nombre = $_POST['nombre'];
      $descripcion = mysql_real_escape_string(trim(strip_tags(stripslashes($_POST['descripcion']))));
      $descripcion = str_replace('\\n', '<br>', $descripcion);
      //$tipo_item = $_POST['tipo_item'];
	  $estado_item    = $_POST['estado_item'];
	  //$padre    = $_POST['padre'];
	   $id=$_GET[id];


      $query = "INSERT INTO  ".dbname.".version
                   (nombre, descripcion, codigo, item)
                VALUES ('$nombre', '$descripcion', '$estado_item', $id)";
      $res = mysql_query($query) or die(mysql_error());

      echo "Versión creada con éxito.<BR>";

      if (isset($_POST['origen'])){
         echo "<button type='button' onClick=\"location.href='".$_POST['origen']."'\">Volver</button>";
      } else {
         echo "<button type='button' onClick=\"location.href='gconfiguracion.php'  \">Volver a Gestión de configuración  </button>";
        // echo "<button type='button' onClick=\"location.href='gincidencias.php'\">Ir a Gestión de incidencias</button>";
      }
      echo "<BR>";

   } else {

     // Formulario

      echo "<H4>Introduzca los datos de la nueva versión:</H4>";
       echo "<form method='post' action='$PHP_SELF' name='formnuevaver' onsubmit='return validaFormulario();'>";
      echo "<table border='0' cellspacing='0' summary='Formulario nueva versión'>";
      echo "   <tr><td>Nombre(*) </td><td> <input type='text' name='nombre' size='30' maxlength='30' value=''> </td></tr>";
      echo "   <tr><td>Descripción</td><td> <textarea rows='5' name='descripcion' cols='28'></textarea> </td></tr>";
      
		  
	  echo "   <tr><td>Estado</td><td> <p><select name='estado_item'>";
      $query = "SELECT id, nombre from ".dbname.".est_it";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row=mysql_fetch_array($res)){
            echo "      <option value='$row[nombre]'>$row[nombre]</option></p>";
      }
	  

      echo "   </select> </td></tr></table>";
	 
	  
	  
	  

      echo "   (*)  Campo obligatorio.<BR>";
      echo "   <input type='submit' value='Enviar' name='enviar'>";
      //if (isset($_POST['origen'])){
        // echo "   <input  type=hidden name='origen'      value=$_POST[origen]>";
         echo "   <button type=button onClick=\"location.href='gconfiguracion.php'\">Cancelar</button>";
      //}
      echo "</form>";

   }

   mysql_close();
   pie();
?>