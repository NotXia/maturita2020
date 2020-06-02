<?php
   session_start();

   require_once(dirname(__FILE__)."/../utilities/login_check.php");
   if(!logged()) {
      header("Location: ../login.php");
      exit;
   }

   if(adminLogged()) {
      header("Location: ../admin");
      exit;
   }
?>

<!DOCTYPE html>
<html>

   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link rel="stylesheet" href="../css/bootstrap.min.css">
      <link rel="stylesheet" href="../css/navbar.css">
      <script src="../js/jquery.min.js"></script>
      <script src="../js/popper.min.js"></script>
      <script src="../js/bootstrap.min.js"></script>

      <title>Dashboard</title>
   </head>

   <body>

      <nav class="navbar navbar-expand-sm navbar-light bg-light">
         <div class="navbar-brand">
            <a class="navbar-brand" href="index.php">
               <table>
                  <tr>
                     <td class="align-middle">
                        <img class="navbar-brand user_nav_logo" src="../img/hospital.png">
                     </td>
                     <td>
                        <h5 style="text-transform: uppercase;margin:0;"><?php if(!empty($_SESSION["reparto_nome"])) echo $_SESSION["reparto_nome"]; ?></h5>
                        <h6 style="margin:0;"><?php if(!empty($_SESSION["cognome"])) echo $_SESSION["cognome"]; ?> <?php if(!empty($_SESSION["nome"])) echo $_SESSION["nome"]; ?></h6>
                     </td>
                  </tr>
               </table>
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
                  <a class="nav-link" href="../logout.php">Logout</a>
               </li>
            </ul>
         </div>
      </nav>


      <div class="container">
         <div class="row text-black">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 mx-auto text-center p-4">
               <h1 class="display-4 py-2">Dashboard</h1>
            </div>

         </div>
      </div>

   </body>


</html>
