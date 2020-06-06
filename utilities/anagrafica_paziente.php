<?php
   require_once(dirname(__FILE__)."/database.php");


   /*
      type = 0 -> Ricerca per ricovero
             1 -> Ricerca per visita
   */
   function anagrafica($id, $type) {
      ?>
      <div class="border border-secondary rounded p-3">
         <h5>Dati paziente</h5>
         <div class="table-responsive" >
            <table align="center">
      <?php
            try {
               $conn = connect();

               $sql = "";

               switch ($type) {
                  case 0:
                     $sql = "SELECT pazienti.nome AS nome_paziente, pazienti.cognome AS cognome_paziente, ddn, sesso, email, telefono,
                                    data_inizio, motivo, posti.nome AS nome_posto,
                                    medici.nome AS nome_medico, medici.cognome AS cognome_medico
                             FROM pazienti, ricoveri, posti, medici
                             WHERE cod_paziente = pazienti.cf AND
                                   cod_posto = posti.id AND
                                   cod_medico = medici.id AND
                                   ricoveri.id = :id";
                     break;

                  case 1:
                     $sql = "SELECT pazienti.nome AS nome_paziente, pazienti.cognome AS cognome_paziente, ddn, sesso, email, telefono,
                                    data_inizio, motivo, posti.nome AS nome_posto,
                                    medici.nome AS nome_medico, medici.cognome AS cognome_medico
                             FROM pazienti, ricoveri, posti, medici, visite
                             WHERE cod_paziente = pazienti.cf AND
                                   cod_posto = posti.id AND
                                   ricoveri.cod_medico = medici.id AND
                                   cod_ricovero = ricoveri.id AND
                                   visite.id = :id";
                     break;
               }

               $stmt = $conn->prepare($sql);
               $stmt->bindParam(":id", $id, PDO::PARAM_INT);
               $stmt->execute();
               $res = $stmt->fetch();

               if(!empty($res)) {
                  $nome = htmlentities($res["nome_paziente"]);
                  $cognome = htmlentities($res["cognome_paziente"]);
                  $ddn = date("d/m/Y", strtotime($res["ddn"]));
                  $sesso = htmlentities($res["sesso"]);
                  $email = htmlentities($res["email"]);
                  $telefono = htmlentities($res["telefono"]);
                  $data_inizio = date("d/m/Y H:i", strtotime($res["data_inizio"]));
                  $nominaivo_medico = htmlentities($res["cognome_medico"] . " " . $res["nome_medico"]);
                  $posto = htmlentities($res["nome_posto"]);
                  $motivo = htmlentities($res["motivo"]);

                  echo "<tr>
                           <td class='anagrafica'><b>Nome</b><br>$nome</td>
                           <td class='anagrafica'><b>Cognome</b><br>$cognome</td>
                           <td class='anagrafica'><b>Sesso</b><br>$sesso</td>
                        </tr>
                        <tr>
                           <td class='anagrafica'><b>Data di nascita</b><br>$ddn</td>
                           <td class='anagrafica'><b>Email</b><br>$email</td>
                           <td class='anagrafica'><b>Telefono</b><br>$telefono</td>
                        </tr>
                        <tr>
                           <td class='anagrafica'><b>Data ricovero</b><br>$data_inizio</td>
                           <td class='anagrafica'><b>Stanza</b><br>$posto</td>
                           <td class='anagrafica'><b>Medico</b><br>$nominaivo_medico</td>
                        </tr>
                        <tr>
                           <td colspan='3'><b>Motivo</b><br>$motivo</td>
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
      <?php
   }

?>
