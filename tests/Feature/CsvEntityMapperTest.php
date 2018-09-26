<?php

namespace Tests\Feature;

use App\Exceptions\EntityMappingException;
use App\Model\Product;
use App\Services\CsvEloquentEntityMapper;
use App\Services\CsvParser;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CsvEntityMapperTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function mapper_should_create_new_entities_from_csv_file()
    {
        $data = factory(\App\Model\Product::class, 5)->make()->toArray();
        $columns = [
            'name',
            'price',
            'quantity',
        ];
        $mapper = $this->createMapper(array_merge([$columns], $data));
        $mapper->mapSourceToEntity('', Product::class);

        foreach ($data as $value) {
            $this->assertDatabaseHas('products', [
                'name' => $value['name'],
            ]);
        }
    }

    /** @test */
    public function mapper_should_not_create_entities_that_already_exist_if_id_field_is_given()
    {
        $productsCount = 5;
        $data = factory(\App\Model\Product::class, $productsCount)->create()->toArray();
        $columns = [
            'id',
            'name',
            'price',
            'quantity',
        ];

        $mapper = $this->createMapper(array_merge([$columns], $data));
        $mapper->mapSourceToEntity('', Product::class);

        $this->assertTrue(Product::count() == $productsCount);
    }

    /** @test */
    public function mapper_should_update_entities_if_they_already_exist()
    {
        $data = factory(\App\Model\Product::class, 5)->create()->toArray();
        $columns = [
            'id',
            'name',
            'price',
            'quantity',
        ];
        array_walk($data, function (&$item) {
           $item['name'] = $this->faker->name;
        });

        $mapper = $this->createMapper(array_merge([$columns], $data));
        $mapper->mapSourceToEntity('', Product::class);

        foreach ($data as $value) {
            $this->assertDatabaseHas('products', [
                'id' => $value['id'],
                'name' => $value['name'],
            ]);
        }
    }

    /** @test */
    public function mapper_should_throw_exception_if_entity_is_not_found_by_id()
    {
        $this->expectException(EntityMappingException::class);

        $data = factory(\App\Model\Product::class, 5)->create()->toArray();
        $columns = [
            'id',
            'name',
            'price',
            'quantity',
        ];
        array_walk($data, function (&$item) {
            $item['id'] = mt_rand(100, 110);
        });

        $mapper = $this->createMapper(array_merge([$columns], $data));
        $mapper->mapSourceToEntity('', Product::class);
    }


    /** @test */
    public function mapper_should_not_work_if_fields_do_not_match()
    {
        $this->expectException(EntityMappingException::class);

        $data = factory(\App\Model\Product::class, 5)->make()->toArray();
        $columns = [
            'inventory',
            'code',
            'type',
        ];
        $mapper = $this->createMapper(array_merge([$columns], $data));
        $mapper->mapSourceToEntity('', Product::class);
    }

    protected function createMapper($data)
    {
        $parser = Mockery::mock(CsvParser::class);
        $parser->shouldReceive('parse')
            ->andReturn($data);
        $mapper = new CsvEloquentEntityMapper($parser);
        return $mapper;
    }

}
