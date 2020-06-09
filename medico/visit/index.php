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

      <title>Visite</title>

      <style media="screen">
         .anagrafica {
            padding: 10px;
         }

         .prescrizione {
            margin: 5px;
         }

         .me {
            background-color: #f4f1f1;
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
            <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 mx-auto text-center p-4">
               <h1 class="display-4 py-2">Storico visite</h1>
               <p>Di seguito le visite effettuate ai pazienti attualmente ricoverati nel reparto</p>

               <div class="table-responsive-lg" align="center">
                  <form action="view.php" method="GET">
                     <table class="table table-bordered">
                        <tr style="text-align:center;">
                           <th>Data</th> <th>Paziente</th> <th>Stanza</th> <th>Medico</th>
                        </tr>

                        <?php
                           try {
                              $conn = connect();
                              $sql = "SELECT visite.id AS id_visita, orario,
                                             pazienti.nome AS nome_paziente, pazienti.cognome AS cognome_paziente,
                                             posti.nome AS nome_posto
                                      FROM visite, ricoveri, posti, pazienti
                                      WHERE cod_ricovero = ricoveri.id AND
                                            cod_posto = posti.id AND
                                            cod_paziente = pazienti.cf AND
                                            data_fine IS NULL AND
                                            visite.cod_medico = :id_medico
                                      ORDER BY orario DESC";
                              $stmt = $conn->prepare($sql);
                              $stmt->bindParam(":id_medico", $_SESSION["id"], PDO::PARAM_INT);
                              $stmt->execute();
                              $res = $stmt->fetchAll();

                              foreach($res as $row) {
                                 $id_visita = htmlentities($row["id_visita"]);
                                 $data = date("d/m/Y H:i", strtotime($row["orario"]));
                                 $nominativo_paziente = htmlentities($row["cognome_paziente"] . " " . $row["nome_paziente"]);
                                 $posto = htmlentities($row["nome_posto"]);

                                 echo "<tr>
                                          <td class='text-center'>$data</td>
                                          <td class='text-center'>$nominativo_paziente</td>
                                          <td class='text-center'>$posto</td>
                                          <td class='text-center'><button type='submit' name='id' value='$id_visita' class='btn btn-outline-primary btn-sm'>i</button></td>
                                       </tr>";
                              }
                           } catch (PDOException $e) {
                              die("<p class='error'>Non è stato possibile estrarre le visite</p>");
                           }
                        ?>

                     </table>
                  </form>
               </div>

            </div>
         </div>
      </div>

   </body>
</html>
