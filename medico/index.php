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

   require_once(dirname(__FILE__)."/../utilities/database.php");
?>

<!DOCTYPE html>
<html>

   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link rel="stylesheet" href="../css/bootstrap.min.css">
      <link rel="stylesheet" href="../css/navbar.css">
      <link rel="stylesheet" href="../css/styles.css">
      <script src="../js/jquery.min.js"></script>
      <script src="../js/popper.min.js"></script>
      <script src="../js/bootstrap.min.js"></script>

      <title>Dashboard</title>

      <style media="screen">
         .task {
            width: 100%;
            padding: 10px;
         }
      </style>

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
                  <a class="nav-link" href="index.php">Home</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="visit/">Visite</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="patient/">Pazienti</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="../logout.php">Logout</a>
               </li>
            </ul>
         </div>
      </nav>


      <div class="container">

         <div class="row p-5" style="padding-bottom:0px !important;">
            <div class="col-xl-6 col-lg-7 col-md-8 col-sm-10 mx-auto p-3 text-center border border-secondary rounded">
               <h5>La tua giornata</h5>
               <hr>
               <h6 style='margin:0'>Pazienti da visitare</h6>
               <form action="visit/add.php" method="POST">
                  <?php
                     try {
                        $conn = connect();
                        $sql = "SELECT ricoveri.id AS id_ricovero, pazienti.nome AS nome_paziente, pazienti.cognome, pazienti.cf, posti.nome AS nome_posto
                                FROM ricoveri, pazienti, posti
                                WHERE cod_paziente = pazienti.cf AND
                                      cod_posto = posti.id AND
                                      cod_medico = :id_medico AND
                                      data_fine IS NULL AND
                                      cod_paziente NOT IN (SELECT cod_paziente
                                                           FROM ricoveri, visite
                                                           WHERE cod_ricovero = ricoveri.id AND
                                                                 DATE(orario) = DATE(NOW()))
                                                           ORDER BY pazienti.cognome, pazienti.nome";
                           $stmt = $conn->prepare($sql);
                           $stmt->bindParam(":id_medico", $_SESSION["id"], PDO::PARAM_INT);
                           $stmt->execute();
                           $res = $stmt->fetchAll();

                           $zero_visite = true;
                           foreach($res as $row) {
                              $zero_visite = false;
                              $id = $row["id_ricovero"];
                              $cf = $row["cf"];
                              $nome = $row["nome_paziente"];
                              $cognome = $row["cognome"];
                              $posto = $row["nome_posto"];
                              echo "<button type='submit' style='margin: 5px;width:100%' class='btn btn-outline-secondary' value='$id' name='id_ricovero'>
                                       <span class='float-left'>$cognome $nome</span> <span class='float-right'>$posto</span>
                                    </button><br>";
                           }
                           if($zero_visite) {
                              echo "<p style='margin:0'>Non ci sono pazienti da visitare</p>";
                           }
                           $conn = null;
                        } catch (PDOException $e) {
                           $conn = null;
                           die("<br><span class='error'>Non è stato possibile estrare le visite di oggi</span>");
                        }
                     ?>
               </form>
            </div>
         </div>

         <div class="row p-4">
            <div class="col-xl-4 col-lg-5 col-md-8 col-sm-10 mx-auto text-center">
               <a class="btn btn-secondary task" href="./patient/add.php">Inserisci ricovero</a><br><br>
               <a class="btn btn-secondary task" href="./add/patient.php">Inserisci visita</a>
            </div>
         </div>
      </div>

   </body>


</html>
