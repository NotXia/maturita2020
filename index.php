<?php
   session_start();

   require_once(dirname(__FILE__)."/utilities/login_check.php");
   if(!logged()) {
      header("Location: login.php");
      exit;
   }
?>

<!DOCTYPE html>
<html>

   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link rel="stylesheet" href="./css/bootstrap.min.css">
      <link rel="stylesheet" href="./css/navbar.css">

      <title></title>
   </head>

   <body class="text-center">

      <nav class="navbar navbar-expand-lg navbar-light bg-light">
         <div class="navbar-brand">
            <a class="navbar-brand" href="index.php">
               <img class="navbar-brand user_nav_logo" src="./img/hospital.png">
               TechnoLab
            </a>
         </div>
      </nav>

      <?php

      foreach($_SESSION["reparti"] as $id_rep) {
         echo $id_rep . "<br>";
      }

      ?>

   </body>

</html>
