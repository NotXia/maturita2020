<?php

   require_once(dirname(__FILE__)."/config.php");

   function connect() : PDO {
      $host = DB_HOST;
      $db = DB_NAME;
      $user = DB_USER;
      $psw = DB_PSW;

      $conn = new PDO("mysql:host=$host;dbname=$db", $user, $psw);
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $conn;
   }

 ?>
