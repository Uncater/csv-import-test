<?php

namespace Tests\Feature;

use App\Exceptions\InvalidCsvStringException;
use App\Services\CsvParser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CsvParserTest extends TestCase
{
    /** @test */
    public function parser_should_process_csv_file_contents_to_array()
    {
        $randomCsv = $this->getRandomCsv();
        $parser = new CsvParser();

        $data = $parser->parse($randomCsv);
        $this->assertTrue(is_array($data));
    }

    /** @test */
    public function parser_should_process_commas_inside_cells()
    {
        $randomCsv = $this->getRandomCsv();
        $parser = new CsvParser();

        $data = $parser->parse($randomCsv);
        array_shift($data);
        foreach ($data as $value) {
            $this->assertContains(',', $value[0]);
        }
    }

    /** @test */
    public function parser_should_throw_exception_if_data_is_invalid()
    {
        $this->expectException(InvalidCsvStringException::class);

        $parser = new CsvParser();
        $parser->parse('');
    }

    protected function getRandomCsv()
    {
        $products = factory(\App\Model\Product::class, 3)->make();
        $columns = [
            'name',
            'price',
            'quantity',
        ];
        $out = implode(",", $columns) . "\n";
        $products->each(function($item) use (&$out) {
            // Inserting comma here in name in order to see if our parser can handle it
            $out .= '"' . $item->name . ', ' . str_random(3) .  '","' . $item->price . '","' . $item->quantity . '"' . "\n";
        });
        return $out;
    }
}
