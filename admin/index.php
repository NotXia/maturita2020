<?php
   require_once(dirname(__FILE__)."/../utilities/login_check.php");
   if(!logged()) {
      header("Location: login.php");
      exit;
   }
   else if(!adminLogged()) {
      header("Location: ../index.php");
      exit;
   }
?>

<!DOCTYPE html>
<html>

   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link rel="stylesheet" href="../css/bootstrap.min.css">
      <link rel="stylesheet" href="../css/styles.css">
      <link rel="stylesheet" href="../css/navbar.css">
      <script src="../js/jquery.min.js"></script>
      <script src="../js/popper.min.js"></script>
      <script src="../js/bootstrap.min.js"></script>

      <title>Admin</title>
   </head>

   <body class="text-center">

      <nav class="navbar navbar-expand-sm navbar-light bg-light">
         <div class="navbar-brand">
            <a class="navbar-brand" href="index.php">
               <img class="navbar-brand admin_nav_logo" src="../img/wrench.png">
               Admin
            </a>
         </div>
         <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
         </button>
         <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
               <li class="nav-item active">
                  <a class="nav-link">Home</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="./add">Inserimento</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="./view">Visualizzazione</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="logout.php">Logout</a>
               </li>
            </ul>
         </div>
      </nav>

      <div class="container">
         <div class="row text-black">
            <div class="col-xl-5 col-lg-6 col-md-8 col-sm-12 mx-auto text-center p-4">
               <h1 class="display-4 py-2">Admin</h1>

               <div id="insert" class="option" style="padding: 25px;">
                  <span>Inserisci</span>
               </div>
               <div id="view" class="option" style="padding: 25px;">
                  <span>Visualizza</span>
               </div>

            </div>
         </div>
      </div>

   </body>

   <script type="text/javascript">
      document.getElementById("insert").addEventListener("click", function() {
         window.location.href = "./add";
      });
      document.getElementById("view").addEventListener("click", function() {
         window.location.href = "./view";
      });
   </script>

</html>
