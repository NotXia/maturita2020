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
            <div class="col-xl-8 col-lg-8 col-md-8 col-sm-12 mx-auto text-center p-4">
               <h1 class="display-4 py-2">Reparti</h1>

               <div class="table-responsive-lg" align="center">
                  <table class="table table-bordered">
                     <tr style="text-align:center;">
                        <th class="align-middle">Denominazione</th>  <th class="align-middle">Posti totali</th> <th class="align-middle">Medici</th>
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
                              $id_reparto = htmlentities($row["id"]);
                              $denom = htmlentities($row["denominazione"]);

                              $sql = "SELECT id, nome
                                      FROM posti
                                      WHERE cod_reparto = :id_reparto";
                              $stmt = $conn->prepare($sql);
                              $stmt->bindParam(":id_reparto", $id_reparto, PDO::PARAM_INT);
                              $stmt->execute();
                              $res_posti = $stmt->fetchAll();
                              $posti_tot = count($res_posti);
                              $posti = "";
                              foreach ($res_posti as $row) {
                                 $posti = $posti . ":" . htmlentities($row["id"]) . ";" . htmlentities($row["nome"]);
                              }

                              // Estrae i posti occupati
                              $sql = "SELECT id
                                      FROM posti
                                      WHERE cod_reparto = :id_reparto AND
                                            id NOT IN (SELECT cod_posto
                                                   FROM posti, ricoveri
                                                   WHERE cod_posto = posti.id AND
                                                         cod_reparto = :id_reparto AND
                                                         data_fine IS NULL)";
                              $stmt = $conn->prepare($sql);
                              $stmt->bindParam(":id_reparto", $_SESSION["reparto"], PDO::PARAM_INT);
                              $stmt->execute();
                              $res = $stmt->fetchAll();
                              $stmt = $conn->prepare($sql);
                              $stmt->bindParam(":id_reparto", $id_reparto, PDO::PARAM_INT);
                              $stmt->execute();
                              $res_posti_occupati = $stmt->fetchAll();
                              $posti_occupati = "";
                              foreach ($res_posti_occupati as $row) {
                                 $posti_occupati = $posti_occupati . ":" . htmlentities($row["id"]);
                              }

                              // Estrazione dei medici del reparto
                              $sql = "SELECT nome, cognome
                                      FROM medici
                                      WHERE cod_reparto = :id_reparto
                                      ORDER BY cognome, nome";
                              $stmt = $conn->prepare($sql);
                              $stmt->bindParam(":id_reparto", $id_reparto, PDO::PARAM_INT);
                              $stmt->execute();
                              $res_doc = $stmt->fetchAll();
                              $medici = "";
                              foreach($res_doc as $row_d) {
                                 $medici = $medici . htmlentities($row_d["cognome"] . " " . $row_d["nome"]) . "<br>";
                              }

                              $del = "";
                              if(strlen($medici) == 0) {
                                 $del = "<a href='#' data-toggle='modal' class='click-delete' data-id='$id_reparto'>Elimina</a>";
                              }
                              else {
                                 $del = "<span data-toggle='tooltip' data-placement='right' title='Ci sono dei medici in questo reparto'>
                                             Elimina
                                          </span>";
                              }

                              echo "<tr>
                                       <td style='text-align:center;' class='align-middle'>$denom</td>
                                       <td style='text-align:center;' class='align-middle'>$posti_tot</td>
                                       <td style='text-align:center;'>$medici</td>
                                       <td style='text-align:center;' class='align-middle'>
                                          $del
                                       </td>
                                       <td style='text-align:center;' class='align-middle'>
                                          <a href='#' data-toggle='modal' class='click-modify' data-id='$id_reparto' data-denominazione='$denom' data-posti='$posti' data-posti_occupati='$posti_occupati'>Modifica</a>
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
                        <div class='modal-dialog' role='document'>
                           <div class='modal-content'>
                              <div class='modal-header'>
                                 <h5 style='margin:8px;'>Modifica</h5>
                              </div>
                              <form id="in_posti" action="modify.php" method="POST">
                                 <input id="in_id_modify" type="hidden" name="id">
                                 <div class='modal-body'>
                                    <button type="button" class="btn btn-light btn-sm" name="button" onclick="addRow()">Aggiungi</button><br>
                                    <hr>
                                    <input id="in_denominazione" type="text" name="denominazione" placeholder="Denominazione" required><br>
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
      $(document).ready(function(){
         $('[data-toggle="tooltip"]').tooltip();
      });

      $(document).on("click", ".click-delete", function () {
         var id = $(this).data('id');
         $(".modal-body #in_id").attr("value", id);

         $('#delete').modal('show');
      });

      $(document).on("click", ".click-modify", function () {

         var id = $(this).data('id');
         var denom = $(this).data('denominazione');
         var posti = $(this).data('posti').split(":");
         var posti_occupati = $(this).data('posti_occupati').split(":");
         $("#in_id_modify").attr("value", id);
         $("#in_denominazione").attr("value", denom);

         for(var i=1; i<posti.length; i++) {
            var parti = posti[i].split(";");

            disabled = false;
            if(posti_occupati.indexOf(parti[0]) == -1) {
               disabled = true;
            }

            $("<div></div>")
               .attr("id", "div_posto"+i)
               .appendTo(" #in_posti");

            $("<br>")
               .appendTo(" #"+"div_posto"+i);

            $("<input type='text'>")
               .attr("id", "in_posto"+i)
               .attr("class", "align-middle")
               .attr("name", "posti_old[" + parti[0] + "]")
               .attr("value", parti[1])
               .appendTo(" #"+"div_posto"+i);

            $("<span>&nbsp</span>")
               .appendTo(" #"+"div_posto"+i);

            $("<button type='button'>Elimina</button>")
               .attr("id", "btn_posto"+i)
               .attr("class", "btn btn-outline-danger btn-sm align-middle")
               .attr("onclick", "delete_row(" + i +")")
               .attr("disabled", disabled)
               .appendTo(" #"+"div_posto"+i);
         }

         $('#modify').modal('show');
      });

      $(document).on('hidden.bs.modal', function () {
         $(this).find('form').trigger('reset');
         $("#in_posti").empty();
      });

      function delete_row(index) {
         document.getElementById("div_posto" + index).style.display = "none";
         document.getElementById("in_posto" + index).value = null;
      }

      function addRow() {
         var row = document.createElement("input");
         row.type = "text";
         row.name = "posti_new[]";
         row.placeholder = "Nome";

         document.getElementById("in_posti").appendChild(document.createElement("br"));
         document.getElementById("in_posti").appendChild(row);
         document.getElementById("in_posti").appendChild(document.createElement("br"));
      }

      $('#in_posti').on('keyup keypress', function(e) {
         var keyCode = e.keyCode || e.which;
         if (keyCode === 13) {
            e.preventDefault();
            return false;
         }
      });
   </script>

</html>
