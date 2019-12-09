<?php

return [
    /**
     * Define list of aliases for query builder operators
     * e.g. gte => '>='
     *
     * Current operators: =, !=, >, <, >=, <=, null, not null, in, not in, between
     */
    'operator_aliases' => [
        'equal' => '=',
        'notequal' => '!=',
        'greaterthan' => '>',
        'greaterthanorequal' => '>=',
        'lessthan' => '<',
        'lessthanorequal' => '<='
    ]
];