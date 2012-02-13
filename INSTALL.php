<?php
   $cabecera = "<!DOCTYPE html PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
<html lang='es'>
   <head>
      <title>GESTIONATIC - ..:: - INSTALADOR - ::.. -</title>
      <meta http-equiv='Content-Type' content='text/html; charset= iso-8859-1'>
      <meta name='description'        content='Web para la gestión de servicios basada en itil'>
      <meta name='robots'             content='NOINDEX'>
      <link rel ='stylesheet' type='text/css' href='./css/estilo.css'>
   </head>
   <body>
      <div id='cabecera'>
         <div id='titulo'>
            <h1><a class='link_titol' href='./'>GESTIONATIC</a></h1>
         </div>
      </div>
      <div id='contenido'>";

// CONTENIDO

   if (!isset($_POST[instalar])){
      echo $cabecera;
      echo "<H4>Instalador de GESTIONATIC</H4>";
      echo "Bienvenido al instalador de la aplicación GESTIONATIC. Para continuar, debe realizar los siguientes pasos:<BR><BR>";
      echo "1.- Cambie el nombre del archivo \"db.conf.sample\" situado en esta misma carpeta por el de \"db.conf\" y a continuación edite su contenido con un editor de texto plano.<BR>";
      echo "2.- Cambie el valor de la variable definida como dbhost de 'localhost' al servidor donde se encuentra la base de datos (si está en local deje el valor 'localhost').<BR>";
      echo "3.- Cambie el valor de la variable definida como dbuser de 'root' al valor del usuario que puede acceder a la base de datos.<BR>";
      echo "4.- Asigne a la variable dbpass la contraseña de la base de datos para el usuario indicado en el apartado anterior.<BR>";
      echo "5.- Asigne a la variable dbname el nombre de la base de datos en cuestión.<BR>";
      echo "6.- Al final el contenido de \"db.conf\" debe quedar algo similar a eso:<pre>
   &lt;?php
      define (dbhost,'XXX');
      define (dbuser,'XXX');
      define (dbpass,'XXX');
      define (dbname,'XXX');
   ?&gt;</pre><BR>";
      echo "Una vez echo todo esto, puedes seguir con la instalación con este botón: ";
      echo "<form method='post' action='INSTALL.php'>";
      echo "<input type='submit' value='Seguir' name='instalar'><BR>";
   } else {
      $error = 0;
      include 'db.conf';
      mysql_connect(dbhost,dbuser,dbpass);
      mysql_select_db(dbname);
      session_start();

      echo $cabecera;

      // Todas las tablas (26) y datos (29) de la base de datos
      $query[0] = "CREATE TABLE `cambio_item` ( `peticion_cambio` smallint(10) unsigned NOT NULL,
 `item` smallint(10) unsigned NOT NULL,  PRIMARY KEY (`peticion_cambio`,`item`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
      $query[1] = "CREATE TABLE `diagnostico` (  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8 NOT NULL,  `descripcion` varchar(5000) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[2] = "CREATE TABLE `diag_sol` (  `id_diag` smallint(10) unsigned NOT NULL,
  `id_sol` smallint(10) unsigned NOT NULL,  PRIMARY KEY (`id_diag`,`id_sol`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
      $query[3] = "CREATE TABLE `estado_item` (  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8 NOT NULL,  `descripcion` varchar(5000) CHARACTER SET utf8 DEFAULT NULL,
  `fecha` datetime NOT NULL,  `item` smallint(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ";
      $query[4] = "CREATE TABLE `estado_peticion` (  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8 NOT NULL,  `descripcion` varchar(5000) CHARACTER SET utf8 DEFAULT NULL,
  `fecha` datetime NOT NULL,  `peticion_cambio` smallint(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[5] = "CREATE TABLE `estado_prob` (  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` enum('creado','modificado','eliminado') CHARACTER SET utf8 NOT NULL DEFAULT 'creado',
  `descripcion` varchar(300) CHARACTER SET utf8 DEFAULT NULL,  `username` varchar(30) CHARACTER SET utf8 NOT NULL,
  `fecha` datetime NOT NULL,  `problema` smallint(5) unsigned NOT NULL,  PRIMARY KEY (`id`),
  KEY `problema` (`problema`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[6] = "CREATE TABLE `estado_version` (  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8 NOT NULL,  `descripcion` varchar(5000) CHARACTER SET utf8 DEFAULT NULL,
  `fecha` datetime NOT NULL,  `version` smallint(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[7] = "CREATE TABLE `est_it` (  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8 NOT NULL,  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[8] = "INSERT INTO `est_it` (nombre) VALUES('Diseño')";
      $query[9] = "INSERT INTO `est_it` (nombre) VALUES('Desarrollo')";
      $query[10]= "INSERT INTO `est_it` (nombre) VALUES('Pruebas')";
      $query[11]= "INSERT INTO `est_it` (nombre) VALUES('Operativo')";
      $query[12]= "INSERT INTO `est_it` (nombre) VALUES('Fuera de servicio')";
      $query[13]= "CREATE TABLE `est_pet` (  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8 NOT NULL,  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[14]= "INSERT INTO `est_pet` (nombre) VALUES('pendiente')";
      $query[15]= "INSERT INTO `est_pet` (nombre) VALUES('aprobado')";
      $query[16]= "INSERT INTO `est_pet` (nombre) VALUES('en proceso')";
      $query[17]= "INSERT INTO `est_pet` (nombre) VALUES('finalizado')";
      $query[18]= "INSERT INTO `est_pet` (nombre) VALUES('eliminado')";
      $query[19]= "CREATE TABLE `est_tar` (  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8 NOT NULL,  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[20]= "INSERT INTO `est_tar` (nombre) VALUES('pendiente')";
      $query[21]= "INSERT INTO `est_tar` (nombre) VALUES('finalizado')";
      $query[22]= "CREATE TABLE `impacto_prob` (  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) CHARACTER SET utf8 NOT NULL,  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[23]= "INSERT INTO `impacto_prob` (nombre) VALUES('Bajo')";
      $query[24]= "INSERT INTO `impacto_prob` (nombre) VALUES('Medio')";
      $query[25]= "INSERT INTO `impacto_prob` (nombre) VALUES('Alto')";
      $query[26]= "CREATE TABLE `incidencia` (  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8 NOT NULL,  `descripcion` varchar(512) CHARACTER SET utf8 DEFAULT NULL,
  `fecha` datetime NOT NULL,  `estado` enum('abierta','escalada','cerrada') CHARACTER SET utf8 NOT NULL DEFAULT 'abierta',
  `urgencia` enum('baja','media','alta','critica') CHARACTER SET utf8 NOT NULL,
  `nivelescalado` enum('service desk','administracion de redes','desarrolladores y analistas','proveedor') CHARACTER SET utf8 NOT NULL,
  `responsable` varchar(50) CHARACTER SET utf8 DEFAULT NULL,  `aplicacion` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `diagnostico` varchar(50) CHARACTER SET utf8 DEFAULT NULL,  `problema` smallint(6) DEFAULT NULL,
  `servicio` smallint(6) DEFAULT NULL,  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[27]= "CREATE TABLE `item_id` (  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(5000) CHARACTER SET utf8 NOT NULL,  `padre` smallint(10) unsigned NOT NULL,
  `tipo_item` smallint(10) unsigned NOT NULL,  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[28]= "CREATE TABLE `peticion_cambio` (  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8 NOT NULL,  `descripcion` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `tipo_cambio` smallint(10) unsigned NOT NULL,  `problema` smallint(10) unsigned DEFAULT NULL COMMENT 'clave extranjera a problema',
  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[29]= "CREATE TABLE `prioridad_prob` (  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) CHARACTER SET utf8 NOT NULL,  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[30]= "INSERT INTO `prioridad_prob` (nombre) VALUES('Baja')";
      $query[31]= "INSERT INTO `prioridad_prob` (nombre) VALUES('Media')";
      $query[32]= "INSERT INTO `prioridad_prob` (nombre) VALUES('Alta')";
      $query[33]= "CREATE TABLE `problema` (  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) CHARACTER SET utf8 NOT NULL,  `descripcion` varchar(300) CHARACTER SET utf8 DEFAULT NULL,
  `urgencia` smallint(6) NOT NULL,  `impacto` smallint(6) NOT NULL,  `prioridad` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[34]= "CREATE TABLE `reunion` (  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(5000) CHARACTER SET utf8 NOT NULL,  `fecha` date NOT NULL,  `peticion_cambio` smallint(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[35]= "CREATE TABLE `servicio` (  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(32) CHARACTER SET utf8 NOT NULL,  `descripcion` varchar(256) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[36]= "INSERT INTO `servicio` (nombre, descripcion) VALUES('Otros', 'Otros servicios sin especificar')";
      $query[37]= "CREATE TABLE `serv_item` (  `servicio` smallint(10) unsigned NOT NULL,
  `item` smallint(10) unsigned NOT NULL,  PRIMARY KEY (`servicio`,`item`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci";
      $query[38]= "CREATE TABLE `solucion` (  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE latin1_general_ci NOT NULL,  `descripcion` varchar(5000) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[39]= "CREATE TABLE `tarea` (  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(5000) CHARACTER SET utf8 NOT NULL,  `estado` varchar(50) CHARACTER SET utf8 NOT NULL,
  `fecha` date NOT NULL COMMENT 'Pendiente de tipo',  `peticion_cambio` smallint(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[40]= "CREATE TABLE `tipo_cambio` (  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8 NOT NULL,  `descripcion` varchar(5000) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[41]= "INSERT INTO `tipo_cambio` (nombre, descripcion) VALUES('Software', 'Cambio en el software')";
      $query[42]= "INSERT INTO `tipo_cambio` (nombre, descripcion) VALUES('Hardware', 'Cambio en el hardware')";
      $query[43]= "INSERT INTO `tipo_cambio` (nombre, descripcion) VALUES('Documentacion', 'Cambio en la documentación')";
      $query[44]= "CREATE TABLE `tipo_item` (  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8 NOT NULL,  `descripcion` varchar(5000) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[45]= "INSERT INTO `tipo_item` (nombre, descripcion) VALUES('Hardware', 'Elemento físico')";
      $query[46]= "INSERT INTO `tipo_item` (nombre, descripcion) VALUES('Software', 'Programa. Elemento lógico.')";
      $query[47]= "INSERT INTO `tipo_item` (nombre, descripcion) VALUES('Documentación', 'Manuales e informes')";
      $query[48]= "CREATE TABLE `urgencia_prob` (  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) CHARACTER SET utf8 NOT NULL,  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[49]= "INSERT INTO `urgencia_prob` (nombre) VALUES('No urgente')";
      $query[50]= "INSERT INTO `urgencia_prob` (nombre) VALUES('Urgente')";
      $query[51]= "INSERT INTO `urgencia_prob` (nombre) VALUES('Muy urgente')";
      $query[52]= "CREATE TABLE `usuario` (  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador',
  `username` varchar(30) CHARACTER SET utf8 NOT NULL COMMENT 'nombre de usuario',  `password` varchar(50) CHARACTER SET utf8 NOT NULL COMMENT 'contraseña',
  `email` varchar(50) CHARACTER SET utf8 NOT NULL COMMENT 'correo',  `is_admin` enum('true','false') CHARACTER SET utf8 NOT NULL DEFAULT 'false' COMMENT 'es admin?',
  `alertas` enum('true','false') CHARACTER SET utf8 NOT NULL DEFAULT 'false',  `g_usuarios` enum('true','false') CHARACTER SET utf8 NOT NULL DEFAULT 'false',
  `g_serv` enum('true','false') CHARACTER SET utf8 NOT NULL DEFAULT 'false',  `g_inc` enum('true','false') CHARACTER SET utf8 NOT NULL DEFAULT 'false',
  `elevar_inc` enum('true','false') CHARACTER SET utf8 NOT NULL DEFAULT 'false',  `g_prob` enum('true','false') CHARACTER SET utf8 NOT NULL DEFAULT 'false',
  `g_conf` enum('true','false') CHARACTER SET utf8 NOT NULL DEFAULT 'false',  `g_cambios` enum('true','false') CHARACTER SET utf8 NOT NULL DEFAULT 'false',
  `g_ver` enum('true','false') CHARACTER SET utf8 NOT NULL DEFAULT 'false',  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`,`email`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";
      $query[53]= "INSERT INTO `usuario` (username, password, email, is_admin) VALUES('ADMIN', md5('admin'), 'admin@aaa.aa', 'true')";
      $query[54]= "CREATE TABLE `version` (  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8 NOT NULL,  `descripcion` varchar(5000) CHARACTER SET utf8 DEFAULT NULL,
  `codigo` varchar(500) CHARACTER SET utf8 NOT NULL,  `item` smallint(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1";

      $i=0;
      echo "Creando tablas y poniendo datos básicos ($i/55).<BR>";
      foreach ($query as &$q){
         $i++;
         if (mysql_query($q)){
            echo "Sentencia ejecutada con éxito ($i/55).<BR>";
         } else {
            $error++;
            echo "ERROR!!! en la sentencia $i.<BR>";
         }
      }
      unset($q);

      if ($error == 0){
         echo "ENHORABUENA: GESTIONATIC Instalado correctamente!<BR>";
         echo "Le recomendamos que borre este fichero de instalación para evitar problemas así como cambiar la contraseña del usuario ADMIN
               (ahora la clave es \"admin\").";
      } else {
         echo "Se han producido $error errores. La instalación no se ha realizado correctamente.<BR>";
         echo "Por favor, borre todo lo generado y vuelva a intentarlo.<BR>";
      }

      mysql_close();
   }
?>
<!-- FIN DEL CONTENIDO -->
         <BR>
         <div id='pie'><center><BR>
         </center> </div> <!--PIE-->
      </div> <!--CONTENIDO-->
   </body>
</html>
