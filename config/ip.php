<?php

return [
    'allow_list' => array_values(array_filter(array_map('trim', explode(',', env('IP_ALLOW_LIST', ''))))),
];