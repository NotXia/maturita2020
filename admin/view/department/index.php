<?php
   require_once(dirname(__FILE__)."/../../../utilities/login_check.php");
   if(!logged()) {
      header("Location: ../../login.php");
      exit;
   }
   else if(!adminLogged()) {
      header("Location: ../../../index.php");
      exit;
   }

   require_once(dirname(__FILE__)."/../../../utilities/database.php");
?>

<!DOCTYPE html>
<html>

   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link rel="stylesheet" href="../../../css/bootstrap.min.css">
      <link rel="stylesheet" href="../../../css/styles.css">
      <link rel="stylesheet" href="../../../css/navbar.css">
      <script src="../../../js/jquery.min.js"></script>
      <script src="../../../js/popper.min.js"></script>
      <script src="../../../js/bootstrap.min.js"></script>

      <title>Visualizza reparti</title>
   </head>

   <body class="text-center">

      <nav class="navbar navbar-expand-sm navbar-light bg-light">
         <div class="navbar-brand">
            <a class="navbar-brand" href="../index.php">
               <img class="navbar-brand admin_nav_logo" src="../../../img/wrench.png">
               Admin
            </a>
         </div>
         <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
         </button>
         <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
               <li class="nav-item">
                  <a class="nav-link" href="../../index.php">Home</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="../../add">Inserimento</a>
               </li>
               <li class="nav-item active">
                  <a class="nav-link" href="./../">Visualizzazione</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="../../logout.php">Logout</a>
               </li>
            </ul>
         </div>
      </nav>

      <div class="container">
         <div class="row text-black">
            <div class="col-xl-6 col-lg-7 col-md-8 col-sm-12 mx-auto text-center p-4">
               <h1 class="display-4 py-2">Reparti</h1>

               <div class="table-responsive-lg" align="center">
                  <table class="table table-bordered">
                     <tr style="text-align:center;">
                        <th>Denominazione</th> <th>Medici</th>
                     </tr>
                     <?php
                        try {
                           $conn = connect();
                           $sql = "SELECT *
                                   FROM reparti
                                   ORDER BY denominazione";
                           $stmt = $conn->prepare($sql);
                           $stmt->execute();
                           $res = $stmt->fetchAll();

                           foreach ($res as $row) {
                              $id_reparto = $row["id"];
                              $denom = $row["denominazione"];

                              $sql = "SELECT nome, cognome
                                      FROM specializzazioni, medici
                                      WHERE cod_medico = medici.id AND
                                            cod_reparto = :id_reparto
                                      ORDER BY cognome, nome";
                              $stmt = $conn->prepare($sql);
                              $stmt->bindParam(":id_reparto", $id_reparto, PDO::PARAM_INT);
                              $stmt->execute();
                              $res_doc = $stmt->fetchAll();

                              $medici = "";
                              foreach($res_doc as $row_d) {
                                 $medici = $medici . $row_d["cognome"] . " " . $row_d["nome"] . "<br>";
                              }

                              echo "<tr>
                                       <td style='text-align:center;' class='align-middle'>$denom</td>
                                       <td style='text-align:center;'>$medici</td>
                                       <td style='text-align:center;' class='align-middle'>
                                          <a href='#' data-toggle='modal' class='click-delete' data-id='$id_reparto'>Elimina</a>
                                       </td>
                                       <td style='text-align:center;' class='align-middle'>
                                          <a href='#' data-toggle='modal' class='click-modify' data-id='$id_reparto' data-denominazione='$denom'>Modifica</a>
                                       </td>
                                    </tr>";

                           }
                        } catch (PDOException $e) {
                           $conn = null;
                           die("<p class='error'>Si è verificato un errore nel caricamento delle utenze</p>");
                        }
                        $conn = null;
                     ?>

                     <div class='modal' id='delete' tabindex='-1' role='dialog' aria-labelledby='del$id' aria-hidden='true'>
                        <div class='modal-dialog' role='document'>
                           <div class='modal-content'>
                              <form action="delete.php" method="POST">
                                 <div class='modal-body'>
                                    <input id="in_id" type="hidden" name="id">
                                    <h5 style='margin:8px;'>Confermi la cancellazione?</h5>
                                 </div>
                                 <div class='modal-footer'>
                                    <a class='btn btn-secondary' style='color:white;' data-dismiss='modal'>No</a>
                                    <input class='btn btn-danger' style='color:white;' type="submit" name="submit" value="Si">
                                 </div>
                              </form>
                           </div>
                        </div>
                     </div>

                     <div class='modal' id='modify' tabindex='-1' role='dialog' aria-labelledby='del$id' aria-hidden='true'>
                        <div class='modal-dialog role='document'>
                           <div class='modal-content'>
                              <div class='modal-header'>
                                 <h5 style='margin:8px;'>Modifica</h5>
                              </div>
                              <form action="modify.php" method="POST">
                                 <div class='modal-body'>
                                    <input id="in_id" type="hidden" name="id">
                                    <label for="in_denominazione">Denominazione</label>
                                    <input id="in_denominazione" type="text" name="denominazione">
                                 </div>
                                 <div class='modal-footer'>
                                    <a class='btn btn-secondary' style='color:white;' data-dismiss='modal'>Annulla</a>
                                    <input class='btn btn-secondary' style='color:white;' type="submit" name="submit" value="Salva">
                                 </div>
                              </form>
                           </div>
                        </div>
                     </div>

                  </div>

               </div>
            </div>
         </div>

   </body>

   <script type="text/javascript">
      $(document).on("click", ".click-delete", function () {
         var id = $(this).data('id');
         $(".modal-body #in_id").attr("value", id);

         $('#delete').modal('show');
      });

      $(document).on("click", ".click-modify", function () {
         var id = $(this).data('id');
         var denom = $(this).data('denominazione');
         $(".modal-body #in_id").attr("value", id);
         $(".modal-body #in_denominazione").attr("value", denom);

         $('#modify').modal('show');
      });
   </script>

</html>
