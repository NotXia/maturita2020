<?php
   require_once(dirname(__FILE__)."/utilities/login_check.php");
   if(logged()) {
      header("Location: index.php");
      exit;
   }

   require_once(dirname(__FILE__)."/utilities/database.php");
?>

<!DOCTYPE html>
<html>

   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link rel="stylesheet" href="./css/bootstrap.min.css">
      <link rel="stylesheet" href="./css/styles.css">
      <script src="./js/jquery.min.js"></script>
      <script src="./js/popper.min.js"></script>
      <script src="./js/bootstrap.min.js"></script>

      <title>Login</title>

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
               <h3>LOGIN</h3><br>

               <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST" required>

                  <div class="form-group">
                     <input name="username" type="text" class="form-control" placeholder="username" required>
                  </div>

                  <div class="form-group">
                     <input name="password" type="password" class="form-control" placeholder="password" required>
                  </div>

                  <input name="submit" type="submit" value="Accedi" class="btn btn-light">
               </form>
               <br>

               <?php
                  if(isset($_POST["submit"])) {

                     // Verifica campi obbligatori
                     if(empty($_POST["username"]) || empty($_POST["password"])) {
                        die("<p class='error'>Alcuni campi non sono stati inseriti</p>");
                     }

                     try {

                        $conn = connect();

                        // Estrae i dati del medico e della sua utenza
                        $sql = "SELECT medici.id, usr, psw, nome, cognome
                                FROM utenze, medici
                                WHERE cod_utenza = utenze.id AND
                                      usr = :username";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(":username", $_POST["username"], PDO::PARAM_STR, 100);
                        $stmt->execute();
                        $res = $stmt->fetch();

                        if(empty($res)) {
                           die("<span class='error'>Utenza non esistente</span>");
                        }

                        if(password_verify($_POST["password"], $res["psw"])) {
                           $_SESSION["id"] = $res["id"];
                           $_SESSION["username"] = $res["usr"];
                           $_SESSION["nome"] = $res["nome"];
                           $_SESSION["cognome"] = $res["cognome"];
                           $_SESSION["admin"] = 0;

                           // Estrazione reparti appartenenti al medico
                           $sql = "SELECT cod_reparto
                                   FROM specializzazioni
                                   WHERE cod_medico = :id_medico";
                           $stmt = $conn->prepare($sql);
                           $stmt->bindParam(":id_medico", $_SESSION["id"], PDO::PARAM_INT);
                           $stmt->execute();
                           $res = $stmt->fetchAll();

                           $reparti = array();
                           foreach($res as $row) {
                              $reparti[] = $row["cod_reparto"];
                           }
                           $_SESSION["reparti"] = $reparti;

                           header("Location: select.php");
                        }
                        else {
                           die("<span class='error'>Credenziali errate</span>");
                        }

                     } catch (PDOException $e) {
                        die("<span class='error'>Qualcosa non ha funzionato</span>");
                     }

                  }
               ?>

            </div>
         </div>
      </div>

   </body>

</html>
