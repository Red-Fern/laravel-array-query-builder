<?php

namespace RedFern\ArrayQueryBuilder\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use RedFern\ArrayQueryBuilder\ArrayQueryable;

class TestModel extends Model
{
    use ArrayQueryable;

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
