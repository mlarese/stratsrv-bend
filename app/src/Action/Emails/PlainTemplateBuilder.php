<?php
/**
 * Created by PhpStorm.
 * User: mauro.larese
 * Date: 23/07/2018
 * Time: 14:46
 */

namespace App\Action\Emails;

use function dirname;
use Exception;
use function fclose;
use function file_exists;
use function fwrite;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function str_replace;
use function stream_get_meta_data;
use function tmpfile;

class PlainTemplateBuilder {
    protected $templateName;

    /**
     * @return mixed
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * @param mixed $templateName
     * @return PlainTemplateBuilder
     */
    public function setTemplateName($templateName)
    {
        $this->templateName = $templateName;
        return $this;
    }


    /**
     * @param $data
     * @param $language
     *
     * @return string
     * @throws Exception
     */
    public function render ($data, $language) {
        if(!isset($this->templateName)) {
            throw new Exception('Templete name not setted');
        }
        $templateName = $this->templateName;

        $tmpTpl = "templates/$templateName/${language}.php";
        $tmpTplComplete = dirname(__FILE__)."/$tmpTpl";

        if(!file_exists($tmpTplComplete))
            $tmpTpl = "templates/$templateName/en.php";

        $tmpTplComplete = dirname(__FILE__)."/$tmpTpl";
        if(!file_exists($tmpTplComplete))
            $tmpTpl = "templates/$templateName/it.php";

        $d = $data;

        ob_start();
        require($tmpTpl);
        $buffer = ob_get_contents();
        @ob_end_clean();
        return $buffer;
    }

    public static function evalTemplate($htmlTemplate, $d) {
        $search  = [
            '%BTNCONFIRM%',
            '%STRUCTURE%',
            '%NOME%',
            '%COGNOME%',
            '%LOGO%',
            'images/LOGO.jpg'
        ];
        $replace = [
            '<?=$d["enclink"]?>',
            '<?=$d["structure"]?>',
            '<?=$d["name"]?>',
            '<?=$d["surname"]?>',
            '<?=$d["logo"]?>',
            '<?=$d["logo"]?>'
        ];

        $htmlTemplate = str_replace($search, $replace, $htmlTemplate);
        $tmp = tmpfile ();
        $tmpf = stream_get_meta_data ( $tmp );
        $tmpf = $tmpf ['uri'];
        fwrite ( $tmp, $htmlTemplate );
        ob_start();
        $ret = include ($tmpf);
        $buffer = ob_get_contents();
        @ob_end_clean();
        fclose ( $tmp );
        return $buffer;
    }
    /**
     * @param $data
     * @param $language
     *
     * @return string
     * @throws Exception
     */
    public function renderHtml ($data, $html) {
        $buffer = self::evalTemplate($html, $data);
        return $buffer;
    }
}
