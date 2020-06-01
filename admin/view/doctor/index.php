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

      <style>
         .scrollable {
             height: auto;
             max-height: 200px;
             overflow-x: hidden;
         }

         .dropdown-item:active {
            background-color: #f8f9fa;
            color: #16181b;
         }
      </style>

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
            <div class="col-xl-8 col-lg-8 col-md-8 col-sm-12 mx-auto text-center form p-4">
               <h1 class="display-4 py-2">Dottori</h1>

               <div class="table-responsive-lg" align="center">
                  <table class="table table-bordered">
                     <tr style="text-align:center;">
                        <th>Cognome</th> <th>Nome</th> <th>Username</th> <th>Reparti</th>
                     </tr>
                     <?php
                        try {
                           $conn = connect();
                           $sql = "SELECT medici.id AS id_medico, cognome, nome, usr
                                   FROM medici, utenze
                                   WHERE cod_utenza = utenze.id
                                   ORDER BY cognome, nome";
                           $stmt = $conn->prepare($sql);
                           $stmt->execute();
                           $res = $stmt->fetchAll();

                           foreach ($res as $row) {
                              $id_medico = $row["id_medico"];
                              $nome = $row["nome"];
                              $cognome = $row["cognome"];
                              $username = $row["usr"];

                              $sql = "SELECT reparti.id AS id_reparto, denominazione
                                      FROM specializzazioni, reparti
                                      WHERE cod_reparto = reparti.id AND
                                            cod_medico = :id_medico
                                      ORDER BY denominazione";
                              $stmt = $conn->prepare($sql);
                              $stmt->bindParam(":id_medico", $id_medico, PDO::PARAM_INT);
                              $stmt->execute();
                              $res_doc = $stmt->fetchAll();

                              $reparti = "";
                              $reparti_id = "";
                              foreach($res_doc as $row_d) {
                                 $reparti = $reparti . $row_d["denominazione"] . "<br>";
                                 $reparti_id = $reparti_id . $row_d["id_reparto"] . ":";
                              }

                              echo "<tr>
                                       <td style='text-align:center;' class='align-middle'>$cognome</td>
                                       <td style='text-align:center;' class='align-middle'>$nome</td>
                                       <td style='text-align:center;' class='align-middle'>$username</td>
                                       <td style='text-align:center;'>$reparti</td>
                                       <td style='text-align:center;' class='align-middle'>
                                          <a href='#' data-toggle='modal' class='click-delete' data-id='$id_medico'>Elimina</a>
                                       </td>
                                       <td style='text-align:center;' class='align-middle'>
                                          <a href='#' data-toggle='modal' class='click-modify' data-id='$id_medico' data-nome='$nome' data-cognome='$cognome' data-username='$username' data-reparti='$reparti_id'>Modifica</a>
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
                              <form class="" action="delete.php" method="POST">
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
                              <form action="modify.php" method="POST">
                                 <div class='modal-body'>
                                    <input id="in_id" type="hidden" name="id">
                                    <input id="in_nome" type="text" name="nome" placeholder="Nome"><br><br>
                                    <input id="in_cognome" type="text" name="cognome" placeholder="Cognome"><br><br>
                                    <input id="in_username" type="text" name="username" placeholder="Username"><br><br>
                                    <input id="in_password" type="password" name="password" placeholder="Password"><br><br>
                                    <div class="btn-group dropup">
                                       <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                          Reparti
                                       </button>
                                       <div class="dropdown-menu scrollable">
                                          <?php
                                             try {
                                                $conn = connect();
                                                $sql = "SELECT * FROM reparti ORDER BY denominazione";
                                                $stmt = $conn->prepare($sql);
                                                $stmt->execute();
                                                $res = $stmt->fetchAll();

                                                foreach($res as $row) {
                                                   $id = $row["id"];
                                                   $denom = $row["denominazione"];
                                                   echo "<label class='dropdown-item'>
                                                         <input id='in_rep$id' type='checkbox' name='reparto[]' value='$id'> $denom
                                                         </label>";
                                                }
                                                ?>
                                                <script type="text/javascript">
                                                   $(document).on('click', '.dropdown-menu', function (e) {
                                                      e.stopPropagation();
                                                   });
                                                </script>
                                                <?php
                                             } catch (PDOException $e) {
                                                $conn = null;
                                                die("<p class='error'>Si è verificato un errore nel caricamento dei reparti</p>");
                                             }
                                             $conn = null;
                                          ?>
                                       </div>
                                    </div>
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
         var nome = $(this).data('nome');
         var cognome = $(this).data('cognome');
         var username = $(this).data('username');
         var id_reparti = $(this).data('reparti').split(":");

         $(".modal-body #in_id").attr("value", id);
         $(".modal-body #in_nome").attr("value", nome);
         $(".modal-body #in_cognome").attr("value", cognome);
         $(".modal-body #in_username").attr("value", username);

         $(".dropdown-item > input").prop('checked', false);
         for(var i=0; i<id_reparti.length; i++) {
            // alert(".modal-body #in_rep"+id_reparti[i]);
            $(".modal-body #in_rep"+id_reparti[i]).prop('checked', true);
         }

         $('#modify').modal('show');
      });
   </script>

</html>
