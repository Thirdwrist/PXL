<?php


namespace App\Filters;


interface BaseFilter{

    public function __construct(array $data);

    public function handle():bool;
}