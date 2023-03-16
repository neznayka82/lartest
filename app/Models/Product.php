<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @property $name string
 * @property $price float
 * @property $stock int остаток на складе
**/
class Product extends Model
{
    protected $table = 'products';

    public $timestamps = false;


    /** Получаем все заказы в которых есть этот продукт
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function orders(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'orders', 'order_id');
    }

    /** изменяем остатки на складе
     * @param int $count
     * @param false $isReturn
     * @return bool
     */
    public function changeStock(int $count, $isReturn = false): bool
    {
        if ($isReturn) {
            //это возврат товара на склад
            $this->stock += $count;
            $this->save();
            return true;
        } else {
            //проверка что товар есть на складе
            if ($this->stock >= $count){
                $this->stock -= $count;
                $this->save();
                return true;
            }
        }
        return false;
    }
}
