<?php

function findValueInArray($needle, $haystack, $key)
{
    return array_search($needle, array_column($haystack, $key));
}
