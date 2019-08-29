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
                        Gentile <?=$d['name']?>  <?=$d['surname']?>
                    </td>
                </tr><?=$spacer?>

                <tr>
                    <td colspan="100">
                        il giorno <?=$d['createdDate']?> alle ore <?=$d['createdTime']?>
                        hai richiesto i <b>dettagli delle sottoscrizioni delle Normative Privacy relative al dominio <?=$d['domain']?></b>, eccoli:
                    </td>
                </tr><?=$spacer?>

                <?php
                    $cntt = 0;
                    foreach($d['privacies'] as $tid=> $prs) {
                        $cntp = 0;
                        foreach($prs as $rowdomain=> $data) {
                            if($data["domain"] !== $d["domain"]) {
                                continue;
                            }
                           if($cntp === 0) include ("header_$clang.php");
                           include ("prrow.php"); $cntp++;
                        }
                        echo $spacer; $cntt++;
                    }
                ?>
                <tr><td colspan="4">
                    Responsabile del trattamento dei dati <?=$d['resp']?>
                </td></tr>
                <tr><td colspan="4"><b>
                    se  desidera  consultare i dettagli di compilazione o modificare le sottoscrizioni alle Normative
                    o revocarle <a href="<?=$d['link']?>">clicchi qui</a>
                </b></td></tr>

            </table>
        </div>
    </body>
</html>
