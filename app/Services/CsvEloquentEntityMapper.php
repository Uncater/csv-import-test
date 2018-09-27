<?php

namespace App\Services;


use App\Contracts\EntityMapper;
use App\Exceptions\EntityMappingException;
use Illuminate\Database\Eloquent\Model;

class CsvEloquentEntityMapper implements EntityMapper
{

    protected $parser;

    public function __construct(CsvParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param $source
     * @param string $entityName
     * @throws EntityMappingException
     */
    public function mapSourceToEntity($source, string $entityName)
    {
        $data = $this->parser->parse($source);
        $fieldNames = array_shift($data);
        if (!is_subclass_of($entityName, Model::class)) {
            throw new EntityMappingException('Only eloquent models are allowed');
        }
        $columnListing = \Schema::getColumnListing((new $entityName)->getTable());
        if (!$this->csvNamesMatchColumns($fieldNames, $columnListing)) {
            throw new EntityMappingException("Field names for entity $entityName do no match");
        }
        foreach ($data as $value) {
            /** @var Model $entity */
            $entity = $this->mayRequireEntityUpdates($fieldNames) ? $this->getEntityInstance($value['id'], $entityName) : new $entityName;
            foreach ($value as $key => $item) {
                $entity->$key = $item;
            }
            $entity->save();
        }
    }

    protected function mayRequireEntityUpdates($fieldNames)
    {
        return in_array('id', $fieldNames);
    }

    protected function getEntityInstance($id, $entityName)
    {
        if ($id) {
            $entity = $entityName::find($id);
            if (!$entity) {
                throw new EntityMappingException("Please check data integrity, we don't have $entityName with id $id");
            }
        } else {
            $entity = new $entityName;
        }
        return $entity;
    }

    protected function csvNamesMatchColumns($csvFieldNames, $tableColumns)
    {
        return array_intersect($csvFieldNames, $tableColumns) == $csvFieldNames;
    }

}
