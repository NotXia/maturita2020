<?php
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

      <title></title>
   </head>

   <body class="text-center">

      <nav class="navbar navbar-expand-lg navbar-light bg-light">
         <a class="navbar-brand" href="index.php">TechnoLab</a>
      </nav>

   </body>

</html>
