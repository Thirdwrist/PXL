<?php


namespace App\Filters;


use Illuminate\Support\Arr;

class AgeFilter extends BaseFilter
{
    protected array $data;

    public function __construct(array $data)
    {
       $this->data = $data;
    }

    public function run(): bool
    {
        $dob = Arr::get($this->data, 'date_of_birth');
        if($dob === null)
            return true;
        return ($age = age($dob)) >= 18 && ($age <=65);
    }
}