<?php

namespace App\Services;


use App\Exceptions\InvalidCsvStringException;

class CsvParser
{
    /**
     * Accepts CSV string and outputs array of data
     *
     * @param string $csv
     * @return array
     * @throws InvalidCsvStringException
     */
    public function parse(string $csv)
    {
        $csv = preg_split("/\r\n|\n|\r/", $csv);
        $csv = array_filter($csv);
        if (!$csv) {
            throw new InvalidCsvStringException('Please provide correct csv data');
        }
        foreach ($csv as $line) {
            $data[] = str_getcsv($line);
        }
        return array_filter($data);
    }

}
