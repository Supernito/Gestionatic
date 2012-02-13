<?php
	include 'db.conf';
	include 'wrappers.php';
	define (LIM_CAR_DES,'30'); // Carácteres a mostrar en Descripción

  cabecera("Gestion de incidencias");
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
	$zona_horaria="+06:00";

	function dateadd($operacion, $date, $dd=0, $mm=0, $yy=0, $hh=0, $mn=0, $ss=0){ 
  if($operacion=="resta"){
      $date_r = getdate(strtotime($date));
      $resultado = date("d-m-Y H:i:s", mktime(($date_r["hours"]-$hh),($date_r["minutes"]-$mn),($date_r["seconds"]-$ss),($date_r["mon"]-$mm),($date_r["mday"]-$dd),($date_r["year"]-$yy)));
      return $resultado; }
  else{
      $date_r = getdate(strtotime($date));
      $resultado = date("d-m-Y H:i:s", mktime(($date_r["hours"]+$hh),($date_r["minutes"]+$mn),($date_r["seconds"]+$ss),($date_r["mon"]+$mm),($date_r["mday"]+$dd),($date_r["year"]+$yy)));
      return $resultado; }
  }


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

      // Hay un usuario logueado
	  $loggedu = $_SESSION['username'];
      echo "<p>Bienvenido ".$loggedu.", Quiz&aacute quieras ver los <a href='diagnosticos.php'>diagn&oacutesticos</a> o las <a href='soluciones.php'>soluciones</a></p><HR>";

      $query = "SELECT id,is_admin FROM $dbname.usuario WHERE username='$loggedu'";
      $res = mysql_query($query) or die(mysql_error());
      $row = mysql_fetch_array($res);
      $id_usuario = $row['id'];
      $isadmin = $row['is_admin'];
      echo "<form action='nuevaincidencia.php' method='POST'> 
	  <input type='submit' class='button' name='nuevaincidencia' value='Introducir nueva incidencia'/>
	  </form>";
      echo "<H4>Incidencias Existentes:</H4>";
		$id=$_GET['ver'];
		if($_GET['ver']){
      	$result=mysql_query("SELECT * FROM $dbname.incidencia WHERE id=$id") or die(mysql_error());
			$row = mysql_fetch_array($result);
			$fecha=$row['fecha'];
			$fecha=dateadd($suma,$fecha,0,0,0,6,0,0);
			$fecha=date($fecha);
      	echo "<H4>Detalles de la incidencia \"$row[nombre]\":</H4>";
     		echo "<table border='0' cellspacing='0'>";
      	
			echo "<tr><td><b>Nombre:</b></td><td> ".$row['nombre']."</td></tr>";
			echo "<tr><td><b>Descripci&oacuten: </b></td><td> ".$row['descripcion']."</td></tr>";
			echo "<tr><td><b>Fecha de inserci&oacuten:</b></td><td> ".$fecha."</td></tr>";
			echo "<tr><td><b>Estado:</b></td><td> ".$row['estado']."</td></tr>";
			echo "<tr><td><b>Urgencia:</b></td><td> ".$row['urgencia']."</td></tr>";
			echo "<tr><td><b>Nivel de escalado:</b></td><td> ".$row['nivelescalado']."</td></tr>";
			echo "<tr><td><b>Responsable:</b></td><td> ".$row['responsable']."</td></tr>";
			echo "<tr><td><b>Aplicaci&oacuten:</b></td><td> ".$row['aplicacion']."</td></tr>";
			echo "<tr><td><b>Diagn&oacutestico:</b></td><td> ".$row['diagnostico']."</td></tr>";
			$diagnostico=$row['diagnostico'];
			$problema=$row['problema'];
			$servicio=$row['servicio'];
			//imprimimos la solución al diagnóstico si lo hay
			$resultdiag=mysql_query("SELECT * FROM $dbname.diagnostico WHERE nombre='$diagnostico'") or die(mysql_error());
			if (mysql_numrows($resultdiag)!=0){//no se ha encontrado diagnóstico con ese nombre
				$rowdiag = mysql_fetch_array($resultdiag);
				$iddiag = $rowdiag['id'];
				echo "<tr><td><b>Descripci&oacuten del diagn&oacutestico:</b></td><td> ".$rowdiag['descripcion']."</td></tr>";
				//imprimimos las soluciones al diagnostico si la hay
				$resultsol=mysql_query("SELECT * FROM $dbname.diag_sol,$dbname.solucion WHERE diag_sol.id_diag='$iddiag' AND diag_sol.id_sol=solucion.id") or die(mysql_error());
				if (mysql_numrows($resultsol)==0) {
					echo "no se han encontrado soluciones para este diagn&oacutestico. <br>";
				}else{
					echo "<tr><td><b>Soluciones:</b></td><td>";
					while ($rowsol=mysql_fetch_array($resultsol)){
						echo "<tr><td><b>Nombre:</b></td><td> ".$rowsol['nombre']."</td></tr>";
						echo "<tr><td><b>Descripci&oacuten: </b></td><td> ".$rowsol['descripcion']."</td></tr>";
					}   	
				}
			}
			//imprimimos el problema asociado si lo hay
			if ($problema){
				$resultprob=mysql_query("SELECT nombre FROM $dbname.problema WHERE id='$problema'") or die(mysql_error());
				$resultprob=mysql_fetch_array($resultprob);
				$nombreprob=$resultprob['nombre'];
				echo "<tr><td><b>Problema asociado:</b></td><td> <a href='./gproblemas.php?ver=$problema'>$nombreprob</a> </td></tr>";
			}
			//imprimimos el servicio asociado si lo hay
			if ($servicio){
				$resserv=mysql_query("SELECT nombre FROM $dbname.servicio WHERE id='$servicio'") or die(mysql_error());
				$resserv=mysql_fetch_array($resserv);
				$nombreserv=$resserv['nombre'];
				echo "<tr><td><b>Servicio asociado:</b></td><td> <a href='./gservicios.php?ver=$servicio'>$nombreserv</a> </td></tr>";
			}
			echo "</table>";
	  
	  //opciones de edición, solo para administradores
      
		if ($isadmin=='true' || $own[g_inc]=='true'){
			$id=$row['id'];
			echo "<table border='0' cellspacing='0'> <tr>";
		

			echo "<td><form method='post' action='editincidencia.php'>
					<input type='hidden' name='idincidencia' value='$id'>
					<input type='submit' class='button' name='editincidencia' value='Editar incidencia'/>
					</form></td>";

			echo "<td><form method='post' action='elimincidencia.php?idincidencia=$id'>
					<input type='submit' class='button' name='elimincidencia' value='Eliminar incidencia'/>
					</form></td>";
			if (!$problema){//sólo si no hay problema asociado se puede elevar a problema
				echo "<td><form method='post' action='nuevoproblema.php'>
						<input type='hidden' name='origen'  value='gincidencias.php'>
						<input type='hidden' name='incidencia'  value='$id'>
						<input type='submit' class='button' name='nuevoproblema' value='Elevar a problema'/>
						</form></td>";
			}else{
				echo "<td><form method='post' action='desvincprob.php'>
						<input type='hidden' name='origen'  value='gincidencias.php'>
						<input type='hidden' name='incidencia'  value='$id'>
						<input type='submit' class='button' name='desvincular' value='Desvincular del problema'/>
						</form></td>";
			}
			echo "</tr> </table>";	
   	}
		echo "<td><button type='button' onClick=\"location.href='gincidencias.php'\">Ocultar</button>";
	}
	//imprimimos la tabla con todas las incidencias
	//impresión de la primera fila
	echo "<table border='1' cellspacing='0'>";
	echo "<tr> <td>
               <b><center>Id</center></b>
            </td> <td>
               <b><center>Nombre</center></b>
            </td> <td>
               <b><center>Descripci&oacuten</center></b>
            </td> <td>
               <b>fecha de inserci&oacuten</b>
            </td> <td>
               <b><center>Estado</center></b>
            </td> <td>
               <b><center>Urgencia</center></b>
            </td> <td>
               <b><center>Nivel de escalado</center></b>
            </td> <td>
               <b><center>Responsable</center></b>
            </td> <td>
               <b><center>Ver</center></b>
            </td> <td>
               <b><center>Borrar</center></b>
            </td> </tr>";	

	$result=mysql_query("SELECT * FROM $dbname.incidencia ORDER BY id ASC") or die(mysql_error());
   while ($row=mysql_fetch_array($result)){
		$id=$row['id'];
		$fecha=$row['fecha'];
		$fecha=dateadd($suma,$fecha,0,0,0,6,0,0);
		$fecha=date($fecha);
		//aquÃ­ se imprimen las incidencias
		echo "<tr onMouseOver='resaltaLinia(this)' onMouseOut='restauraLinia(this)'>";
		echo "<td> ".$id."</td>";
		echo "<td> ".$row['nombre']."</td>";
		$descrip = $row['descripcion'];
      if (strlen($descrip) > LIM_CAR_DES) $descrip = substr($descrip,0,LIM_CAR_DES - 3)."...";
		echo "<td>".$descrip."</td>";
		echo "<td>".$fecha."</td>";
		echo "<td>".$row['estado']."</td>";
		echo "<td>".$row['urgencia']."</td>";
		echo "<td>".$row['nivelescalado']."</td>";
		echo "<td>".$row['responsable']."</td>";
		echo "   <td><center><a href='gincidencias.php?ver=$row[id]'>
      			<img src='img/view.gif'   alt='Ver' title='Ver detalles'></a></center></td>";
		echo "   <td><center><a href='elimincidencia.php?idincidencia=$row[id]' onclick='return asegurar();'>
               <img src='img/delete.gif' alt='Borrar' title='Borrar'></a></center></td>";	
	}
	echo "</tr> </table>";
}

mysql_close();
pie();
?>
