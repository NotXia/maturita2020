<?php
   ob_start();
   require_once(dirname(__FILE__)."/../../utilities/login_check.php");
   if(!logged()) {
      header("Location: ../login.php");
      exit;
   }
   else if(!adminLogged()) {
      header("Location: ../../index.php");
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
      <link rel="stylesheet" href="../../css/styles.css">
      <link rel="stylesheet" href="../../css/navbar.css">
      <script src="../../js/jquery.min.js"></script>
      <script src="../../js/popper.min.js"></script>
      <script src="../../js/bootstrap.min.js"></script>

      <title>Inserimento dottore</title>

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
               <img class="navbar-brand admin_nav_logo" src="../../img/wrench.png">
               Admin
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
               <li class="nav-item active">
                  <a class="nav-link" href="./">Inserimento</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="../view">Visualizzazione</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="../logout.php">Logout</a>
               </li>
            </ul>
         </div>
      </nav>

      <div class="container">
         <div class="row text-black">
            <div class="col-xl-8 col-lg-8 col-md-8 col-sm-10 mx-auto text-center p-4">
               <h1 class="display-4">Inserimento dottore</h1><br>

               <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
                  <div class="form-group">
                     <label for="nome">Nome</label><br>
                     <input id="nome" name="nome" type="text" value="<?php if(!empty($_POST['nome'])) echo htmlentities($_POST['nome']); ?>" maxlength="100" required>
                  </div>

                  <div class="form-group">
                     <label for="cognome">Cognome</label><br>
                     <input id="cognome" name="cognome" type="text" value="<?php if(!empty($_POST['cognome'])) echo htmlentities($_POST['cognome']); ?>" maxlength="100" required>
                  </div>

                  <div class="form-group">
                     <label for="username">Username</label><br>
                     <input id="username" name="username" type="text" value="<?php if(!empty($_POST['username'])) echo htmlentities($_POST['username']); ?>" maxlength="100" required>
                  </div>

                  <div class="form-group">
                     <label for="password">Password</label><br>
                     <input id="password" name="password" type="password" required>
                  </div>

                  <div class="form-group options">
                     <select class="custom-select" style="width:auto;" name="reparto" required>
                        <option value="" selected>Reparto</option>
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
                                 echo "<option value='$id'>$denom</option>";
                              }
                           } catch (PDOException $e) {
                              $conn = null;
                              die("<p class='error'>Si è verificato un errore nel caricamento dei reparti</p>");
                           }
                           $conn = null;
                        ?>
                     </select>
                  </div>

                  <div class="form-group">
                     <input name="submit" class="btn btn-secondary" type="submit" value="Inserisci">
                  </div>
               </form>

               <?php
                  if(isset($_POST["submit"])) {

                     // Verifica campi obbligatori
                     if(empty($_POST["nome"]) || empty($_POST["cognome"]) || empty($_POST["username"]) || empty($_POST["password"]) || empty($_POST["reparto"])) {
                        die("<p class='error'>Alcuni campi non sono stati inseriti</p>");
                     }

                     $psw_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

                     try {
                        $conn = connect();

                        // Verifica se l'utenza è già esistente
                        $sql = "SELECT COUNT(*) AS tot_usr
                                FROM utenze
                                WHERE usr = :username";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(":username", $_POST["username"], PDO::PARAM_STR, 100);
                        $stmt->execute();
                        if($stmt->fetch()["tot_usr"] != 0) {
                           die("<p class='error'>Username già in uso</p>");
                        }
                     } catch (PDOException $e) {
                        die("<p class='error'>Qualcosa non ha funzionato</p>");
                     }

                     try {
                        $conn->beginTransaction();

                        // Inserimento utenza
                        $sql = "INSERT utenze (usr, psw, admin) VALUES(:username, :password, 0)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(":username", $_POST["username"], PDO::PARAM_STR, 100);
                        $stmt->bindParam(":password", $psw_hash, PDO::PARAM_STR, 60);
                        $stmt->execute();

                        $id_utenza = $conn->lastInsertId();

                        // Inserimento dottore
                        $sql = "INSERT medici (nome, cognome, cod_utenza, cod_reparto) VALUES(:nome, :cognome, :cod_utenza, :cod_reparto)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(":nome", trim($_POST["nome"]), PDO::PARAM_STR, 100);
                        $stmt->bindParam(":cognome", trim($_POST["cognome"]), PDO::PARAM_STR, 100);
                        $stmt->bindParam(":cod_utenza", $id_utenza, PDO::PARAM_INT);
                        $stmt->bindParam(":cod_reparto", $_POST["reparto"], PDO::PARAM_INT);
                        $stmt->execute();

                        $conn->commit();

                        header("Location: index.php");
                     } catch (PDOException $e) {
                        $conn->rollBack();
                        die("<p class='error'>Non è stato possibile inserire il dottore</p>");
                     }

                  }
               ?>

            </div>
         </div>
      </div>

   </body>

</html>
