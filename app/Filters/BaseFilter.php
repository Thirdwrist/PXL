<?php


namespace App\Filters;


abstract class BaseFilter{

    abstract function __construct(array $data);

    abstract function run():bool;
}