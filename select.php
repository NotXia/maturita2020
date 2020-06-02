<?php
   session_start();

   require_once(dirname(__FILE__)."/utilities/login_check.php");
   if(!logged()) {
      header("Location: login.php");
      exit;
   }

   $id_reparti = implode(",", $_SESSION["reparti"]);

   require_once(dirname(__FILE__)."/utilities/database.php");

   try {

      $conn = connect();
      $sql = "SELECT id, denominazione
             FROM reparti
             WHERE id IN ($id_reparti)";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $res_reparti = $stmt->fetchAll();

      // Se un utente lavora in un solo reparto
      if(count($_SESSION["reparti"]) == 1) {
         $_SESSION["curr_reparto"] = $_SESSION["reparti"][0];
         $_SESSION["curr_reparto_name"] = $res_reparti[0]["denominazione"];
         header("Location: index.php");
         exit;
      }

   } catch (PDOException $e) {
      die("<span class='error'>Qualcosa non ha funzionato</span>");
   }
?>

<!DOCTYPE html>
<html>

   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link rel="stylesheet" href="./css/bootstrap.min.css">
      <link rel="stylesheet" href="./css/navbar.css">
      <link rel="stylesheet" href="./css/styles.css">
      <script src="./js/jquery.min.js"></script>
      <script src="./js/popper.min.js"></script>
      <script src="./js/bootstrap.min.js"></script>

      <title>Reparti</title>
   </head>

   <body class="text-center">

      <nav class="navbar navbar-expand-sm navbar-light bg-light">
         <div class="navbar-brand">
            <a class="navbar-brand" href="index.php">
               <img class="navbar-brand admin_nav_logo" src="img/hospital.png">
               TechnoLab
            </a>
         </div>
         <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
         </button>
         <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
               <li class="nav-item">
                  <a class="nav-link" href="logout.php">Logout</a>
               </li>
            </ul>
         </div>
      </nav>

      <div class="container">
         <div class="row text-black">
            <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 mx-auto text-center p-4">
               <h1 class="display-4 py-2">Selezione</h1>
               <h4>Seleziona il reparto in cui operare</h4>
            </div>
         </div>

         <div class="row text-black">
            <div class="col-xl-5 col-lg-6 col-md-8 col-sm-10 mx-auto text-center p-4">
               <?php
                  foreach($res_reparti as $row) {
                     $id_reparto = $row["id"];
                     $denominazione = $row["denominazione"];
                     ?>
                     <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
                        <input type="hidden" name="id_reparto" value="<?php echo($id_reparto); ?>">
                        <input style="width:100%;padding:15px;" class="btn btn-outline-secondary" type="submit" name="submit" value="<?php echo($denominazione); ?>">
                     </form>
                     <br>
                     <?php
                  }
               ?>
            </div>
         </div>

      </div>

   </body>

</html>

<?php

   if(isset($_POST["submit"])) {
      try {
         $sql = "SELECT COUNT(*) AS num_med, reparti.id AS id_reparto, denominazione
                 FROM specializzazioni, reparti
                 WHERE cod_reparto = reparti.id AND
                       cod_medico = :cod_medico AND
                       cod_reparto = :cod_reparto";
         $stmt = $conn->prepare($sql);
         $stmt->bindParam(":cod_medico", $_SESSION["id"], PDO::PARAM_INT);
         $stmt->bindParam(":cod_reparto", $_POST["id_reparto"], PDO::PARAM_INT);
         $stmt->execute();
         $res = $stmt->fetch();

         if($res["num_med"] == 1) {
            $_SESSION["curr_reparto"] = $res["id_reparto"];
            $_SESSION["curr_reparto_name"] = $res["denominazione"];
            header("Location: index.php");
            exit;
         }
         else {
            die("<span class='error'>Si è verificato un errore</span>");
         }

      } catch (PDOException $e) {
         die("<span class='error'>Qualcosa non ha funzionato</span>");
      }
   }

?>
