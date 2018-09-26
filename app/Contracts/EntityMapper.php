<?php

namespace App\Contracts;


interface EntityMapper
{
    public function mapSourceToEntity($source, string $entityName);
}
