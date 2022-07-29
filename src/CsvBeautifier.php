<?php

namespace CsvBeautifier;

require('vendor/autoload.php');

use DateTime;
use Pear\Console_Table;
use Service\Slugify;

class CsvBeautifier {

    // Récupère le fichier
    static function getFile(string $argv)
    {
        return file_get_contents($argv);
    }

    static function getLines(string $csv)
    {
        // On met chaque ligne dans un tableau
        return preg_split('/\r\n|\r|\n/', $csv);
    }

    static function getColumns(array $lines) 
    {
        // Nom des colonnes
        $columns = explode(';', $lines[0]);
        // Ajout de la colonne slug
        $columns[7] = 'slug';

        return $columns;
    }

    static function format(array $lines)
    {
        // Récupération de toutes les lignes de données sous forme de chaîne
        for($i = 1; $i < count($lines); $i++) {
            $dataStrings[] = $lines[$i];
        }

        // Scindage des chaînes de données en segments
        foreach($dataStrings as $string) {
            $dataArray[] = explode(';', $string);
        }

        // Formatages
        foreach($dataArray as &$data) {
            // De la date
            $date = new DateTime($data[6]);
            $data[6] = $date->format('l, d-M-Y H:i:s T');

            // De la description (il me semble qu'il y a une faute de frappe sur le br donné)
            $data[5] = str_replace(['\r', '<br/>', '\n', '<br>'], PHP_EOL, $data[5]);

            // Du prix
            $price = number_format(round($data[3], 1), 2, ',', '.');
            $data[3] = $price . '€';

            // Colonne slug
            $slug = new Slugify;
            $data[7] = $slug->generate($data[1]);

            // Du statut
            $data[2] = $data[2] ? "Enable" : "Disable";
        }

        return $dataArray;

    }

    public function createTable(string $argv1, ?string $argv2 = null) 
    {
        $file = self::getFile($argv1);
        $lines = self::getLines($file);
        $columns = self::getColumns($lines);
        $dataArray = self::format($lines);

        // Construction de la table à afficher
        $tbl = new Console_Table();
        $tbl->setHeaders($columns);
        foreach($dataArray as $d) {
            $tbl->addRow($d);
        }

        // JSON
        for ($i=0; $i<count($dataArray); $i++) {
            $jsonArray[] = array_combine($columns, array_values($dataArray[$i]));
        }

        if (isset($argv2) && $argv2 === "--json") {
            echo json_encode($jsonArray, JSON_PRETTY_PRINT);
        } else {
            echo $tbl->getTable();
        }
    }
}