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

   if(empty($_GET["id"])) {
      header("Location: ../index.php");
      exit;
   }

   require_once(dirname(__FILE__)."/../../utilities/database.php");

   try {
      $conn = connect();

      // Verifica se è un paziente del reparto
      $sql = "SELECT cod_reparto
              FROM ricoveri, medici
              WHERE cod_medico = medici.id AND
                    ricoveri.id = :id_ricovero";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(":id_ricovero", $_GET["id"], PDO::PARAM_INT);
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

         .visita {
            margin: 10px;
            overflow: auto;
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
            <div class="col-xl-7 col-lg-8 col-md-10 col-sm-12 mx-auto text-center p-4">
               <h1 class="display-4 py-2">Paziente</h1>

               <div class="border border-secondary rounded p-3">
                  <div class="table-responsive">
                     <table align="center">
                        <?php
                           try {
                              $conn = connect();
                              $sql = "SELECT pazienti.nome AS nome_paziente, pazienti.cognome AS cognome_paziente, ddn, sesso, email, telefono,
                                             data_inizio, motivo, posti.nome AS nome_posto,
                                             medici.nome AS nome_medico, medici.cognome AS cognome_medico
                                      FROM pazienti, ricoveri, posti, medici
                                      WHERE cod_paziente = pazienti.cf AND
                                            cod_posto = posti.id AND
                                            ricoveri.cod_medico = medici.id AND
                                            ricoveri.id = :id_ricovero";
                              $stmt = $conn->prepare($sql);
                              $stmt->bindParam(":id_ricovero", $_GET["id"], PDO::PARAM_INT);
                              $stmt->execute();
                              $res = $stmt->fetch();

                              if(!empty($res)) {
                                 $nome = $res["nome_paziente"];
                                 $cognome = $res["cognome_paziente"];
                                 $ddn = date("d/m/Y", strtotime($res["ddn"]));
                                 $sesso = $res["sesso"];
                                 $email = $res["email"];
                                 $telefono = $res["telefono"];
                                 $data_inizio = date("d/m/Y H:i", strtotime($res["data_inizio"]));
                                 $nominaivo_medico = $res["cognome_medico"] . " " . $res["nome_medico"];
                                 $posto = $res["nome_posto"];
                                 $motivo = $res["motivo"];

                                 echo "<tr>
                                          <td class='anagrafica'><b>Nome</b><br>$nome</td>
                                          <td class='anagrafica'><b>Cognome</b><br>$cognome</td>
                                          <td class='anagrafica'><b>Sesso</b><br>$sesso</td>
                                       </tr>
                                       <tr>
                                          <td class='anagrafica'><b>Data di nascita</b><br>$ddn</td>
                                          <td class='anagrafica'><b>Email</b><br>$email</td>
                                          <td class='anagrafica'><b>Telefono</b><br>$telefono</td>
                                       </tr>
                                       <tr>
                                          <td class='anagrafica'><b>Data ricovero</b><br>$data_inizio</td>
                                          <td class='anagrafica'><b>Stanza</b><br>$posto</td>
                                          <td class='anagrafica'><b>Medico</b><br>$nominaivo_medico</td>
                                       </tr>
                                       <tr>
                                          <td colspan='3'><b>Motivo</b><br>$motivo</td>
                                       </tr>";
                              }
                              else {
                                 die("<br><span class='error'>Non è stato possibile trovare i dati del paziente</span>");
                              }

                           } catch (PDOException $e) {
                              $conn = null;
                              die("<br><span class='error'>Qualcosa non ha funzionato</span>");
                           }
                        ?>
                     </table>
                  </div>
               </div>

            </div>
         </div>

         <div class="row text-black">
            <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 mx-auto text-center">
               <?php
                  try {
                     $conn = connect();
                     $sql = "SELECT visite.id AS id_visita, orario, nome, cognome
                             FROM visite, medici
                             WHERE cod_medico = medici.id AND
                                   cod_ricovero = :id_ricovero
                             ORDER BY orario DESC";
                     $stmt = $conn->prepare($sql);
                     $stmt->bindParam(":id_ricovero", $_GET["id"], PDO::PARAM_INT);
                     $stmt->execute();
                     $res = $stmt->fetchAll();

                     if(!empty($res)) {
                        ?>
                           <h5>Storico visite</h5>
                           <form action="../visit/view.php" method="GET">
                              <div class="table-responsive">
                                 <table class="table table-bordered">
                                    <?php
                                    foreach($res as $row) {
                                       $id_visita = $row["id_visita"];
                                       $orario = date("d/m/Y H:i", strtotime($row["orario"]));
                                       $nominaivo_medico = $row["cognome"] . " " . $row["nome"];

                                       echo "<tr><td style='border-right: 1px solid white!important;'>";
                                       echo "<p class='visita'><b>Orario</b> $orario</p>
                                             <p><b>Medico</b> $nominaivo_medico</p>";

                                       $sql = "SELECT posologia, prescrizioni.qta AS qta, qta_ritirata, denominazione
                                       FROM prescrizioni, farmaci
                                       WHERE cod_farmaco = farmaci.id AND
                                       cod_visita = :id_visita";
                                       $stmt = $conn->prepare($sql);
                                       $stmt->bindParam(":id_visita", $id_visita, PDO::PARAM_INT);
                                       $stmt->execute();
                                       $res_farmaci = $stmt->fetchAll();

                                       if(!empty($res_farmaci)) {
                                          ?>
                                          <div class="table-responsive">
                                             <table class="table table-bordered">
                                                <tr>
                                                   <th>Farmaco</th> <th>Posologia</th> <th>Quantità</th> <th>Ritirata</th>
                                                </tr>
                                                <?php
                                                foreach($res_farmaci as $row) {
                                                   $farmaco = $row["denominazione"];
                                                   $posologia = $row["posologia"];
                                                   $qta = $row["qta"];
                                                   $qta_ritirata = $row["qta_ritirata"];
                                                   echo "<tr>
                                                      <td>$farmaco</td> <td class='text-left'>$posologia</td> <td>$qta</td> <td>$qta_ritirata</td>
                                                   </tr>";
                                                }
                                                ?>
                                             </table>
                                          </div>
                                          <?php
                                       }

                                       echo "</td>
                                       <td style='border-left: 1px solid white!important;' class='align-middle'><button type='submit' name='id' value='$id_visita' class='btn btn-outline-primary btn-sm'>i</button></td>
                                    </tr>";
                                 }
                                 ?>
                              </table>
                           </div>
                        </form>
                        <?php
                     }
                     else {
                        die("<br><span class='error'>Il paziente non ha visite</span>");
                     }
                  }
                  catch(PDOException $e) {
                     die("<br><span class='error'>Qualcosa non ha funzionato</span>");
                  }
               ?>
            </div>
         </div>

      </div>

   </body>
</html>
