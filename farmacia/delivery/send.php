<?php
   ob_start();
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

   // Verifica che non sia un medico
   if(!isFarmacista()) {
      header("Location: ../../medico");
      exit;
   }

   if(empty($_GET["id_prescrizione"]) && empty($_POST["id_prescrizione"])) {
      header("Location: index.php");
      exit;
   }
   else {
      $id_prescrizione = empty($_GET["id_prescrizione"]) ? $_POST["id_prescrizione"] : $_GET["id_prescrizione"];
   }

   require_once(dirname(__FILE__)."/../../utilities/database.php");

   try {
      $conn = connect();

      // Verifica se la la prescrizione è stata già consegnata
      $sql = "SELECT qta, qta_ritirata
              FROM prescrizioni, visite, ricoveri
              WHERE cod_visita = visite.id AND
                    cod_ricovero = ricoveri.id AND
                    prescrizioni.id = :id_prescrizione AND
                    data_fine IS NULL";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(":id_prescrizione", $id_prescrizione, PDO::PARAM_INT);
      $stmt->execute();
      $res = $stmt->fetch();
      if($res["qta_ritirata"] >= $res["qta"]) {
         header("Location: index.php");
         exit;
      }
      $conn = null;
   } catch (PDOException $e) {
      $conn = null;
      header("Location: index.php");
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
      <link rel="stylesheet" href="../../css/autocomplete.css">
      <script src="../../js/jquery.min.js"></script>
      <script src="../../js/popper.min.js"></script>
      <script src="../../js/bootstrap.min.js"></script>
      <script src="../../js/autocomplete.js"></script>

      <title>Consegna</title>

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
                  <a class="nav-link" href="../index.php">Home</a>
               </li>
               <li class="nav-item active">
                  <a class="nav-link" href="index.php">Consegne</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="../storage.php">Magazzino</a>
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
               <h1 class="display-4 py-2">Consegna</h1>

               <?php
                  try {
                     $conn = connect();

                     $sql = "SELECT prescrizioni.qta AS qta_prescrizione, qta_ritirata,
                                    farmaci.id AS id_farmaco, farmaci.qta AS qta_farmaco, farmaci.denominazione AS nome_farmaco, descrizione,
                                    reparti.denominazione AS nome_reparto,
                                    medici.nome AS nome_medico, medici.cognome AS cognome_medico
                             FROM prescrizioni, farmaci, visite, medici, reparti
                             WHERE cod_farmaco = farmaci.id AND
                                   cod_visita = visite.id AND
                                   cod_medico = medici.id AND
                                   cod_reparto = reparti.id AND
                                   prescrizioni.id = :id_prescrizione";
                     $stmt = $conn->prepare($sql);
                     $stmt->bindParam(":id_prescrizione", $id_prescrizione, PDO::PARAM_INT);
                     $stmt->execute();
                     $res = $stmt->fetch();

                     if(!empty($res)) {
                        $qta_prescrizione = $res["qta_prescrizione"];
                        $qta_ritirata = $res["qta_ritirata"];
                        $qta_farmaco = $res["qta_farmaco"];
                        $id_farmaco = $res["id_farmaco"];
                        $farmaco = $res["nome_farmaco"];
                        $desc_farmaco = nl2br(htmlentities($res["descrizione"]));
                        $reparto = htmlentities($res["nome_reparto"]);
                        $medico = htmlentities($res["cognome_medico"] . " " . $res["nome_medico"]);

                        $max_consegna = $qta_prescrizione-$qta_ritirata > $qta_farmaco ? $qta_farmaco : $qta_prescrizione-$qta_ritirata;

                        echo "<h6 style='margin: 0;'>Richiesto da</h6><h5>$medico - $reparto</h5>"
                        ?>
                        <div class="border border-secondary rounded p-3">
                           <h4><?php echo $farmaco; ?></h4>
                           <span><?php echo $desc_farmaco; ?></span>
                        </div>
                        <br>
                        <table align='center'>
                           <tr>
                              <td style='padding-left:10px;padding-right:10px;'><b>Quantità richiesta</b><br><?php echo htmlentities("$qta_ritirata / $qta_prescrizione"); ?></td>
                              <td style='padding-left:10px;padding-right:10px;'><b>Magazzino</b><br><?php echo htmlentities($qta_farmaco); ?></td>
                           </tr>
                        </table>
                        <br>
                        <?php

                        if($qta_farmaco != 0) {
                           ?>
                           <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST" autocomplete="off">
                              <input type="hidden" name="id_prescrizione" value="<?php echo htmlentities($id_prescrizione); ?>">
                              <input type="hidden" name="id_farmaco" value="<?php echo htmlentities($id_farmaco); ?>">
                              <label for="qta_consegna"><b>Quantità consegna</b></label><br>
                              <input id="qta_consegna" type="number" name="qta_consegna" min=0 max="<?php echo htmlentities($max_consegna); ?>">
                              <br><br>
                              <input class='btn btn-success' type="submit" name="submit" value="Consegna">
                           </form>
                           <?php
                        }
                        else {
                           die("<p class='error'>Farmaco terminato</p>");
                        }
                     }
                     else {
                        die("<p class='error'>Non è stato trovato nessun farmaco</p>");
                     }

                  } catch (PDOException $e) {
                     $conn = null;
                     die("<p class='error'>Qualcosa non ha funzionato</p>");
                  }
               ?>

               <?php

                  if(isset($_POST["submit"])) {
                     if(empty($_POST["qta_consegna"])) {
                        die("<br><p class='error'>Quantità non impostata</p>");
                     }
                     try {
                        $conn = connect();

                        $conn->beginTransaction();

                        $sql = "UPDATE prescrizioni
                                SET qta_ritirata = qta_ritirata + :qta_ritirata
                                WHERE id = :id_prescrizione";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(":qta_ritirata", $_POST["qta_consegna"], PDO::PARAM_INT);
                        $stmt->bindParam(":id_prescrizione", $id_prescrizione, PDO::PARAM_INT);
                        $stmt->execute();

                        $sql = "UPDATE farmaci
                                SET qta = qta - :qta_ritirata
                                WHERE id = :id_farmaco";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(":qta_ritirata", $_POST["qta_consegna"], PDO::PARAM_INT);
                        $stmt->bindParam(":id_farmaco", $id_farmaco, PDO::PARAM_INT);
                        $stmt->execute();

                        $conn->commit();

                        ?>
                        <script type="text/javascript">
                           window.history.back();
                        </script>
                        <?php
                     } catch (PDOException $e) {
                        $conn->rollBack();
                        die("<br><p class='error'>Qualcosa non ha funzionato</p>");
                     }
                  }

               ?>
            </div>
         </div>
      </div>

   </body>

</html>
