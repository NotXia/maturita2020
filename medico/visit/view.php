<?php
   session_start();

   require_once(dirname(__FILE__)."/../../utilities/login_check.php");
   if(!logged()) {
      header("Location: ../../login.php");
      exit;
   }

   if(adminLogged()) {
      header("Location: ../../admin");
      exit;
   }

   // Verifica che non sia un farmacista
   if(isFarmacista()) {
      header("Location: ../../farmacia");
      exit;
   }

   if(empty($_GET["id"])) {
      header("Location: ../index.php");
      exit;
   }

   require_once(dirname(__FILE__)."/../../utilities/database.php");
   require_once(dirname(__FILE__)."/../../utilities/anagrafica_paziente.php");

   try {
      $conn = connect();

      // Verifica se è un paziente del reparto
      $sql = "SELECT cod_reparto
              FROM visite, medici
              WHERE cod_medico = medici.id AND
                    visite.id = :id_visite";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(":id_visite", $_GET["id"], PDO::PARAM_INT);
      $stmt->execute();

      if($stmt->fetch()["cod_reparto"] != $_SESSION["reparto"]) {
         header("Location: ../index.php");
         exit;
      }
      $conn = null;
   } catch (PDOException $e) {
      $conn = null;
      header("Location: ../index.php");
      exit;
   }
?>

<!DOCTYPE html>
<html>

   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link rel="stylesheet" href="../../css/bootstrap.min.css">
      <link rel="stylesheet" href="../../css/navbar.css">
      <link rel="stylesheet" href="../../css/styles.css">
      <script src="../../js/jquery.min.js"></script>
      <script src="../../js/popper.min.js"></script>
      <script src="../../js/bootstrap.min.js"></script>

      <title>Visita</title>

      <style media="screen">
         .anagrafica {
            padding: 10px;
         }

         .prescrizione {
            margin: 5px;
         }
      </style>

   </head>

   <body>

      <nav class="navbar navbar-expand-sm navbar-light bg-light">
         <div class="navbar-brand">
            <a class="navbar-brand" href="../index.php">
               <table>
                  <tr>
                     <td class="align-middle">
                        <img class="navbar-brand user_nav_logo" src="../../img/hospital.png">
                     </td>
                     <td>
                        <h5 style="text-transform: uppercase;margin:0;"><?php if(!empty($_SESSION["reparto_nome"])) echo htmlentities($_SESSION["reparto_nome"]); ?></h5>
                        <h6 style="margin:0;"><?php if(!empty($_SESSION["cognome"])) echo htmlentities($_SESSION["cognome"]); ?> <?php if(!empty($_SESSION["nome"])) echo htmlentities($_SESSION["nome"]); ?></h6>
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
               <li class="nav-item">
                  <a class="nav-link" href="../">Home</a>
               </li>
               <li class="nav-item active">
                  <a class="nav-link" href="index.php">Visite</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="../patient/">Pazienti</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="../../logout.php">Logout</a>
               </li>
            </ul>
         </div>
      </nav>


      <div class="container">
         <div class="row text-black">
            <div class="col-xl-7 col-lg-8 col-md-10 col-sm-12 mx-auto text-center p-4">
               <h1 class="display-4 py-2">Visita</h1>

               <?php
                  anagrafica($_GET["id"], 1);
               ?>

            </div>
         </div>

         <div class="row text-black">
            <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 mx-auto text-center">
               <?php
                  try {
                     $conn = connect();
                     // Estrae i dati della visita
                     $sql = "SELECT orario, pressione, temperatura, saturazione, battito, note, nome, cognome
                             FROM visite, medici
                             WHERE cod_medico = medici.id AND
                                   visite.id = :id_visita";
                     $stmt = $conn->prepare($sql);
                     $stmt->bindParam(":id_visita", $_GET["id"], PDO::PARAM_INT);
                     $stmt->execute();
                     $res = $stmt->fetch();

                     if(!empty($res)) {
                        $orario = date("d/m/Y H:i", strtotime($res["orario"]));
                        $pressione = htmlentities($res["pressione"]);
                        $temperatura = htmlentities($res["temperatura"]);
                        $saturazione = htmlentities($res["saturazione"]);
                        $battito = htmlentities($res["battito"]);
                        $note = nl2br(htmlentities($res["note"]));
                        $nominaivo_medico = htmlentities($res["cognome"] . " " . $res["nome"]);

                        echo "<p><b>Orario</b> $orario</p>
                              <p><b>Medico</b> $nominaivo_medico</p><hr>";

                        if(!empty($pressione)) {
                           echo "<p><b>Pressione</b> $pressione mmHg</p>";
                        }
                        if(!empty($temperatura)) {
                           echo "<p><b>Temperatura</b> $temperatura"."°C</p>";
                        }
                        if(!empty($saturazione)) {
                           echo "<p><b>Saturazione</b> $saturazione%</p>";
                        }
                        if(!empty($battito)) {
                           echo "<p><b>Battito</b> $battito bpm</p>";
                        }
                        if(!empty($note)) {
                           echo "<p><b>Note</b><br>$note</p>";
                        }
                     }
                     else {
                        die("<br><span class='error'>Non è stato possibile trovare la visita</span>");
                     }

                     // Estrae le prescrizioni della visita
                     $sql = "SELECT posologia, prescrizioni.qta AS qta, qta_ritirata, denominazione
                             FROM prescrizioni, farmaci
                             WHERE cod_farmaco = farmaci.id AND
                                   cod_visita = :id_visita";
                     $stmt = $conn->prepare($sql);
                     $stmt->bindParam(":id_visita", $_GET["id"], PDO::PARAM_INT);
                     $stmt->execute();
                     $res = $stmt->fetchAll();

                     if(!empty($res)) {
                        ?>
                        <hr>
                        <h4>Prescrizioni</h4>
                        <div class="table table-responsive-lg">
                           <table class="table-bordered" align="center">
                              <tr>
                                 <th>Farmaco</th> <th>Posologia</th> <th>Quantità</th> <th>Quantità ritirata</th>
                              </tr>
                        <?php
                        foreach($res as $row) {
                           $posologia = nl2br(htmlentities($row["posologia"]));
                           $qta = htmlentities($row["qta"]);
                           $qta_ritirata = htmlentities($row["qta_ritirata"]);
                           $denominazione = htmlentities($row["denominazione"]);
                           echo "<tr>
                                    <td>$denominazione</td> <td style='text-align:left;'>$posologia</td> <td>$qta</td> <td>$qta_ritirata</td>
                                 </tr>";
                        }
                        ?>
                        </table>
                     </div>
                     <?php
                     }

                  } catch (PDOException $e) {
                     $conn = null;
                     die("<br><span class='error'>Qualcosa non ha funzionato</span>");
                  }
               ?>
            </div>
         </div>

      </div>

   </body>
</html>
