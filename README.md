# Laravel Array Query Builder

![](https://github.com/Red-Fern/laravel-array-query-builder/workflows/Run%20PHP%20Tests/badge.svg)
[![StyleCI](https://styleci.io/repos/225727963/shield)](https://styleci.io/repos/225727963)

This package allows you to add where clauses to eloquent query builders using multidimensional arrays. By following a simple structure, you can chain multiple query conditions, nested conditions and relational queries. 

```
$queryArray = [
    'condition' => 'or',
    'rules' => [
        [
            'field' => 'name',
            'operator' => '=',
            'value' => 'john'
        ],
        [
            'field' => 'age',
            'operator' => '>',
            'value' => 25
        ],
    ]
];

$users = User::arrayWheres($queryArray)->get();
```
The **condition** describes how the coinciding rules are applied. In this case the **name** and **age** fields will be wrapped in an **OR** query.

The above section would generate the following eloquent query builder:
```
$users = User::where('name', 'john')
            ->orWhere('age', '>', 25)
            ->get();
```

## Operator Types
When querying against fields, there are several options for the operator. These options are:

### Comparison Operators
The standard comparison operators e.g. =, <, >, <=, >=. An example below:
```
$rules = [
    'field' => 'age',
    'operator' => '>=',
    'value' => 25
];
```
This becomes:
```
$query->where('age','>=',25);
```

### Nullable checks
Checking for if a column is **"null"** or **"not null"** e.g.
```
$rules = [
    'field' => 'dob',
    'operator' => 'null'
];
```
This becomes:
```
$query->whereNull('dob');
```

### Between 
Checking if a field value is between array of values:
```
$rules = [
    'field' => 'age',
    'operator' => 'between',
    'value' => [20, 50]
];
```
This becomes:
```
$query->whereBetween('age', [20, 50]);
```

### In/Not in list of values
Checking if field value is **"in"** or **"not in"**  an array of values:
```
$rules = [
    'field' => 'id',
    'operator' => 'in',
    'value' => [2, 5, 6]
];
```
This becomes:
```
$query->whereIn('id', [2, 5, 6]);
```

### String comparison
Checking if a field value contains a string. This is basically an alias for a like query with wildcards e.g.
```
$rules = [
    'field' => 'name',
    'operator' => 'contains',
    'value' => 'john'
];
```
This becomes:
```
$query->where('name', 'like', '%john%');
```


## Relations
You can also query eloquent relations using dot notation.
```
 $queryArray = [
    'condition' => 'and',
    'rules' => [
        [
            'field' => 'name',
            'operator' => 'like',
            'value' => '%john%'
        ],
        [
            'field' => 'orders.order_date',
            'operator' => '>',
            'value' => '2019-01-01 00:00:00'
        ]
    ]
];

$users = User::arrayWheres($queryArray)->get();
```
Using dot notation in the fields, will determine that it will query, in this case the **orders** relation. The above query would become:

```
$users = User::where('name', 'like', '%john%')
        ->whereHas('orders', function($q) {
            $q->where('order_date', '>', '2019-01-01 00:00:00');
        })->get();
```

## Nested Queries
You can also add nested conditions to build more complex queries such as:

```
$rules = [
    'condition' => 'and',
    'rules' => [
        [
            'condition' => 'or',
            'rules' => [
                [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'john'
                ],
                [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'james'
                ],
            ]
        ],
        [
            'field' => 'orders.order_date',
            'operator' => '>',
            'value' => '2010-01-01 00:00:00'
        ],
    ]
];

$users = User::arrayWheres($queryArray)->get();
```

The above example shows how you can add nested conditional rules to the array. This follows the same format and you can nested to any depth. The above example would become:

```
$users = User::where(function($q) {
                $q->where('name', '=', 'john')
                    ->orWhere('name', '=', 'james');
            })
            ->whereHas('orders', function($q) {
                $q->where('order_date', '>', '2010-01-01 00:00:00');
            })->get();
```

## Future Development
The plan is to expand the capabilities of this package, extending the array capability to also allow query selects, joins, order by, group bys etc.

