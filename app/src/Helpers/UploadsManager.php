<?php
/**
 * Created by PhpStorm.
 * User: mauro.larese
 * Date: 14/06/2018
 * Time: 15:32
 */

namespace App\Helpers;


use function json_decode;
use Slim\Http\UploadedFile;

class UploadsManager {
    private $file;

    /**
     * UploadsManager constructor.
     *
     * @param $file UploadedFile
     */
    public function __construct($fileName) {
        /** @var UploadedFile files */
        $this->file =  file($fileName);
    }

    /**
     * @return array
     */
    public function getCsv () {
        $data = array_map('str_getcsv', $this->file);

        $result = [];
        $header = $data[0];

        for($i=1; $i<count($data); $i++) {
            $result[] = array_combine($data[0], $data[$i]);
        }

        return $result;
    }

    public function getJson () {
        $data = json_decode($this->file, true);
        return $data;
    }
}
