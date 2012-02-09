<?php
   include 'db.conf';
   include 'wrappers.php';

   cabecera("..::PAGINA PRINCIPAL::..");

   mysql_connect(dbhost,dbuser,dbpass); 
   mysql_select_db(dbname);
   session_start();

   if ($_SESSION['logged']!=true){
      // No hay nadie logueado
      echo "<p>Hay que estar logueado para poder acceder a las funcionalidades</p>";
   } else {
      // Hay un usuario logueado
      echo "<p>Bienvenido ".$_SESSION[username].", por favor, escoge una de las siguientes opciones disponibles</p><HR>";
//      echo "<p>Las siguientes son obligatorias</p>";
      $opciones = 0;
      $query = "SELECT * FROM ".dbname.".usuario WHERE username='$_SESSION[username]'";
      $res = mysql_query($query) or die(mysql_error());
      $row = mysql_fetch_array($res);
      if ($row[is_admin] == 'true' || $row[g_serv] == 'true'){
         echo "<a href='./gservicios.php'>Gestión de Servicios</a><BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true' || $row[g_inc] == 'true'){
         echo "<a href='./gincidencias.php'>Gestión de Incidencias</a><BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true' || $row[g_prob] == 'true'){
         echo "<a href='./gproblemas.php'>Gestión de Problemas</a><BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true' || $row[g_conf] == 'true'){
         echo "<a href='./'>Gestión de Configuraciones</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true' || $row[g_cambios] == 'true'){
         echo "<a href='./gcambios.php'>Gestión de Cambios</a><BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true' || $row[g_ver] == 'true'){
         echo "<a href='./'>Gestión de Versiones</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true' || $row[g_usuarios] == 'true'){
         echo "<a href='./gusuarios.php'>Gestión de Usuarios</a><BR>"; $opciones++;
      }
/*
      if ($row[is_admin] == 'true'){
         echo "<a href='./'>Gestión de de niveles de servicio</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true'){
         echo "<a href='./'>Gestión financiera</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true'){
         echo "<a href='./'>Gestión de la capacidad</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true'){
         echo "<a href='./'>Gestión de la continuidad del servicio</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true'){
         echo "<a href='./'>Gestión de la disponibilidad</a>(pendiente)<BR>"; $opciones++;
      }
      if ($row[is_admin] == 'true'){
         echo "<a href='./'>Gestión de la seguridad</a>(pendiente)<BR>"; $opciones++;
      }
*/
      if ($row[is_admin] == 'true' || $row[alertas] == 'true'){
         echo "<HR>";
         echo "<H4>Alertas:</H4>";
         $opciones++;
         $alertas = 0;
         $query = "SELECT count(*) tot,
                      (SELECT count(*) FROM ".dbname.".incidencia
                      WHERE urgencia = 'Critica' and estado != 'Cerrada') crit
                   FROM ".dbname.".incidencia where estado != 'Cerrada'";
         $res = mysql_query($query) or die(mysql_error());
         $lin = mysql_fetch_array($res);
         if ($lin[tot] != 0){
            $alertas++;
            echo "<p><a href='gincidencias.php'><img src='img/alert.gif'   alt='ALERTA' title='Gestionar Incidencia'></a>";
            echo "Se han encontrado $lin[tot] incidencias abiertas, de las cuales $lin[crit] son críticas.";
            echo "<a href='gincidencias.php'><img src='img/alert.gif'   alt='ALERTA' title='Gestionar Incidencia'></a>";
            echo "<button type='button' onClick=\"location.href='gincidencias.php'\">Arreglar</button></p>";
         }

         $query = "SELECT count(*) tot,
                      (SELECT count(*)
                       FROM ".dbname.".problema prob
                       LEFT JOIN ".dbname.".estado_prob ep ON (ep.problema = prob.id)
                       WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_prob
                                    WHERE problema = prob.id group by problema)
                          AND ep.nombre != 'eliminado'
                          AND prob.urgencia >= 3) urgencia,
                      (SELECT count(*)
                       FROM ".dbname.".problema prob
                       LEFT JOIN ".dbname.".estado_prob ep ON (ep.problema = prob.id)
                       WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_prob
                                    WHERE problema = prob.id group by problema)
                          AND ep.nombre != 'eliminado'
                          AND prob.impacto >= 3) impacto,
                      (SELECT count(*)
                       FROM ".dbname.".problema prob
                       LEFT JOIN ".dbname.".estado_prob ep ON (ep.problema = prob.id)
                       WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_prob
                                    WHERE problema = prob.id group by problema)
                          AND ep.nombre != 'eliminado'
                          AND prob.prioridad >= 3) prioridad
                   FROM ".dbname.".problema prob
                   LEFT JOIN ".dbname.".estado_prob   ep ON (ep.problema = prob.id)
                   WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_prob
                                WHERE problema = prob.id group by problema)
                      AND ep.nombre != 'eliminado'";
         $res = mysql_query($query) or die(mysql_error());
         $lin = mysql_fetch_array($res);
         if ($lin[tot] != 0){
            $alertas++;
            echo "<p><a href='gproblemas.php'><img src='img/alert.gif' alt='ALERTA' title='Gestionar Problemas'></a>";
            echo "Se han encontrado $lin[tot] problemas abiertos, de las cuales
                  $lin[urgencia] son urgentes,
                  $lin[impacto] tienen un gran impacto y
                  $lin[prioridad] tienen una alta prioridad.";
            echo "<a href='gproblemas.php'><img src='img/alert.gif' alt='ALERTA' title='Gestionar Problemas'></a>";
            echo "<button type='button' onClick=\"location.href='gproblemas.php'\">Arreglar</button></p>";
         }

         $query = "SELECT count(*) tot
                   FROM ".dbname.".peticion_cambio pc
                   LEFT JOIN ".dbname.".estado_peticion ep ON (pc.id = ep.peticion_cambio)
                   WHERE ep.id=(SELECT max(id) FROM ".dbname.".estado_peticion
                                WHERE peticion_cambio = pc.id group by peticion_cambio)
                      AND ep.nombre != 'eliminado'";
         $res = mysql_query($query) or die(mysql_error());
         $lin = mysql_fetch_array($res);
         if ($lin[tot] != 0){
            $alertas++;
            echo "<p><a href='gcambios.php'><img src='img/alert.gif' alt='ALERTA' title='Gestionar Cambios'></a>";
            echo "Se han detectado $lin[tot] cambios pendientes.";
            echo "<a href='gcambios.php'><img src='img/alert.gif' alt='ALERTA' title='Gestionar Cambios'></a>";
            echo "<button type='button' onClick=\"location.href='gcambios.php'\">Arreglar</button></p>";
         }

         if ($alertas == 0){
            echo "<p>No hay ninguna alerta de la que preocuparse.</p>";
         }

         echo "<HR><H4>Grafica:</H4><center>Incidencias los últimos 7 dias</center>";

         // Para configurar mejor, visitar:
         // http://code.google.com/intl/es-ES/apis/chart/interactive/docs/gallery/linechart.html#Configuration_Options

         $query = "SELECT count(*) DIA1, curdate() DIAa,
                     (SELECT count(*) FROM ".dbname.".incidencia WHERE date(fecha)=curdate()-1) DIA2, date(curdate()-1) DIAb,
                     (SELECT count(*) FROM ".dbname.".incidencia WHERE date(fecha)=curdate()-2) DIA3, date(curdate()-2) DIAc,
                     (SELECT count(*) FROM ".dbname.".incidencia WHERE date(fecha)=curdate()-3) DIA4, date(curdate()-3) DIAd,
                     (SELECT count(*) FROM ".dbname.".incidencia WHERE date(fecha)=curdate()-4) DIA5, date(curdate()-4) DIAe,
                     (SELECT count(*) FROM ".dbname.".incidencia WHERE date(fecha)=curdate()-5) DIA6, date(curdate()-5) DIAf,
                     (SELECT count(*) FROM ".dbname.".incidencia WHERE date(fecha)=curdate()-6) DIA7, date(curdate()-6) DIAg
                   FROM ".dbname.".incidencia where date(fecha)=curdate()";
         $res = mysql_query($query) or die(mysql_error());
         $lin = mysql_fetch_array($res);
         echo "    <script type='text/javascript' src='https://www.google.com/jsapi'></script>
    <script type='text/javascript'>
      google.load('visualization', '1', {packages:['corechart']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Dia');
        data.addColumn('number', 'Incidencias');
        data.addRows([
          ['$lin[DIAa]', $lin[DIA1]],
          ['$lin[DIAb]', $lin[DIA2]],
          ['$lin[DIAc]', $lin[DIA3]],
          ['$lin[DIAd]', $lin[DIA4]],
          ['$lin[DIAe]', $lin[DIA5]],
          ['$lin[DIAf]', $lin[DIA6]],
          ['$lin[DIAg]', $lin[DIA7]]
        ]);

        var options = {
          backgroundColor: '#FDD096',
          width: 800, height: 400,
          legend:{position: 'none'},
          vAxis: {title: 'Cantidad de incidencias', format: '#', gridlines: {count: 6, color:'black'}},
          hAxis: {title: 'Dia'}
        };

        var chart = new google.visualization.LineChart(document.getElementById('grafica_div'));
        chart.draw(data, options);
      }
    </script>
    <div align='center' id='grafica_div'></div>";

      }
      if ($opciones == 0){
         echo "<p>No tienes ninguna opción disponible, contacta con un administrador.</p>";
      }
   }

   mysql_close();
   pie();
?>
