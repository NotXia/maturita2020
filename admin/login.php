<?php
   session_start();

   require_once(dirname(__FILE__)."/../utilities/login_check.php");
   if(adminLogged()) {
      header("Location: index.php");
      exit;
   }

   require_once(dirname(__FILE__)."/../utilities/database.php");
?>

<!DOCTYPE html>
<html>

   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link rel="stylesheet" href="../css/bootstrap.min.css">
      <link rel="stylesheet" href="../css/styles.css">

      <title>Admin login</title>

      <style>
         html, body {
            height: 100%;
         }

         .container {
            height: 100%;
            align-content: center;
         }

         .card {
            margin-top: auto;
            margin-bottom: auto;
            background-color: rgba(0,0,0,0.3) !important;
            padding: 30px;
         }
      </style>
   </head>

   <body class="text-center">

      <div class="container">
         <div class="d-flex justify-content-center h-100">
            <div class="col-xl-3 col-lg-4 col-md-5 col-sm-5 d-flex justify-content-center card">
               <h3>ADMIN</h3><br>

               <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
                  <div class="form-group">
                     <input name="username" type="text" class="form-control" value="<?php if(!empty($_POST['username'])) echo $_POST['username']; ?>" placeholder="username">
                  </div>

                  <div class="form-group">
                     <input name="password" type="password" class="form-control" placeholder="password">
                  </div>

                  <input name="submit" type="submit" value="Accedi" class="btn btn-light">

               </form>

               <?php
                  if(isset($_POST["submit"])) {

                     // Verifica campi obbligatori
                     if(empty($_POST["username"]) || empty($_POST["password"])) {
                        die("<p class='error'>Alcuni campi non sono stati inseriti</p>");
                     }

                     try {

                        $conn = connect();

                        // Estrae i dati dell'utente
                        $sql = "SELECT id, usr, psw
                                FROM utenze
                                WHERE usr = :username AND
                                      admin = 1";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(":username", $_POST["username"], PDO::PARAM_STR, 100);
                        $stmt->execute();
                        $res = $stmt->fetch();

                        if(empty($res)) {
                           die("<br><span class='error'>Utenza non esistente</span>");
                        }

                        if(password_verify($_POST["password"], $res["psw"])) {
                           $_SESSION["id"] = $res["id"];
                           $_SESSION["username"] = $res["usr"];
                           $_SESSION["admin"] = 1;

                           header("Location: index.php");
                        }
                        else {
                           die("<br><span class='error'>Credenziali errate</span>");
                        }

                     } catch (PDOException $e) {
                        die("<br><span class='error'>Qualcosa non ha funzionato</span>");
                     }

                  }
               ?>

            </div>
         </div>
      </div>

   </body>

</html>
