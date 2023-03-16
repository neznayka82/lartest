<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Response;

/**
 * @property $order_id int
 * @property $product_id int
 * @property $count int
 * @property $discount float
 * @property $cost float
**/
class OrderItem extends Model
{
    protected $table = 'order_items';
    protected $primaryKey = 'id';

    public $timestamps = false;

    /** Получаем товар
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Product::class, 'product_id');
    }

    /** Получаем заказ
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function order(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Order::class, 'order_id');
    }
}
