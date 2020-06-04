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

   if(empty($_POST["id_ricovero"])) {
      header("Location: ../index.php");
      exit;
   }

   require_once(dirname(__FILE__)."/../../utilities/database.php");

   try {
      $conn = connect();
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
      <script src="../../js/jquery.min.js"></script>
      <script src="../../js/popper.min.js"></script>
      <script src="../../js/bootstrap.min.js"></script>

      <title>Visita</title>

      <style media="screen">
         .header_anagrafica {
            padding-left: 10px;
            padding-right: 10px;
            padding-bottom: 10px;
            text-align: right;
         }
         .info_anagrafica {
            padding-bottom: 10px;
            text-align: left;
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
            <div class="col-xl-8 col-lg-8 col-md-8 col-sm-12 mx-auto text-center p-4">
               <!-- <h1 class="display-4 py-2">Visita</h1> -->

               <div class="border border-secondary rounded p-3">
                  <h5>Dati paziente</h5>
                  <div class="table-responsive">
                     <table align="center">
                        <?php
                           try {
                              $conn = connect();
                              $sql = "SELECT pazienti.nome AS nome_paziente, cognome, ddn, sesso, email, telefono, data_inizio, motivo, posti.nome AS nome_posto
                                      FROM pazienti, ricoveri, posti
                                      WHERE cod_paziente = pazienti.cf AND
                                            cod_posto = posti.id AND
                                            ricoveri.id = :id_ricovero";
                              $stmt = $conn->prepare($sql);
                              $stmt->bindParam(":id_ricovero", $_POST["id_ricovero"], PDO::PARAM_INT);
                              $stmt->execute();
                              $res = $stmt->fetch();

                              $nome = $res["nome_paziente"];
                              $cognome = $res["cognome"];
                              $ddn = date("d/m/Y", strtotime($res["ddn"]));
                              $sesso = $res["sesso"];
                              $email = $res["email"];
                              $telefono = $res["telefono"];
                              $data_inizio = date("d/m/Y H:i", strtotime($res["data_inizio"]));
                              $posto = $res["nome_posto"];
                              $motivo = $res["motivo"];
                              if(!empty($res)) {
                                 echo "<tr>
                                          <th class='header_anagrafica'>Nome</th> <td class='info_anagrafica'>$nome</td>
                                          <th class='header_anagrafica'>Cognome</th> <td class='info_anagrafica'>$cognome</td>
                                       </tr>
                                       <tr>
                                          <th class='header_anagrafica'>Data di nascita</th> <td class='info_anagrafica'>$ddn</td>
                                          <th class='header_anagrafica'>Sesso</th> <td class='info_anagrafica'>$sesso</td>
                                       </tr>
                                       <tr>
                                          <th class='header_anagrafica'>Email</th> <td class='info_anagrafica'>$email</td>
                                          <th class='header_anagrafica'>Telefono</th> <td class='info_anagrafica'>$telefono</td>
                                       </tr>
                                       <tr>
                                       <th class='header_anagrafica'>Data ricovero</th> <td class='info_anagrafica'>$data_inizio</td>
                                       <th class='header_anagrafica'>Stanza</th> <td class='info_anagrafica'>$posto</td>
                                       </tr>
                                       <tr>
                                          <td colspan='4'><b>Motivo</b><br>$motivo</td>
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
      </div>

   </body>


</html>
