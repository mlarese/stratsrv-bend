<?php

//echo '<pre>'; print_r($data); die;
    if(  is_array($data['created'])  ) {
        $date = date_create($data['created']['date']);
    } else {

        if(is_string($data['created']))
            $date = date_create($data['created']);
        else
            $date = $data['created'];
    }

    $dtcreate = date_format($date,"d/m/Y");
    $tmcreate = date_format($date,"H:i");

    $flparagraphs = $data['privacy']['paragraphs'];
    $flags = '';

    if(!isset($flparagraphs)) $flparagraphs=[];
    foreach ($flparagraphs as $pg) {
        foreach ($pg['treatments'] as $t) {
            $checked = $t['selected']?'checked="true"':'';

            $flags.='<div>'.
                        '<input disabled="disabled" type="checkbox" ' . $checked . '>'.
                        $t['code'].
                    '</div>';
        }
    }
?>

<tr>
    <td style="<?=$st_ba?>"><?=$dtcreate?></td>
    <td style="<?=$st_ba?>"><?=$tmcreate?></td>
    <td style="<?=$st_ba?>"><?=$flags?></td>
    <td style="<?=$st_ba?>"><?=$data['page']?></td>
    <td style="<?=$st_ba?>"><?=$data['ip']?></td>
</tr>
