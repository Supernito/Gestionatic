<?php
//TODO control de errores

   include 'wrappers.php';

   cabecera("Logout");

   session_destroy();
   echo "Has salido! <BR> <a href='./'>Volver al inicio</a>";

   pie();
?>
