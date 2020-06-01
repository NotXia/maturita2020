<?php
   require_once(dirname(__FILE__)."/utilities/login_check.php");
   if(logged()) {
      header("Location: index.php");
      exit;
   }
?>

<!DOCTYPE html>
<html>

   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link rel="stylesheet" href="./css/bootstrap.min.css">

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

               <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">

                  <div class="form-group">
                     <input name="username" type="text" class="form-control" placeholder="username">
                  </div>

                  <div class="form-group">
                     <input name="password" type="password" class="form-control" placeholder="password">
                  </div>

                  <input name="submit" type="submit" value="Accedi" class="btn btn-light">
               </form>

            </div>
         </div>
      </div>

   </body>

</html>
