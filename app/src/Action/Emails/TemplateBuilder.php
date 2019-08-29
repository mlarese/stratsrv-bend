<?php
/**
 * Created by PhpStorm.
 * User: mauro.larese
 * Date: 06/07/2018
 * Time: 12:06
 */

namespace App\Action\Emails;


use function file_exists;

class TemplateBuilder {
    private $templateName;
    private $data;
    private $language;

    /**
     * TemplateBuilder constructor.
     *
     * @param $templateName
     * @param $data
     * @param $labels
     */
    public function __construct($templateName, $data, $language) {
        $this->configureTemplateName($templateName,$language);
        $this->data = $data;
        $this->language = $language;
    }

    private function configureTemplateName($templateName,$language) {
        $tmpTpl = "templates/$templateName/${language}.php";
        $tmpTplComplete = dirname(__FILE__)."/templates/$templateName/${language}.php";

        if(!file_exists($tmpTplComplete))
            $tmpTpl = "templates/$templateName/en.php";
        if(!file_exists($tmpTplComplete))
            $tmpTpl = "templates/$templateName/it.php";

        $this->templateName = $tmpTpl;
    }
    public function render () {
        $d = $this->data;

        ob_start();
        require($this->templateName);
        $buffer = ob_get_contents();
        @ob_end_clean();
        return $buffer;
    }

}
