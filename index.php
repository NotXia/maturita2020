<?php
   session_start();

   require_once(dirname(__FILE__)."/utilities/login_check.php");
   if(!logged()) {
      header("Location: login.php");
      exit;
   }

   if(empty($_SESSION["curr_reparto"])) {
      header("Location: select.php");
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
      <script src="./js/jquery.min.js"></script>
      <script src="./js/popper.min.js"></script>
      <script src="./js/bootstrap.min.js"></script>

      <title>Dashboard</title>
   </head>

   <body>

      <nav class="navbar navbar-expand-sm navbar-light bg-light">
         <div class="navbar-brand">
            <a class="navbar-brand" href="index.php">
               <img class="navbar-brand admin_nav_logo" src="img/hospital.png">
               TechnoLab
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
               <?php
                  if(count($_SESSION["reparti"]) > 1) {
                     ?>
                     <li class="nav-item">
                        <a class="nav-link" href="select.php">Cambia reparto</a>
                     </li>
                     <?php
                  }
               ?>
               <li class="nav-item">
                  <a class="nav-link" href="logout.php">Logout</a>
               </li>
            </ul>
         </div>
      </nav>

      <div style="padding-right:20px;padding-top:20px;" class="text-right">

      </div>

      <div class="container-fluid">
         <div class="row text-black">
            <div class="col">

            </div>
            <div class="col text-center">
               <h1 class="display-4 py-2">Dashboard</h1>
            </div>
            <div class="col text-right">
               <h5 style="text-transform: uppercase;"><?php echo $_SESSION["cognome"] . " " . $_SESSION["nome"] ?></h5>
               <h6><?php echo $_SESSION["curr_reparto_name"] ?></h6>
            </div>
         </div>

      </div>

   </body>


</html>
