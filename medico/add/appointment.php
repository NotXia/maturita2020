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

   require_once(dirname(__FILE__)."/../../utilities/database.php");

   function gotoForm1() {
      ?>
         <script type="text/javascript">
            document.getElementById("form_1").style.display = "block";
            document.getElementById("form_2").style.display = "none";
            document.getElementById("form_3").style.display = "none";
         </script>
      <?php
   }

   function gotoForm2() {
      ?>
         <script type="text/javascript">
            document.getElementById("form_2").style.display = "block";
            document.getElementById("form_1").style.display = "none";
            document.getElementById("form_3").style.display = "none";
         </script>
      <?php
   }

   function gotoForm3() {
      ?>
         <script type="text/javascript">
            document.getElementById("form_3").style.display = "block";
            document.getElementById("form_1").style.display = "none";
            document.getElementById("form_2").style.display = "none";
         </script>
      <?php
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

      <title>Inserisci visita</title>
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

         <div class="row">
            <div class="col-xl-6 col-lg-7 col-md-8 col-sm-10 mx-auto p-4 text-center">
               <h1 class="display-4">Inserimento visita</h1>

               <div id="form_1">
                  <h3>Dati del paziente</h3><br>
                  <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
                     <div class="form-group">
                        <label for="cf">Codice fiscale</label><br>
                        <input id="cf" name="cf" type="text" value="<?php if(!empty($_POST['cf'])) echo $_POST['cf']; ?>" maxlength="16" required>
                     </div>

                     <div class="form-group">
                        <input name="submit_1" type="submit" value="Avanti">
                     </div>
                  </form>
               </div>

               <div id="form_2" style="display:none;">
                  <h3>Dati del paziente</h3><br>
                  <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
                     <input type="hidden" name="cf" value="<?php if(!empty($_POST['cf'])) echo $_POST['cf']; ?>">
                     <div class="form-group">
                        <label for="nome">Nome</label><br>
                        <input id="nome" name="nome" type="text" value="<?php if(!empty($_POST['nome'])) echo $_POST['nome']; ?>" maxlength="100" required>
                     </div>
                     <div class="form-group">
                        <label for="cognome">Cognome</label><br>
                        <input id="cognome" name="cognome" type="text" value="<?php if(!empty($_POST['cognome'])) echo $_POST['cognome']; ?>" maxlength="100" required>
                     </div>
                     <div class="form-group">
                        <label for="ddn">Data di nascita</label><br>
                        <input id="ddn" name="ddn" type="date" max="<?php echo htmlentities(date("Y-m-d")); ?>" value="<?php if(!empty($_POST['ddn'])) echo $_POST['ddn']; ?>" required>
                     </div>
                     <div class="form-group">
                        <label for="sesso">Sesso</label><br>
                        <select id="sesso" name="sesso" required>
                           <option value="" selected>-</option>
                           <option value="M">M</option>
                           <option value="F">F</option>
                        </select>
                     </div>
                     <div class="form-group">
                        <label for="email">Email</label><br>
                        <input id="email" name="email" type="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>" required>
                     </div>
                     <div class="form-group">
                        <label for="telefono">Telefono</label><br>
                        <input id="telefono" name="telefono" type="telefono" value="<?php if(!empty($_POST['telefono'])) echo $_POST['telefono']; ?>" maxlength="20" required>
                     </div>

                     <div class="form-group">
                        <input name="submit_2" type="submit" value="Avanti">
                     </div>
                  </form>
               </div>

               <div id="form_3" style="display:none;">
                  <h3>Orario</h3><br>
                  <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
                     <input type="hidden" name="cf" value="<?php if(!empty($_POST['cf'])) echo htmlentities($_POST['cf']); ?>">
                     <div class="form-group">
                        <label for="data">Data</label><br>
                        <input id="data" name="data" type="date" min="<?php echo htmlentities(date("Y-m-d")); ?>" value="<?php if(!empty($_POST['data'])) echo htmlentities($_POST['data']); ?>" required>
                     </div>
                     <div class="form-group">
                        <label for="ora_inizio">Ora inizio</label><br>
                        <input id="ora_inizio" name="ora_inizio" type="time" value="<?php if(!empty($_POST['ora_inizio'])) echo htmlentities($_POST['ora_inizio']); ?>" required>
                     </div>
                     <div class="form-group">
                        <label for="ora_fine">Ora fine</label><br>
                        <input id="ora_fine" name="ora_fine" type="time" value="<?php if(!empty($_POST['ora_fine'])) echo htmlentities($_POST['ora_fine']); ?>" required>
                     </div>

                     <div class="form-group">
                        <input name="submit_3" type="submit" value="Inserisci">
                     </div>
                  </form>
               </div>

            </div>
         </div>
      </div>

   </body>

</html>

<?php

   if(isset($_POST["submit_1"])) {
      gotoForm1();

      if(empty($_POST["cf"])) {
         die("<p class='error'>Alcuni campi non sono stati inseriti</p>");
      }

      if(strlen($_POST["cf"]) != 16) {
         die("<p class='error'>Formato del codice fiscale non valido</p>");
      }

      try {
         $conn = connect();

         // Controlla se il paziente esiste
         $sql = "SELECT COUNT(*) as num
                 FROM pazienti
                 WHERE cf = :cf";
         $stmt = $conn->prepare($sql);
         $stmt->bindParam(":cf", $_POST["cf"], PDO::PARAM_STR, 16);
         $stmt->execute();

         if($stmt->fetch()["num"] == 0) { // Non esiste
            gotoForm2();
            exit;
         }
         else {
            gotoForm3();
            exit;
         }
      } catch (PDOException $e) {
         die("<p class='error'>Si è verificato un errore nel caricamento dei reparti</p>");
      }

   } // if(isset($_POST["submit_1"]))


   if(isset($_POST["submit_2"])) {
      gotoForm2();

      if(empty($_POST["cf"])) {
         gotoForm1();
         exit;
      }
      if(strlen($_POST["cf"]) != 16) {
         gotoForm1();
         exit;
      }

      if(empty($_POST["nome"]) || empty($_POST["cognome"]) || empty($_POST["ddn"]) ||
         empty($_POST["sesso"]) || empty($_POST["email"]) || empty($_POST["telefono"])) {
         die("<p class='error'>Alcuni campi non sono stati inseriti</p>");
      }

      // Controllo email
      if(!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
         die("<p class='error'>La mail inserita non è valida</p>");
      }

      // Controllo data di nascita
      if(strtotime($_POST["ddn"]) > strtotime(date("Y-m-d"))) {
         die("<p class='error'>La data di nascita non è valida</p>");
      }

      try {
         $conn = connect();

         // Controlla se il paziente esiste
         $sql = "SELECT COUNT(*) as num
                 FROM pazienti
                 WHERE cf = :cf";
         $stmt = $conn->prepare($sql);
         $stmt->bindParam(":cf", $_POST["cf"], PDO::PARAM_STR, 16);
         $stmt->execute();

         if($stmt->fetch()["num"] == 0) { // Non esiste
            $sql = "INSERT pazienti (cf, nome, cognome, ddn, sesso, email, telefono)
                    VALUES(:cf, :nome, :cognome, :ddn, :sesso, :email, :telefono)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":cf", $_POST["cf"], PDO::PARAM_STR, 16);
            $stmt->bindParam(":nome", $_POST["nome"], PDO::PARAM_STR, 100);
            $stmt->bindParam(":cognome", $_POST["cognome"], PDO::PARAM_STR, 100);
            $stmt->bindParam(":ddn", $_POST["ddn"]);
            $stmt->bindParam(":sesso", $_POST["sesso"], PDO::PARAM_STR, 1);
            $stmt->bindParam(":email", $_POST["email"], PDO::PARAM_STR, 100);
            $stmt->bindParam(":telefono", $_POST["telefono"], PDO::PARAM_STR, 20);
            $stmt->execute();

            gotoForm3();
            exit;
         }
         else {
            gotoForm3();
            exit;
         }
      } catch (PDOException $e) {
         die("<p class='error'>Si è verificato un errore nel caricamento dei reparti</p>");
      }

   } // if(isset($_POST["submit_2"]))


   if(isset($_POST["submit_3"])) {
      gotoForm3();

      if(empty($_POST["cf"])) {
         gotoForm1();
         exit;
      }
      if(strlen($_POST["cf"]) != 16) {
         gotoForm1();
         exit;
      }

      if(empty($_POST["data"]) || empty($_POST["ora_inizio"]) || empty($_POST["ora_fine"])) {
         die("<p class='error'>Alcuni campi non sono stati inseriti</p>");
      }

      // Controllo data
      if(strtotime($_POST["data"]) < strtotime(date("Y-m-d"))) {
         die("<p class='error'>La data non è valida</p>");
      }

      // Controllo orario
      if(strtotime($_POST["ora_inizio"]) < strtotime($_POST["ora_fine"])) {
         die("<p class='error'>L'orario non è valido</p>");
      }

      try {
         $conn = connect();

         // Controlla se il paziente esiste
         $sql = "SELECT COUNT(*) as num
                 FROM pazienti
                 WHERE cf = :cf";
         $stmt = $conn->prepare($sql);
         $stmt->bindParam(":cf", $_POST["cf"], PDO::PARAM_STR, 16);
         $stmt->execute();

         if($stmt->fetch()["num"] == 0) { // Non esiste
            $sql = "INSERT pazienti (cf, nome, cognome, ddn, sesso, email, telefono)
                    VALUES(:cf, :nome, :cognome, :ddn, :sesso, :email, :telefono)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":cf", $_POST["cf"], PDO::PARAM_STR, 16);
            $stmt->bindParam(":nome", $_POST["nome"], PDO::PARAM_STR, 100);
            $stmt->bindParam(":cognome", $_POST["cognome"], PDO::PARAM_STR, 100);
            $stmt->bindParam(":ddn", $_POST["ddn"]);
            $stmt->bindParam(":sesso", $_POST["sesso"], PDO::PARAM_STR, 1);
            $stmt->bindParam(":email", $_POST["email"], PDO::PARAM_STR, 100);
            $stmt->bindParam(":telefono", $_POST["telefono"], PDO::PARAM_STR, 20);
            $stmt->execute();


         }
         else {

         }
      } catch (PDOException $e) {
         die("<p class='error'>Si è verificato un errore nel caricamento dei reparti</p>");
      }

   } // if(isset($_POST["submit_3"]))


?>
