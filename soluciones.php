<?php
   include 'db.conf';
   include 'wrappers.php';
	define (LIM_CAR_DES,'50'); // Carácteres a mostrar en Descripción

   cabecera("Soluciones");

?>
 
   <script type="text/javascript">
      var tmp;
      function resaltaLinia(row) {
         tmp = row.style.backgroundColor
         row.style.backgroundColor = "#a0a0a0";
      }
      function restauraLinia(row){
         row.style.backgroundColor = tmp;
      }
 
      function asegurar() {
         return confirm("Esta entrada será eliminada. ¿Está de acuerdo?");
      }
 
   </script>
 
<?php

   mysql_connect(dbhost,dbuser,dbpass); 
   mysql_select_db(dbname);
   session_start();

   if ($_SESSION['logged']!=true){
      // No hay nadie logueado
      echo "<p>Hay que estar logueado para poder acceder a las funcionalidades</p>";
   } else {

      // Miraramos los permisos para gestionar incidencias. Los guardamos en $own
      $query = "SELECT is_admin,g_inc FROM ".dbname.".usuario WHERE username='$_SESSION[username]'";
      $res   = mysql_query($query) or die(mysql_error());
      $own   = mysql_fetch_array($res);

      // Si no tiene permisos morimos
      if ($own[is_admin] == 'false' && $own[g_inc] == 'false'){
         echo "No tienes permisos suficientes.";
         mysql_close;
         pie();die();
      }

		echo "<p>Bienvenido $_SESSION[username], Quizá quieras ver los <a href='diagnosticos.php'>diagn&oacutesticos</a> o las <a href='gincidencias.php'>incidencias</a></p><HR>";
      // Hay un usuario logueado
	   $loggedu = $_SESSION['username'];
      $query = "SELECT id,is_admin FROM $dbname.usuario WHERE username='$loggedu'";
      $res = mysql_query($query) or die(mysql_error());
      $row = mysql_fetch_array($res);
      $id_usuario = $row['id'];
      $isadmin = $row['is_admin'];
		$result=mysql_query("SELECT * FROM $dbname.solucion ORDER BY id ASC") or die(mysql_error());
		echo "<form action='nuevasolucion.php' method='POST'> 
         <input type='submit' class='button' name='nuevasolucion' value='Introducir nueva solucion'/>
         </form>";
      echo "<H4>Soluciones Existentes:</H4>";

		$id=$_GET['ver'];
		if($_GET['ver']){
			$result=mysql_query("SELECT * FROM $dbname.solucion WHERE id=$id") or die(mysql_error());
			$row = mysql_fetch_array($result);
			echo "<H4>Detalles de la soluci&oacuten \"$row[nombre]\":</H4>";
			echo "<table border='0' cellspacing='0'>";
			echo "<tr><td><b>Nombre:</b></td><td> ".$row['nombre']."</td></tr>";
			echo "<tr><td><b>Descripci&oacuten: </b></td><td> ".$row['descripcion']."</td></tr>";
			echo "</table>";
			//opciones de edición, solo para administradores
      	if ($isadmin=='true'){
				echo "<button type=button onClick=\"location.href='elimsolucion.php?id=$id'\">Eliminar solución</button>";
				echo "<button type=button onClick=\"location.href='editsolucion.php?id=$id'\">Editar solución</button>";
      	}
			echo "<td><button type='button' onClick=\"location.href='soluciones.php'\">Ocultar</button>";
		}
		echo "<table border='1' cellspacing='0'>";
		echo "<tr> <td>
            <b><center>Id</center></b>
          </td> <td>
            <b><center>Nombre</center></b>
         </td> <td>
            <b><center>Descripci&oacuten</center></b>
         </td> <td>
            <b><center>Ver</center></b>
         </td> <td>
            <b><center>Borrar</center></b>
         </td> </tr>";  
		$result=mysql_query("SELECT * FROM $dbname.solucion ORDER BY id ASC") or die(mysql_error());
		while ($row=mysql_fetch_array($result)){
			$id=$row['id'];
			echo "<tr onMouseOver='resaltaLinia(this)' onMouseOut='restauraLinia(this)'>";
			echo "<td> ".$id."</td>";
			echo "<td> ".$row['nombre']."</td>";
			$descrip = $row['descripcion'];
     		if (strlen($descrip) > LIM_CAR_DES) $descrip = substr($descrip,0,LIM_CAR_DES - 3)."...";
			echo "<td>".$descrip."</td>";
			echo "   <td><center><a href='soluciones.php?ver=$row[id]'>
     			<img src='img/view.gif'   alt='Ver' title='Ver detalles'></a></center></td>";
   	 	echo "   <td><center><a href='elimsolucion.php?id=$row[id]' onclick='return asegurar();'>
            <img src='img/delete.gif' alt='Borrar' title='Borrar'></a></center></td>";	
		}
		echo "</tr> </table>";
    	  
	}

   mysql_close();
   pie();
?>
