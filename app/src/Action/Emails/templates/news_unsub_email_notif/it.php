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

                <tr>
                    <td colspan="100">
                        Può aggiornare i suoi consensi  cliccando su questo
                        <a href="<?=$d['enclink']?>">link</a>
                        <br>
                        Grazie,
                        Il team di DataOne

                    </td>
                </tr><?=$spacer?>

            </table>
        </div>
    </body>
</html>
