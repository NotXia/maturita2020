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

   // Verifica che non sia un medico
   if(!isFarmacista()) {
      header("Location: ../../medico");
      exit;
   }

   require_once(dirname(__FILE__)."/../../utilities/database.php");
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

      <title>Consegne</title>
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

            <div class="col-xl-10 col-lg-11 col-md-12 col-sm-12 mx-auto text-center p-4">
               <h1 class="display-4 py-2">Consegne</h1>

               <div class="table-responsive-lg" align="center">
                  <form action="send.php" method="GET">
                     <?php
                        try {
                           $conn = connect();

                           $sql = "SELECT reparti.denominazione AS nome_reparto, farmaci.denominazione AS nome_farmaco,
                                          prescrizioni.id AS id_prescrizione, prescrizioni.qta AS qta_prescrizione, qta_ritirata,
                                          orario
                                   FROM farmaci, prescrizioni, visite, medici, reparti
                                   WHERE cod_farmaco = farmaci.id AND
                                         cod_visita = visite.id AND
                                         cod_medico = medici.id AND
                                         cod_reparto = reparti.id AND
                                         qta_ritirata != prescrizioni.qta
                                    ORDER BY orario";
                              $stmt = $conn->prepare($sql);
                              $stmt->bindParam(":id_medico", $_SESSION["id"], PDO::PARAM_INT);
                              $stmt->execute();
                              $res = $stmt->fetchAll();

                              if(!empty($res)) {
                                 echo "<table class='table table-bordered' align='center'>
                                       <tr style='text-align:center;'>
                                       <th>Reparto</th> <th>Farmaco</th> <th>Quantità</th> <th>Data prescrizione</th>";
                                 foreach($res as $row) {
                                    $id_prescrizione = htmlentities($row["id_prescrizione"]);
                                    $reparto = htmlentities($row["nome_reparto"]);
                                    $farmaco = htmlentities($row["nome_farmaco"]);
                                    $qta = htmlentities($row["qta_prescrizione"]);
                                    $qta_ritirata = htmlentities($row["qta_ritirata"]);
                                    $orario = date("d/m/Y H:i", strtotime($row["orario"]));

                                    echo "<tr class='text-center'>
                                             <td>$reparto</td>
                                             <td>$farmaco</td>
                                             <td>$qta_ritirata / $qta</td>
                                             <td>$orario</td>
                                             <td><button type='submit' name='id_prescrizione' value='$id_prescrizione' class='btn btn-outline-primary btn-sm'>Gestisci</button></td>
                                          </tr>";
                                 }
                                 echo "</tr>
                                       </table>";
                              }
                              else {
                                 die("<p>Non ci sono farmaci da consegnare</p>");
                              }
                        } catch (PDOException $e) {
                           die("<p class='error'>Qualcosa non ha funzionato</p>");
                        }
                     ?>
                  </form>
               </div>
            </div>


         </div>
      </div>

   </body>


</html>
