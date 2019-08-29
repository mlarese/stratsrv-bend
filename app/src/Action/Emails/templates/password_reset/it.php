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
                        Buongiorno  <?=$d['user']?><br>

                    </td>
                </tr><?=$spacer?>

                <tr>
                    <td colspan="100">
                        per reimpostare la  password clicca su questo <a href="<?=$d['link']?>">link</a>
                        <br>
                        Il team di DataOne

                    </td>
                </tr><?=$spacer?>

            </table>
        </div>
    </body>
</html>
