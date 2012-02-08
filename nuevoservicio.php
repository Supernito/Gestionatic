<?php
   include 'db.conf';
   include 'wrappers.php';

   cabecera("Nuevo Servicio");

   mysql_connect(dbhost,dbuser,dbpass);
   mysql_select_db(dbname);
   session_start();

   // Si no está logueado o no tiene permiso terminamos
   if ($_SESSION['logged']!=true){
      echo "No tienes permisos para ver esto.<BR>";
      mysql_close();pie();die();
   }

   // Miraramos los permisos para gestionar servicios. Los guardamos en $own
   $query = "SELECT is_admin,g_serv FROM ".dbname.".usuario WHERE username='$_SESSION[username]'";
   $res   = mysql_query($query) or die(mysql_error());
   $own   = mysql_fetch_array($res);

   // Si no tiene permisos morimos
   if ($own[is_admin] == 'false' && $own[g_serv] == 'false'){
      echo "No tienes permisos suficientes.<BR>";
      mysql_close;pie();die();
   }

   // Script de comprovación
?>

   <script type="text/javascript">
      function validaFormulario(){
         var x = document.forms.formnuevoservicio.nombre.value;
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

      // Comprovación que el nombre no esté ya en la BBDD
      $query = "SELECT count(*) num FROM ".dbname.".servicio
                  WHERE nombre='$nombre'";
      $res   = mysql_query($query) or die(mysql_error());
      $row   = mysql_fetch_array($res);
      $num   = $row[num];

      if ($num!='0'){
         echo "Ya existe un servicio activo con este nombre.<BR>";
         if (isset($_POST['origen'])){
            echo "<button type='button' onClick=\"location.href='".$_POST['origen']."'\">Volver</button>";
         } else {
            echo "<button type='button' onClick=\"location.href='gservicios.php'  \">Ir a Gestión de Servicios  </button>";
            echo "<button type='button' onClick=\"location.href='gincidencias.php'\">Ir a Gestión de Incidencias</button>";
         }
         echo "<BR>";mysql_close();pie();die();
      }

      $query = "INSERT INTO ".dbname.".servicio
                   (nombre, descripcion)
                VALUES ('$nombre', '$descripcion')";
      $res = mysql_query($query) or die(mysql_error());
      echo "Servicio insertado con éxito.<BR>";

      if (isset($_POST['origen'])){
         echo "<button type='button' onClick=\"location.href='".$_POST['origen']."'\">Volver</button>";
      } else {
         echo "<button type='button' onClick=\"location.href='giservicios.php'  \">Ir a Gestión de Servicios  </button>";
         echo "<button type='button' onClick=\"location.href='gincidencias.php'\">Ir a Gestión de Incidencias</button>";
      }
      echo "<BR>";

   } else {

      // Formulario
      echo "<H4>Introduzca los datos de un nuevo servicio:</H4>";

      echo "<form method='post' action='$PHP_SELF' name='formnuevoservicio' onsubmit='return validaFormulario();'>";
      echo "<table border='0' cellspacing='0' summary='Formulario nuevo servicio'>";
      echo "   <tr><td>Nombre(*) </td><td> <input type='text' name='nombre' size='30' maxlength='30' value=''> </td></tr>";
      echo "   <tr><td>Descripción </td><td> <textarea rows='5' name='descripcion' cols='28'></textarea> </td></tr>";
      echo "</table>";
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
