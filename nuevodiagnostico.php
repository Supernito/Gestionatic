<?php
   include 'db.conf';
   include 'wrappers.php';

   cabecera("Nuevo diagnostico");

if(isset($_POST['enviar']) && $_SESSION['logged']==true) {

   session_start(); 
   mysql_connect(dbhost,dbuser,dbpass); 
   mysql_select_db(dbname);

   $nombre=$_POST['nombre'];
   $descripcion = mysql_real_escape_string(trim(strip_tags(stripslashes($_POST['descripcion']))));
   $descripcion = str_replace('\\n', '<br>', $descripcion);


       $res = mysql_query("INSERT INTO  $dbname.diagnostico (nombre, descripcion)
               VALUES ('$nombre',  '$descripcion');") or die(mysql_error());
      mysql_close();
      echo "Diagn&oacutestico insertado con &eacute;xito<br>";
      echo "<a href='./gincidencias.php'>Volver al inicio</a>";

   } else {

      // Formulario
?>
   <form method="post" action="<?php echo $PHP_SELF; ?>" name="formnuevodiagnostico" >
      <p>Nombre <input type='text' name='nombre' size='30' value=''></p>
      <p>Descripci&oacute;n <textarea rows="5" name="descripcion" cols="28"></textarea></p>
      <p><input type='submit' value='enviar' name='enviar'>
		<button type=button onClick="location.href='diagnosticos.php'">Cancelar</button>
   </form>

<?php
	}
   pie();
?>
