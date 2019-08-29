<?php include 'styles.php'; ?>
<?php $clang='de'; ?>
<!DOCTYPE html>
<html lang="en">
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
                        Sehr geehrte/r <?=$d['name']?>  <?=$d['surname']?>
                    </td>
                </tr><?=$spacer?>

                <tr>
                    <td colspan="100">
                        Am  <?=$d['createdDate']?>  <?=$d['createdTime']?>
                        haben Sie für die Domain <?=$d['domain']?> die Details der unterzeichneten Datenschutzbestimmungen verlangt,
                        die wir Ihnen hier vorlegen:
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
                <tr><td colspan="4">
                        Verantwortlicher der Datenschutzbestimmungen <?=$d['resp']?>
                </td></tr>
                <tr><td colspan="4"><b>
                  falls Sie die Details der ausgefüllten Daten einsehen, die Unterzeichnung der Bestimmungen ändern oder widerrufen möchten
                            <a href="<?=$d['link']?>">klicken Sie bitte hier</a>
                </b></td></tr>

            </table>
        </div>
    </body>
</html>
