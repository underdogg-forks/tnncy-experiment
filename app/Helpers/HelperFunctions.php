<?php

function linearSearch($value, $array)
{
    foreach ($array as $item) {
        if ($item->name == $value->name) {
            return true;
        }
    }

    return false;
}
