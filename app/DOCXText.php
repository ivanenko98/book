<?php
/**
 * Created by PhpStorm.
 * User: westham
 * Date: 13.12.2017
 * Time: 16:37
 */

namespace App;

use DOMDocument;
use ZipArchive;

class DOCXText
{
    function readDocx($filePath) {

        $zip = new ZipArchive;
        $dom = new DOMDocument;
        $dataFile = 'word/document.xml';
        // Open received archive file
        if (true === $zip->open($filePath)) {
            // If done, search for the data file in the archive
            if (($index = $zip->locateName($dataFile)) !== false) {
                // If found, read it to the string
                $data = $zip->getFromIndex($index);
                // Close archive file
                $zip->close();
                // Load XML from a string
                // Skip errors and warnings
                $dom->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                // Return data without XML formatting tags

                $contents = explode('\n',strip_tags($dom->saveXML()));
                $text = '';
                foreach($contents as $i=>$content) {
                    $text .= $contents[$i];
                }
                return $text;
            }
            $zip->close();
        }
        // In case of failure return empty string
        return "";
    }
}