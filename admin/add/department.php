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

      <title>Inserimento reparto</title>
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
               <h1 class="display-4">Inserimento reparto</h1><br>

               <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
                  <div class="form-group">
                     <label for="denominazione">Denominazione</label><br>
                     <input id="denominazione" name="denom" type="text" value="<?php if(!empty($_POST['denom'])) echo $_POST['denom']; ?>" maxlength="100" required>
                  </div>

                  <div class="form-group">
                     <label>Posti</label><br>
                     <div id="posti">
                     </div>
                     <button type="button" class="btn btn-light" name="button" onclick="addRow()">Aggiungi</button><br><br>
                  </div>

                  <div class="form-group">
                     <input name="submit" class="btn btn-secondary" type="submit" value="Inserisci">
                  </div>
               </form>

               <?php
                  if(isset($_POST["submit"])) {

                     // Verifica campi obbligatori
                     if(empty($_POST["denom"])) {
                        die("<p class='error'>Alcuni campi non sono stati inseriti</p>");
                     }

                     try {
                        $conn = connect();

                        $denominazione = trim($_POST["denom"]);

                        $conn->beginTransaction();

                        $sql = "INSERT reparti (denominazione) VALUES(:denominazione)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(":denominazione", $denominazione, PDO::PARAM_STR, 100);
                        $stmt->execute();

                        $id_reparto = $conn->lastInsertId();

                        foreach($_POST["posti"] as $i=>$nome_posto) {
                           $nome_posto = trim($nome_posto);

                           $sql = "INSERT posti (nome, cod_reparto) VALUES(:nome, :id_reparto)";
                           $stmt = $conn->prepare($sql);
                           $stmt->bindParam(":nome", $nome_posto, PDO::PARAM_STR, 100);
                           $stmt->bindParam(":id_reparto", $id_reparto, PDO::PARAM_INT);
                           $stmt->execute();
                        }

                        $conn->commit();

                        header("Location: index.php");
                     } catch (PDOException $e) {
                        $conn->rollBack();
                        die("<p class='error'>Qualcosa non ha funzionato</p>");
                     }

                  }
               ?>

            </div>
         </div>
      </div>
   </body>

   <script type="text/javascript">
      function addRow() {
         var row = document.createElement("input");
         row.type = "text";
         row.name = "posti[]";
         row.placeholder = "Nome";

         document.getElementById("posti").appendChild(row);
         document.getElementById("posti").appendChild(document.createElement("br"));
         document.getElementById("posti").appendChild(document.createElement("br"));
      }
   </script>

</html>
