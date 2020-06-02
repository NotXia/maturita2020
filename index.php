<?php
   session_start();

   require_once(dirname(__FILE__)."/utilities/login_check.php");
   if(!logged()) {
      header("Location: login.php");
      exit;
   }

   if(adminLogged()) {
      header("Location: ./admin");
      exit;
   }

   if($_SESSION["reparto"] == 1) {
      header("Location: ./farmacia");
      exit;
   }
   else {
      header("Location: ./medico");
      exit;
   }
?>
