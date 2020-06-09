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

   if(empty($_POST["id"])) {
      header("Location: index.php");
      exit;
   }

   require_once(dirname(__FILE__)."/../../utilities/database.php");
   require_once(dirname(__FILE__)."/../../utilities/anagrafica_paziente.php");
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

      <title>Dimetti</title>

      <style>
         .statistiche {
            padding: 5px;
         }
         .header {
            text-align: right;
         }
         .value {
            text-align: left;
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
               <li class="nav-item">
                  <a class="nav-link" href="../visit">Visite</a>
               </li>
               <li class="nav-item active">
                  <a class="nav-link" href="index.php">Pazienti</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="../../logout.php">Logout</a>
               </li>
            </ul>
         </div>
      </nav>


      <div class="container">
         <div class="row text-black">
            <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 mx-auto text-center p-4">
               <h1 class="display-4 py-2">Resoconto</h1>
               <?php
                  try {
                     $conn = connect();

                     $conn->beginTransaction();

                     $sql = "UPDATE ricoveri
                             SET data_fine = NOW()
                             WHERE id = :id";
                     $stmt = $conn->prepare($sql);
                     $stmt->bindParam(":id", $_POST["id"], PDO::PARAM_INT);
                     $stmt->execute();

                     $conn->commit();
                  } catch (PDOException $e) {
                     $conn->rollBack();
                     die("<p class='error'>Qualcosa non ha funzionato</p>");
                  }

                  anagrafica($_POST["id"], 0);

                  try {
                     $sql = "SELECT DATEDIFF(data_fine, data_inizio) AS durata, COUNT(visite.id) AS num_visite
                             FROM ricoveri, visite
                             WHERE cod_ricovero = ricoveri.id AND
                                   ricoveri.id = :id_ricovero";
                     $stmt = $conn->prepare($sql);
                     $stmt->bindParam(":id_ricovero", $_POST["id"], PDO::PARAM_INT);
                     $stmt->execute();
                     $res = $stmt->fetch();

                     $durata = htmlentities($res["durata"]);
                     $num_visite = htmlentities($res["num_visite"]);
                     echo "<br>
                           <table align='center'>
                              <tr>
                                 <th class='statistiche header'>Durata del ricovero</th>
                                 <td class='statistiche value'>$durata giorni</td>
                              </tr>
                              <tr>
                                 <th class='statistiche header'>Visite effettuate</th>
                                 <td class='statistiche value'>$num_visite</td>
                              </tr>
                           </table>";

                     $sql = "SELECT MAX(pressione) AS max_pressione, AVG(pressione) AS avg_pressione, MIN(pressione) AS min_pressione,
                                    MAX(temperatura) AS max_temperatura, AVG(temperatura) AS avg_temperatura, MIN(temperatura) AS min_temperatura,
                                    MAX(saturazione) AS max_saturazione, AVG(saturazione) AS avg_saturazione, MIN(saturazione) AS min_saturazione,
                                    MAX(battito) AS max_battito, AVG(battito) AS avg_battito, MIN(battito) AS min_battito
                             FROM visite
                             WHERE cod_ricovero = :id_ricovero";
                     $stmt = $conn->prepare($sql);
                     $stmt->bindParam(":id_ricovero", $_POST["id"], PDO::PARAM_INT);
                     $stmt->execute();
                     $res = $stmt->fetch();

                     $max_pressione = htmlentities(round($res["max_pressione"], 2));
                     $avg_pressione = htmlentities(round($res["avg_pressione"], 2));
                     $min_pressione = htmlentities(round($res["min_pressione"], 2));
                     $max_temperatura = htmlentities(round($res["max_temperatura"], 2));
                     $avg_temperatura = htmlentities(round($res["avg_temperatura"], 2));
                     $min_temperatura = htmlentities(round($res["min_temperatura"], 2));
                     $max_saturazione = htmlentities(round($res["max_saturazione"], 2));
                     $avg_saturazione = htmlentities(round($res["avg_saturazione"], 2));
                     $min_saturazione = htmlentities(round($res["min_saturazione"], 2));
                     $max_battito = htmlentities(round($res["max_battito"], 2));
                     $avg_battito = htmlentities(round($res["avg_battito"], 2));
                     $min_battito = htmlentities(round($res["min_battito"], 2));

                     echo "<table align='center' style='width:60%'>
                              <tr>
                                 <th style='padding-top: 10px' colspan='3'>Pressione</th>
                              </tr>
                              <tr>
                                 <td style='width: 33.33%; padding: 10px; padding-top: 0'><b>Minima</b><br>$min_pressione mmHg</td>
                                 <td style='width: 33.33%; padding: 10px; padding-top: 0'><b>Media</b><br>$avg_pressione mmHg</td>
                                 <td style='width: 33.33%; padding: 10px; padding-top: 0'><b>Massima</b><br>$max_pressione mmHg</td>
                              </tr>
                              <tr>
                                 <th style='padding-top: 10px' colspan='3'>Temperatura</th>
                              </tr>
                              <tr>
                                 <td><b>Minima</b><br>$min_temperatura °C</td>
                                 <td><b>Media</b><br>$avg_temperatura °C</td>
                                 <td><b>Massima</b><br>$max_temperatura °C</td>
                              </tr>
                              <tr>
                                 <th style='padding-top: 10px' colspan='3'>Saturazione</th>
                              </tr>
                              <tr>
                                 <td><b>Minima</b><br>$min_saturazione %</td>
                                 <td><b>Media</b><br>$avg_saturazione %</td>
                                 <td><b>Massima</b><br>$max_saturazione %</td>
                              </tr>
                              <tr>
                                 <th style='padding-top: 10px' colspan='3'>Battiti</th>
                              </tr>
                              <tr>
                                 <td><b>Minima</b><br>$min_battito bpm</td>
                                 <td><b>Media</b><br>$avg_battito bpm</td>
                                 <td><b>Massima</b><br>$max_battito bpm</td>
                              </tr>
                           </table>";


                     $sql = "SELECT denominazione, SUM(prescrizioni.qta) AS sum_qta, SUM(prescrizioni.qta_ritirata) AS sum_qta_ritirata
                             FROM visite, prescrizioni, farmaci
                             WHERE cod_visita = visite.id AND
                                   cod_farmaco = farmaci.id AND
                                   cod_ricovero = :id_ricovero
                             GROUP BY farmaci.denominazione
                             ORDER BY farmaci.denominazione";
                     $stmt = $conn->prepare($sql);
                     $stmt->bindParam(":id_ricovero", $_POST["id"], PDO::PARAM_INT);
                     $stmt->execute();
                     $res = $stmt->fetchAll();

                     echo "<br><h4>Farmaci</h4>
                           <table align='center' class='table table-bordered'>
                           <tr>
                              <th>Denominazione</th>
                              <th>Quantità richiesta</th>
                              <th>Quantità ritirata</th>
                           </tr>";
                     foreach($res as $row) {
                        $denominazione = $row["denominazione"];
                        $sum_qta = $row["sum_qta"];
                        $sum_qta_ritirata = $row["sum_qta_ritirata"];
                        echo "<tr>
                                 <td>$denominazione</td>
                                 <td>$sum_qta</td>
                                 <td>$sum_qta_ritirata</td>
                              </tr>";
                     }
                     echo "</table>";
                  } catch (PDOException $e) {
                     echo $e;
                     die("<p class='error'>Qualcosa non ha funzionato</p>");
                  }

               ?>

            </div>
         </div>
      </div>

   </body>
</html>
