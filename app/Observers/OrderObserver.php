<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderItem;

class OrderObserver
{
    /**
     * Заполняем данные перед созданием модели
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function creating(Order $order)
    {
        //статус вновь созданного заказа "active"
        if ( $order->status == 'active') {
            //проверка запасов кол-во в заказе
            $check = true;
            $products = [];
            foreach ($order->items()->get() as $item) {
                /** @var $item OrderItem **/
                $product = $item->product()->get();
                //собираем данные об остатках товара
                if (isset($products[$product->id])) {
                    $stock_count = $products[$product->id];
                } else {
                    $stock_count = $products[$product->id] = $product->stock;
                }
                //Проверяем что товара хватает
                if ($stock_count >= $item->count){
                    $products[$product->id] -= $item->count;
                } else {
                    $check = false;
                    break;
                }
            }
            if ($check) {

            }
        }
    }

    /**
     * Handle the Order "updated" event.
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function updating(Order $order)
    {
        //Если изменили статус то пересчитываю остатки
        if (in_array('status', $order->getChanges())) {
            $status = $order->getOriginal('status');

        }
    }

    /**
     * Handle the Order "deleted" event.
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function deleted(Order $order)
    {
        //
    }
}
