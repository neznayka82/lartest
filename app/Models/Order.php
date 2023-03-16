<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id int
 * @property $customer string
 * @property $phone string
 * @property $status string Статус
 * @property $user_id int
 * @property $type string
 * @property $created_at
 * @property $completed_at
 **/
class Order extends Model
{

    protected $table = 'orders';

    public $timestamps = false;

    /** Получаем менеджера
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class, 'id');
    }

    /** Получаем связанные Позиции заказа
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
