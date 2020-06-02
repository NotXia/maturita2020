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

   if(empty($_POST["id"])) {
      header("Location: index.php");
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

      <title>Modifica dottore</title>
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

      <?php
         try {
            $conn = connect();

            $conn->beginTransaction();

            // Aggiornamento anagrafica medico
            $sql = "UPDATE medici
                    SET nome = :nome, cognome = :cognome, cod_reparto = :cod_reparto
                    WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":nome", $_POST["nome"], PDO::PARAM_STR, 100);
            $stmt->bindParam(":cognome", $_POST["cognome"], PDO::PARAM_STR, 100);
            $stmt->bindParam(":cod_reparto", $_POST["reparto"], PDO::PARAM_INT);
            $stmt->bindParam(":id", $_POST["id"], PDO::PARAM_INT);
            $stmt->execute();

            // Estrazione utenza medico
            $sql = "SELECT cod_utenza
                    FROM medici
                    WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":id", $_POST["id"], PDO::PARAM_INT);
            $stmt->execute();

            $id_utenza = $stmt->fetch()["cod_utenza"];

            // Aggiornamento utenza medico
            if(empty($_POST["password"])) {
               $sql = "UPDATE utenze
                       SET usr = :username
                       WHERE id = :id";
               $stmt = $conn->prepare($sql);
               $stmt->bindParam(":username", $_POST["username"], PDO::PARAM_STR, 100);
               $stmt->bindParam(":id", $id_utenza, PDO::PARAM_INT);
               $stmt->execute();
            }
            else {
               $psw_hash =  password_hash($_POST["password"], PASSWORD_DEFAULT);
               $sql = "UPDATE utenze
                       SET usr = :username, psw = :password
                       WHERE id = :id";
               $stmt = $conn->prepare($sql);
               $stmt->bindParam(":username", $_POST["username"], PDO::PARAM_STR, 100);
               $stmt->bindParam(":password", $psw_hash, PDO::PARAM_STR, 60);
               $stmt->bindParam(":id", $id_utenza, PDO::PARAM_INT);
               $stmt->execute();
            }

            $conn->commit();

            ?>
               <script type="text/javascript">
                  window.history.back();
               </script>
            <?php
         } catch (PDOException $e) {
            $conn->rollBack();
            die("<p class='error'>Si è verificato un errore nell'aggiornamento</p>");
         }
      ?>
   </body>

</html>
