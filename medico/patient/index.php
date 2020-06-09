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

      <title>Pazienti</title>

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
               <h1 class="display-4 py-2">Pazienti ricoverati</h1>
               <p>Di seguito i pazienti attualmente ricoverati nel reparto</p>

               <div class="table-responsive-lg" align="center">
                  <table class="table table-bordered">
                     <tr style="text-align:center;">
                        <th>Cognome</th> <th>Nome</th> <th>Stanza</th> <th>Medico</th> <th>Data ricovero</th>
                     </tr>

                     <?php
                        try {
                           $conn = connect();
                           // Estrae tutti i pazienti attualmente ricoverati nel reparto
                           $sql = "SELECT ricoveri.id AS id_ricovero, data_inizio,
                                          pazienti.nome AS nome_paziente, pazienti.cognome AS cognome_paziente,
                                          posti.nome AS nome_posto,
                                          medici.id AS id_medico, medici.nome AS nome_medico, medici.cognome AS cognome_medico
                                   FROM pazienti, ricoveri, posti, medici
                                   WHERE cod_paziente = pazienti.cf AND
                                         cod_posto = posti.id AND
                                         cod_medico = medici.id AND
                                         data_fine IS NULL AND
                                         medici.cod_reparto = :id_reparto
                                   ORDER BY pazienti.cognome, pazienti.nome";
                           $stmt = $conn->prepare($sql);
                           $stmt->bindParam(":id_reparto", $_SESSION["reparto"], PDO::PARAM_INT);
                           $stmt->execute();
                           $res = $stmt->fetchAll();

                           foreach($res as $row) {
                              $id_ricovero = htmlentities($row["id_ricovero"]);
                              $cognome = htmlentities($row["cognome_paziente"]);
                              $nome = htmlentities($row["nome_paziente"]);
                              $posto = htmlentities($row["nome_posto"]);
                              $nominativo_medico = htmlentities($row["cognome_medico"] . " " . $row["nome_medico"]);
                              $data = date("d/m/Y H:i", strtotime($row["data_inizio"]));

                              $me = "";
                              if($row["id_medico"] == $_SESSION["id"]) {
                                 $me = "class='me'";
                              }

                              echo "<tr $me>
                                       <td class='text-center'>$cognome</td>
                                       <td class='text-center'>$nome</td>
                                       <td class='text-center'>$posto</td>
                                       <td class='text-center'>$nominativo_medico</td>
                                       <td class='text-center'>$data</td>
                                       <td class='text-center align-middle'><a href='view.php?id=$id_ricovero' class='btn btn-outline-primary btn-sm'>i</a></td>
                                       <form action='../visit/add.php' method='POST'>
                                          <td class='text-center align-middle'><button type='submit' name='id_ricovero' value='$id_ricovero' class='btn btn-outline-primary btn-sm'>Visita</button></td>
                                       </form>";

                              if(!empty($me)) {
                                 echo "<td class='text-center align-middle'>
                                          <button type='button' data-toggle='modal' data-id='$id_ricovero' class='btn btn-outline-success btn-sm click-confirm'>Dimetti</button>
                                       </td>";
                              }

                              echo "</tr>";
                           }
                        } catch (PDOException $e) {
                           die("<p class='error'>Non è stato possibile estrarre le visite</p>");
                        }
                     ?>

                  </table>
               </div>

               <div class='modal' id='confirm' tabindex='-1' role='dialog' aria-labelledby='confirm' aria-hidden='true'>
                  <div class='modal-dialog' role='document'>
                     <div class='modal-content'>
                        <form action="./dimetti.php" method="POST">
                           <div class='modal-body'>
                              <input id="in_id" type="hidden" name="id">
                              <h5 style='margin:8px;'>Confermi la dimissione del paziente?</h5>
                           </div>
                           <div class='modal-footer'>
                              <a class='btn btn-secondary' style='color:white;' data-dismiss='modal'>No</a>
                              <input class='btn btn-success' style='color:white;' type="submit" name="submit" value="Si">
                           </div>
                        </form>
                     </div>
                  </div>
               </div>

            </div>
         </div>
      </div>

   </body>

   <script type="text/javascript">
      $(document).on("click", ".click-confirm", function () {
         var id = $(this).data('id');
         document.getElementById("in_id").value = id;

         $('#confirm').modal('show');
      });
   </script>

</html>
