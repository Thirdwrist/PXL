<?php


namespace App\Filters;


use Illuminate\Support\Arr;

class AgeFilter implements BaseFilter
{
    protected array $data;

    public function __construct(array $data)
    {
       $this->data = $data;
    }

    public function handle(): bool
    {
        $dob = Arr::get($this->data, 'date_of_birth');
        if($dob === null)
            return true;
        return ($age = age($dob)) >= 18 && ($age <=65);
    }
}