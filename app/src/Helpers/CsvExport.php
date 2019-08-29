<?php
namespace  App\Helpers;
use App\Resource\ExportError;
/**
 * Created by PhpStorm.
 * User: giuseppe.donato
 * Date: 01/05/18
 * Time: 17.34
 */

Class CsvExport {

    public static function export(
        $header = array(),
        $data = array()
    ) {

        if (!is_array($header) || count($header) == 0) {
            throw new ExportError(sprintf(
                'The header values to export is null or empty'
            ));
        }
        if (!is_array($data) || count($data) == 0) {
            throw new ExportError(sprintf(
                'The data values to export is null or empty'
            ));
        }



        // Add header
        $data = array_merge(array($header), $data);


        $docLine = '';
        // Print data
        foreach ($data as $datum) {

            foreach ($datum as $i => $value) {
                if (strpos($value, '"') !== false) {
                    $matches = array();
                    preg_match_all('/[\"]{1}(.[^\"]+)[\"]{1}/i', $value, $matches);
                    foreach ($matches[1] as $match) {
                        $value = str_replace(
                            '"' . $match . '"',
                            '“' . $match . '”',
                            $value
                        );
                    }
                    $datum[$i] = $value;
                }
                $datum[$i] = str_replace(array("\n\r", "\n","\r"), ' ', $datum[$i]);
            }

            $docLine.=          implode(",", array_map(function($val){return "\"$val\"";}, $datum))."\r\n";
        }

        return $docLine;
    }


}