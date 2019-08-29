<?php include 'styles.php'; ?>
<?php $clang='en'; ?>
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
                        Dear <?=$d['name']?>  <?=$d['surname']?>
                    </td>
                </tr><?=$spacer?>

                <tr>
                    <td colspan="100">
                        On  <?=$d['createdDate']?> at <?=$d['createdTime']?>
                        you requested further <b>details of the subscription of the Privacy Regulations relating to the domain <?=$d['domain']?></b>,
                        here they are:
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
                        Responsible for data processing <?=$d['resp']?>
                </td></tr>
                <tr><td colspan="4"><b>
                    If you want to examine the compilation details or modify the subscriptions to the Regulations or revoke them
                            <a href="<?=$d['link']?>">Click here</a>
                </b></td></tr>

            </table>
        </div>
    </body>
</html>
