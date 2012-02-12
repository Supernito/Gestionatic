<?php
   include 'db.conf';
   include 'wrappers.php';

   cabecera("Nuevo elemento de configuración");

   mysql_connect(dbhost,dbuser,dbpass);
   mysql_select_db(dbname);
   session_start();

   // Si no está logueado o no tiene permiso terminamos
   if ($_SESSION['logged']!=true){
      echo "No tienes permisos para ver esto.<BR>";
      mysql_close();pie();die();
   }

   // Miraramos los permisos para gestionar problemas. Los guardamos en $own
   $query = "SELECT is_admin,g_conf FROM ".dbname.".usuario WHERE username='$_SESSION[username]'";
   $res   = mysql_query($query) or die(mysql_error());
   $own   = mysql_fetch_array($res);

   // Si no tiene permisos morimos
   if ($own[is_admin] == 'false' && $own[g_conf] == 'false' && $own[g_prob]){
      echo "No tienes permisos suficientes.<BR>";
      mysql_close;pie();die();
   }

   // Script de comprovación
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

      $descripcion = mysql_real_escape_string(trim(strip_tags(stripslashes($_POST['descripcion']))));
      $descripcion = str_replace('\\n', '<br>', $descripcion);
      $tipo_item = $_POST['tipo_item'];
	  $estado_item    = $_POST['estado_item'];
	  $padre    = $_POST['padre'];



      $query = "INSERT INTO  ".dbname.".item_id
                   (descripcion, tipo_item, padre)
                VALUES ('$descripcion', '$tipo_item', '$padre')";
      $res = mysql_query($query) or die(mysql_error());
      $id = mysql_insert_id ();
      $query = "INSERT INTO ".dbname.".estado_item
                   (nombre, descripcion, fecha, item)
                VALUES ('$_POST[estado_item]','Se ha añadido el nuevo elemento', NOW(), $id)";
      $res = mysql_query($query) or die(mysql_error());
	  
	  
	
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
      echo "Elemento de configuración creada con éxito.<BR>";

      if (isset($_POST['origen'])){
         echo "<button type='button' onClick=\"location.href='".$_POST['origen']."'\">Volver</button>";
      } else {
         echo "<button type='button' onClick=\"location.href='gconfiguracion.php'  \">Volver a Gestión de configuración  </button>";
        // echo "<button type='button' onClick=\"location.href='gincidencias.php'\">Ir a Gestión de incidencias</button>";
      }
      echo "<BR>";

   } else {

     // Formulario

      echo "<H4>Introduzca los datos del nuevo elemento de configuración:</H4>";
      /*if (isset($_POST['problema'])){
         $query = "SELECT nombre from ".dbname.".problema where id=".$_POST['problema'];
         $res   = mysql_query($query) or die(mysql_error());
         $row   = mysql_fetch_array($res);
         echo "Nota: La peticion de cambio estará relacionada con el problema \"".$row['nombre']."\" <BR>";
      }*/

       echo "<form method='post' action='$PHP_SELF' name='formnuevoitem' onsubmit='return validaFormulario();'>";
      echo "<table border='0' cellspacing='0' summary='Formulario nueva elemento de configuración'>";
      //echo "   <tr><td>Nombre(*) </td><td> <input type='text' name='nombre' size='30' maxlength='30' value=''> </td></tr>";
      echo "   <tr><td>Descripción (*)</td><td> <textarea rows='5' name='descripcion' cols='28'></textarea> </td></tr>";
      
	  echo "   <tr><td>Tipo </td><td> <select name='tipo_item'>";
      $query = "SELECT id, nombre from ".dbname.".tipo_item";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row=mysql_fetch_array($res)){
         echo "      <option value='".$row['id']."'>".$row['nombre']."</option>";
      }
    
	  echo "   </select> </td></tr>";
	  
	  
	  
	  
	  echo "   <tr><td>Estado</td><td> <p><select name='estado_item'>";
      $query = "SELECT id, nombre from ".dbname.".est_it";
      $res   = mysql_query($query) or die(mysql_error());
      while ($row=mysql_fetch_array($res)){
            echo "      <option value='$row[nombre]'>$row[nombre]</option></p>";
      }
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
      echo "   <tr><td>Padre </td><td> <select name='padre'>";
      $query = "SELECT id, descripcion from ".dbname.".item_id";
      $res   = mysql_query($query) or die(mysql_error());
	  //Opcion sin padre	  
		 echo "      <option value=0>Sin padre</option>";
		 
      while ($row=mysql_fetch_array($res)){
         echo "      <option value='".$row['id']."'>".$row['descripcion']."</option>";
      } 
	  
	  
	    echo "   </select> </td></tr>";
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