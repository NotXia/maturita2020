<?php
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

      <title>Inserimento admin</title>
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
               <h1 class="display-4">Inserimento admin</h1><br>

               <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
                  <div class="form-group">
                     <label for="username">Username</label><br>
                     <input id="username" name="username" type="text" value="<?php if(!empty($_POST['username'])) echo $_POST['username']; ?>" maxlength="100" required>
                  </div>

                  <div class="form-group">
                     <label for="password">Password</label><br>
                     <input id="password" name="password" type="password" required>
                  </div>

                  <div class="form-group">
                     <input name="submit" type="submit" value="Inserisci">
                  </div>
               </form>

               <?php
                  if(isset($_POST["submit"])) {

                     // Verifica campi obbligatori
                     if(empty($_POST["username"]) || empty($_POST["password"])) {
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

                        $sql = "INSERT utenze (usr, psw, admin) VALUES(:username, :password, 1)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(":username", $_POST["username"], PDO::PARAM_STR, 100);
                        $stmt->bindParam(":password", $psw_hash, PDO::PARAM_STR, 60);
                        $stmt->execute();

                        header("Location: index.php");

                     } catch (PDOException $e) {
                        die("<p class='error'>Qualcosa non ha funzionato</p>");
                     }

                  }
               ?>

            </div>
         </div>
      </div>
   </body>

</html>
