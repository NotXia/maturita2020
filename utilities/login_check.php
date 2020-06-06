<?php

   if(!isset($_SESSION)) {
      session_start();
   }

   function logged() : bool {
      return !empty($_SESSION["id"]);
   }

   function adminLogged() : bool {
      return !empty($_SESSION["id"]) && $_SESSION["admin"]==1;
   }

   function isFarmacista() : bool {
      return $_SESSION["reparto"] == 1;
   }

 ?>
