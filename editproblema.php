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
                u.nombre urgencia, i.nombre impacto, p.nombre prioridad,
                inc.nombre incidencia
            FROM ".dbname.".problema prob
            LEFT JOIN ".dbname.".urgencia_prob  u on (u.id   = prob.urgencia)
            LEFT JOIN ".dbname.".impacto_prob   i on (i.id   = prob.impacto)
            LEFT JOIN ".dbname.".prioridad_prob p on (p.id   = prob.prioridad)
            LEFT JOIN ".dbname.".incidencia   inc on (inc.id = prob.incidencia)
            WHERE prob.id = $_GET[id]";
   $res = mysql_query($query) or die(mysql_error());
   $old = mysql_fetch_array($res);

   if (isset($_POST[enviar])){
      // Realizamos las actualizaciones

      // Nombre
      if ($old[nombre] != $_POST[nombre]){
         $query = "SELECT count(*) num from ".dbname.".problema WHERE nombre='$_POST[nombre]'";
         $res   = mysql_query($query) or die(mysql_error());
         $row   = mysql_fetch_array($res);
         $num   = $row['num'];

         if ($num=='0'){
            $query = "UPDATE ".dbname.".problema SET nombre = '$_POST[nombre]' WHERE id = $_POST[id_prob]";
            $res = mysql_query($query) or die(mysql_error());
            $query = "INSERT INTO ".dbname.".estado_prob
                         (nombre, descripcion, username, fecha, problema)
                      VALUES ('modificado','Se ha cambiado el nombre de \"$old[nombre]\" a \"$_POST[nombre]\"',
                              '$_SESSION[username]', NOW(), $_POST[id_prob])";
            $res = mysql_query($query) or die(mysql_error());
            echo "Problema modificado con éxito.<BR>";
         } else {
            echo "Ya existe un problema con este nombre.<BR>";
            echo "<button type='button' onClick=\"location.href='gproblemas.php'\">Volver</button>";
            mysql_close();
            pie(); die();
         }
      }

      // Descripción
      // Urgencia
      // Impacto
      // Prioridad
      // Incidencia ?

// Nota: Nombre no fa falta que sigui únic, perque si se borra sen ha de poder crear un altre amb so mateix nom
//      o siqui que sa comprovació sería que no existesqui cap amb so mateix nom que no estigui borrat.

      echo "<button type='button' onClick=\"location.href='gproblemas.php'\">Volver</button>";

   } else {

      // Formulario
      echo "<form method='post' action='$PHP_SELF' name='formeditproblema' onsubmit='return validaFormulario();'>";
      echo "   <input type='hidden' name='id_prob'  value='$_GET[id]'>";
      echo "<H4>Editar el problema \"$old[nombre]\":</H4>";
      echo "<table border='0' cellspacing='0' summary='Editar el problema'>";
      echo "   <tr><td>Nombre(*) </td>
               <td> <input type='text' name='nombre' size='30' maxlength='30' value='$old[nombre]'> </td></tr>";
      echo "</table><input type='submit' value='Enviar' name='enviar'>";

      echo "<button type='button' onClick=\"location.href='gproblemas.php'\">Cancelar</button>";
      echo "</form>";
   }

   mysql_close();
   pie();
?>
