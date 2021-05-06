<?php

use Carbon\Carbon;


function sanitizeDate($date)
{
    if($date)
    {
        $explode = explode('/', $date);
        return Carbon::parse(implode('-', $explode))->format('Y-m-d');
    }
    return $date;
}

function age($date)
{

    $explode = explode('/', $date);
    return Carbon::parse(implode('-', $explode))->diffInYears(now());
}
