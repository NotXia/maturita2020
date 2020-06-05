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

   if(empty($_POST["id_ricovero"])) {
      header("Location: ../index.php");
      exit;
   }

   require_once(dirname(__FILE__)."/../../utilities/database.php");

   try {
      $conn = connect();

      // Verifica se è un paziente del reparto
      $sql = "SELECT cod_reparto
              FROM ricoveri, posti
              WHERE cod_posto = posti.id AND
                    ricoveri.id = :id_ricovero";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(":id_ricovero", $_POST["id_ricovero"], PDO::PARAM_INT);
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
      <link rel="stylesheet" href="../../css/autocomplete.css">
      <script src="../../js/jquery.min.js"></script>
      <script src="../../js/popper.min.js"></script>
      <script src="../../js/bootstrap.min.js"></script>
      <script src="../../js/autocomplete.js"></script>

      <title>Visita</title>

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
                  <a class="nav-link" href="../index.php">Home</a>
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
               <h1 class="display-4 py-2">Visita</h1>

               <div class="border border-secondary rounded p-3">
                  <h5>Dati paziente</h5>
                  <div class="table-responsive" >
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
                                            cod_medico = medici.id AND
                                            ricoveri.id = :id_ricovero";
                              $stmt = $conn->prepare($sql);
                              $stmt->bindParam(":id_ricovero", $_POST["id_ricovero"], PDO::PARAM_INT);
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
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 mx-auto text-center">

               <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
                  <input type="hidden" name="id_ricovero" value="<?php if(!empty($_POST["id_ricovero"])) echo htmlentities($_POST["id_ricovero"]); ?>">
                  <h3>Misurazioni</h3>
                  <div class="form-group">
                     <label for="pressione">Pressione (mmHg)</label><br>
                     <input id="pressione" name="pressione" type="number" step="0.1">
                  </div>

                  <div class="form-group">
                     <label for="temperatura">Temperatura (°C)</label><br>
                     <input id="temperatura" name="temperatura" type="number" step="0.1">
                  </div>

                  <div class="form-group">
                     <label for="saturazione">Saturazione (%)</label><br>
                     <input id="saturazione" name="saturazione" type="number" step="0.1">
                  </div>

                  <div class="form-group">
                     <label for="battito">Battito (bpm)</label><br>
                     <input id="battito" name="battito" type="number">
                  </div>

                  <div class="form-group">
                     <textarea name="note" rows="8" cols="30" placeholder="Note"></textarea>
                  </div>
                  <br>

                  <h3>Prescrizioni</h3>

                  <div class="form-group">
                     <div style="width:auto;" class="autocomplete align-middle" style="width:300px;">
                        <input id="farmaco" type="text" placeholder="Farmaco">
                     </div>
                     <button id="add_button" type="button" class="btn btn-outline-secondary btn-sm align-middle" name="button" onclick="addRow()">+</button>
                     <p id="error_msg" class="error"></p>
                     <div id="in_farmaci">
                     </div>
                  </div>

                  <div class="form-group">
                     <input type="submit" class="btn btn-success" name="submit" value="Conferma">
                  </div>
               </form>

            </div>
         </div>

      </div>

   </body>

   <script type="text/javascript">
      index = 0;

         <?php
            try {
               $conn = connect();
               $sql = "SELECT id, denominazione FROM farmaci ORDER BY denominazione";
               $stmt = $conn->prepare($sql);
               $stmt->execute();
               $res = $stmt->fetchAll();

               $farmaci = "";
               $id_farmaci = "";
               foreach($res as $row) {
                  $id = $row["id"];
                  $nome = $row["denominazione"];
                  $farmaci = $farmaci . "'$nome',";
                  $id_farmaci = $id_farmaci . "'$id',";
               }

               $farmaci = substr($farmaci, 0, -1);
               $id_farmaci = substr($id_farmaci, 0, -1);
               echo "var farmaci = [" . $farmaci . "];";
               echo "var id_farmaci = [" . $id_farmaci . "];";
            } catch (PDOException $e) {
               echo "";
            }
         ?>

      var farmaci_selected = [];

      autocomplete(document.getElementById("farmaco"), farmaci);

      function addRow() {
         var nome_farmaco = document.getElementById("farmaco").value;
         var input_index = farmaci.indexOf(nome_farmaco);

         if(input_index != -1) {
            if(farmaci_selected.indexOf(farmaci[input_index]) == -1) {
               var table = document.getElementById("in_farmaci");

               var lbl_farmaco = document.createElement("input");
               lbl_farmaco.type = "text";
               lbl_farmaco.value = farmaci[input_index];
               lbl_farmaco.className = "align-middle prescrizione";
               lbl_farmaco.disabled = true;
               table.appendChild(lbl_farmaco);

               var id_farmaco = document.createElement("input");
               id_farmaco.type = "hidden";
               id_farmaco.value = id_farmaci[input_index];
               id_farmaco.name = "farmaco[" + index + "]";
               table.appendChild(id_farmaco);

               var qta = document.createElement("input");
               qta.type = "number";
               qta.name = "qta[" + index + "]";
               qta.min = "0";
               qta.placeholder = "Quantità";
               qta.className = "align-middle prescrizione";
               qta.required = true;
               table.appendChild(qta);

               var posologia = document.createElement("textarea");
               posologia.name = "posologia[" + index + "]";
               posologia.rows = "4";
               posologia.placeholder = "Posologia";
               posologia.className = "align-middle prescrizione";
               posologia.required = true;
               table.appendChild(posologia);

               table.appendChild(document.createElement("br"));
               table.appendChild(document.createElement("br"));

               index++;
               farmaci_selected.push(farmaci[input_index]);
            }
            else {
               document.getElementById("error_msg").innerHTML = "Hai già selezionato il farmaco";
            }
         }
         else {
            document.getElementById("error_msg").innerHTML = "Il farmaco non è presente in farmacia";
         }
      }

      document.getElementById("farmaco").addEventListener("click", function(e) {
         document.getElementById("error_msg").innerHTML = "";
      });

      document.getElementById("add_button").addEventListener("click", function(e) {
         document.getElementById("farmaco").value = "";
      });

   </script>

</html>

<?php

   if(isset($_POST["submit"])) {

      try {
         $conn = connect();

         $conn->beginTransaction();

         $note = trim($_POST["note"]);

         $sql = "INSERT visite (orario, pressione, temperatura, saturazione, note, battito, cod_ricovero, cod_medico)
                 VALUES(NOW(), :pressione, :temperatura, :saturazione, :note, :battito, :cod_ricovero, :cod_medico)";
         $stmt = $conn->prepare($sql);
         $stmt->bindParam(":pressione", $_POST["pressione"]);
         $stmt->bindParam(":temperatura", $_POST["temperatura"]);
         $stmt->bindParam(":saturazione", $_POST["saturazione"]);
         $stmt->bindParam(":battito", $_POST["battito"]);
         $stmt->bindParam(":note", $note);
         $stmt->bindParam(":cod_ricovero", $_POST["id_ricovero"]);
         $stmt->bindParam(":cod_medico", $_SESSION["id"]);
         $stmt->execute();

         $id_visita = $conn->lastInsertId();

         if(isset($_POST["farmaco"])) {
            foreach($_POST["farmaco"] as $index=>$id_farmaco) {
               $sql = "INSERT prescrizioni (posologia, qta, cod_farmaco, cod_visita)
                       VALUES(:posologia, :qta, :cod_farmaco, :cod_visita)";
               $stmt = $conn->prepare($sql);
               $stmt->bindParam(":posologia", $_POST["posologia"][$index]);
               $stmt->bindParam(":qta", $_POST["qta"][$index]);
               $stmt->bindParam(":cod_farmaco", $id_farmaco);
               $stmt->bindParam(":cod_visita", $id_visita);
               $stmt->execute();
            }
         }

         $conn->commit();

         header("Location: ../visit/view.php?id=$id_visita");

      } catch (PDOException $e) {
         $conn->rollBack();
         die("<p class='error'>Qualcosa non ha funzionato</p>");
      }

   }

?>
