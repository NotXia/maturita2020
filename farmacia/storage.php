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

   // Verifica che non sia un medico
   if(!isFarmacista()) {
      header("Location: ../medico");
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

      <title>Magazzino</title>
   </head>

   <body>

      <nav class="navbar navbar-expand-sm navbar-light bg-light">
         <div class="navbar-brand">
            <a class="navbar-brand" href="../index.php">
               <table>
                  <tr>
                     <td class="align-middle">
                        <img class="navbar-brand user_nav_logo" src="../img/hospital.png">
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
                  <a class="nav-link" href="index.php">Home</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="delivery/index.php">Consegne</a>
               </li>
               <li class="nav-item active">
                  <a class="nav-link" href="storage.php">Magazzino</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="../logout.php">Logout</a>
               </li>
            </ul>
         </div>
      </nav>

      <div class="container">
         <div class="row text-black">

            <div class="col-xl-10 col-lg-11 col-md-12 col-sm-12 mx-auto text-center p-4">
               <h1 class="display-4 py-2">Gestione magazzino</h1>

               <div class="table-responsive-lg" align="center">
                  <table class="table table-bordered" align="center">
                     <tr style="text-align:center;">
                        <th>Farmaco</th> <th>Descrizione</th> <th>Quantità</th> <th>Richiesta</th>

                        <?php
                           try {
                              $conn = connect();

                              $sql = "SELECT farmaci.id AS id_farmaco, denominazione, descrizione, farmaci.qta AS qta_farmaco,
                                             SUM(prescrizioni.qta) - SUM(qta_ritirata) AS qta_richiesta
                                      FROM farmaci LEFT JOIN prescrizioni ON cod_farmaco = farmaci.id
                                      GROUP BY farmaci.id
                                      ORDER BY denominazione";
                                 $stmt = $conn->prepare($sql);
                                 $stmt->execute();
                                 $res = $stmt->fetchAll();

                                 if(!empty($res)) {
                                    foreach($res as $row) {
                                       $id_farmaco = htmlentities($row["id_farmaco"]);
                                       $nome = htmlentities($row["denominazione"]);
                                       $descrizione = nl2br(htmlentities($row["descrizione"]));
                                       $qta_farmaco = htmlentities($row["qta_farmaco"]);
                                       $qta_richiesta = htmlentities($row["qta_richiesta"]);

                                       if(empty($qta_richiesta)) {
                                          $qta_richiesta = 0;
                                       }

                                       $warning = "";
                                       if($qta_farmaco == 0) {
                                          $warning = "style='color: #ff8200; font-weight: bold;'";
                                       }
                                       if($qta_farmaco < $qta_richiesta) {
                                          $warning = "style='color: #ff0000; font-weight: bold; text-decoration: underline'";
                                       }

                                       echo "<tr class='text-center' $warning>
                                                <td class='align-middle'>$nome</td>
                                                <td class='align-middle text-left'>$descrizione</td>
                                                <td class='align-middle'>$qta_farmaco</td>
                                                <td class='align-middle'>$qta_richiesta</td>
                                                <td class='align-middle'><button type='button' data-toggle='modal' data-id='$id_farmaco' data-qta='$qta_farmaco' class='btn btn-outline-secondary btn-sm click-modify'>Modifica</button></td>
                                             </tr>";
                                    }
                                 }
                                 else {
                                    die("<p>Non ci sono farmaci da consegnare</p>");
                                 }
                           } catch (PDOException $e) {
                              die("<p class='error'>Qualcosa non ha funzionato</p>");
                           }
                        ?>

                     </tr>
                  </table>

                  <button type='button' data-toggle='modal' class='btn btn-outline-success btn-sm click-add'>Aggiungi</button><br><br>

               </div>

               <div class='modal' id='modify' tabindex='-1' role='dialog' aria-labelledby='modify' aria-hidden='true'>
                  <div class='modal-dialog' role='document'>
                     <div class='modal-content'>
                        <div class='modal-header'>
                           <h5 style='margin:8px;'>Modifica</h5>
                        </div>
                        <form class="" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST" autocomplete="off">
                           <div class='modal-body text-center'>
                              <p>Inserisci la quantità da aggiungere o togliere</p>
                              <input id="in_id" type="hidden" name="id">
                              <input id="in_qtanew" type="number" name="add_qta" placeholder="Aggiungi" required>
                           </div>
                           <div class='modal-footer'>
                              <a class='btn btn-secondary' style='color:white;' data-dismiss='modal'>Annulla</a>
                              <input type="submit" name="submit_modify" class='btn btn-primary' style='color:white;' value="Salva">
                           </div>
                        </form>
                     </div>
                  </div>
               </div>

               <div class='modal' id='add' tabindex='-1' role='dialog' aria-labelledby='add' aria-hidden='true'>
                  <div class='modal-dialog' role='document'>
                     <div class='modal-content'>
                        <div class='modal-header'>
                           <h5 style='margin:8px;'>Inserisci</h5>
                        </div>
                        <form class="" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST" autocomplete="off">
                           <div class='modal-body text-center'>
                              <input type="text" name="denominazione" placeholder="Denominazione" required><br><br>
                              <textarea name="descrizione" placeholder="Descrizione" rows="6" cols="22"></textarea><br><br>
                              <input type="number" min="0" name="qta" placeholder="Quantità">
                           </div>
                           <div class='modal-footer'>
                              <a class='btn btn-secondary' style='color:white;' data-dismiss='modal'>Annulla</a>
                              <input class='btn btn-success' style='color:white;' type="submit" name="submit_add" value="Salva">
                           </div>
                        </form>
                     </div>
                  </div>
               </div>

               <?php

                  if(isset($_POST["submit_modify"])) {
                     if(empty($_POST["add_qta"]) || empty($_POST["id"])) {
                        die("<p class='error'>Alcuni campi non sono stati inseriti</p>");
                     }

                     try {
                        $conn = connect();

                        $conn->beginTransaction();

                        $sql = "UPDATE farmaci
                                SET qta = qta + :qta_new
                                WHERE id = :id";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(":qta_new", $_POST["add_qta"], PDO::PARAM_INT);
                        $stmt->bindParam(":id", $_POST["id"], PDO::PARAM_INT);
                        $stmt->execute();

                        $conn->commit();
                        $conn = null;
                        ?>
                        <script type="text/javascript">
                           window.history.back();
                        </script>
                        <?php
                     } catch (PDOException $e) {
                        $conn->rollBack();
                        $conn = null;
                        die("<p class='error'>Qualcosa non ha funzionato</p>");
                     }
                  }

                  if(isset($_POST["submit_add"])) {
                     if(empty($_POST["denominazione"])) {
                        die("<p class='error'>Alcuni campi non sono stati inseriti</p>");
                     }

                     if(!empty($_POST["qta"])) {
                        if($_POST["qta"] < 0) {
                           die("<p class='error'>La quantità non può essere negativa</p>");
                        }
                     }

                     try {
                        $conn = connect();

                        $sql = "INSERT farmaci (denominazione, descrizione, qta) VALUES(:denominazione, :descrizione, :qta)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(":denominazione", $_POST["denominazione"], PDO::PARAM_STR, 100);
                        $stmt->bindParam(":descrizione", $_POST["descrizione"], PDO::PARAM_STR, 500);
                        $stmt->bindParam(":qta", $_POST["qta"], PDO::PARAM_INT);
                        $stmt->execute();

                        $conn = null;
                        ?>
                        <script type="text/javascript">
                           window.history.back();
                        </script>
                        <?php
                     } catch (PDOException $e) {
                        $conn = null;
                        die("<p class='error'>Qualcosa non ha funzionato</p>");
                     }
                  }

               ?>

            </div>

         </div>
      </div>

   </body>

   <script type="text/javascript">
      $(document).on("click", ".click-modify", function () {
         var id = $(this).data('id');
         var qta = $(this).data('qta');
         document.getElementById("in_id").value = id;
         document.getElementById("in_qtanew").min = -1 * qta;

         $('#modify').modal('show');
      });

      $(document).on("click", ".click-add", function () {
         $('#add').modal('show');
      });
   </script>

</html>
