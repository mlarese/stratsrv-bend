<?php include 'styles.php'; ?>
<?php $clang='it'; ?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <title></title>
        <style>

        </style>
    </head>
    <body>
        <div style="<?=$containerStyle?>" >
            <table style="<?=$tableStyle?>">
                <tr>
                    <td colspan="100">
                        Buongiorno  <?=$d['name']?>  <?=$d['surname']?>,
                    </td>
                </tr><?=$spacer?>


                <?php
                $cntt = 0;
                foreach($d['privacies'] as $tid=> $prs) {
                    $cntp = 0;
                    foreach($prs as $domain=> $data) {
                        if($cntp === 0) include ("header_$clang.php");
                        include ("prrow.php"); $cntp++;
                    }
                    echo $spacer; $cntt++;
                }
                ?>



                <tr>
                    <td colspan="100">
                        abbiamo ricevuto le tue preferenze sul trattamento dei dati raccolti attraverso il servizio DataOne.
                        Per proteggerli al meglio, conferma la tua accettazione cliccando su questo <a href="<?=$d['enclink']?>">link</a>
                        <br>
                        Grazie,
                        Il team di DataOne

                    </td>
                </tr><?=$spacer?>

            </table>
        </div>
    </body>
</html>
