<?php

namespace App\Http\Controllers;

use AdminSection;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrdersController extends Controller
{
    public function index(){
        $data = Order::all();
        return view('admin.products', $data);
    }

    public function getbyid(Request $r){
        $order = Order::find($r->id ?? 0);
        $items = $order->items()->get();
        return json_encode([
            'order' => $order,
            'items' => $items
        ]);
    }

    public function create(Request $r){
        $result = [];
        $result['status'] = 'ok';
        //создаем все или ничего
        DB::beginTransaction();
        $order = new Order();
        $order->user_id = $r->order['user_id'];
        $order->type = $r->order['type'];
        $order->status = $r->order['status'];
        $order->customer = $r->order['customer'];
        $order->phone = $r->order['phone'];
        if ($order->status == 'completed') {
            $order->completed_at = date("Y-m-d H:i:s", time());
        }
        if (!$order->save()){
            $result['status'] = 'fail';
        }
        $p = [];
        foreach ($r->items as $item){
            if ($item['isRemove'] === true) continue;
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $item['product_id'];
            $orderItem->count = $item['count'];
            $orderItem->discount = $item['discount'];
            $orderItem->cost = $item['cost'];
            $orderItem->save();
            //собираем данные по продуктам т.к. может быть несколько одинаковых позиций в заказе
            if (isset($p[$orderItem->product_id])) {
                $p[$orderItem->product_id] += $orderItem->count;
            } else {
                $p[$orderItem->product_id] = $orderItem->count;
            }
        }
        //Если статус изначально отменен то не обрабатываем складские позиции
        if ($order->status != 'canceled') {
            foreach ($p as $id => $totalCount) {
                /** @var $product Product */
                $product = Product::find($id);
                if (isset($product)) {
                    //проверка и изменение остатков если это возможно
                    if (!$product->changeStock($totalCount)) {
                        //позиций недостаточно откатываем транзакцию и отдаем ошибку
                        $need = $totalCount - $product->stock;
                        $result['status'] = 'fail';
                        $result['message'] = "<b>{$product->name}</b> нет на складе. Уменьшите все позиции этого товара в заказе сумарно до {$product->stock} шт. или пополните склад на $need шт.";
                        DB::rollBack();
                        break;
                    }
                } else {
                    $result['status'] = 'fail';
                }
            }
        }
        //фиксируем транзакцию
        if ($result['status'] == 'ok') {
            DB::commit();
        }
        return json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
    }

    public function update(Request $r){
        $result = [];
        $result['status'] = 'ok';
        $order = Order::find($r->order['id']);
        if (!isset($order)){
            $result['status'] = 'fail';
            $result['message'] = "нет такого заказа";
            return json_encode($result);
        }
        //старый статус
        $oldStatus = $order->status;
        //обновляем все или ничего
        DB::beginTransaction();
        //Обновляем данные в заказе
        $order->user_id = $r->order['user_id'];
        $order->type = $r->order['type'];
        $order->status = $r->order['status'];
        $order->customer = $r->order['customer'];
        $order->phone = $r->order['phone'];
        if ($order->status == 'completed') {
            $order->completed_at = date("Y-m-d H:i:s", time());
        } elseif ($order->status == 'canceled') {
            $order->completed_at = null;
        }
        $order->save();
        $p = [];
        //Обновляем данные в позициях заказа
        foreach ($r->items as $item){
            /** @var $item OrderItem */
            //Log::info(json_encode($item, JSON_PRETTY_PRINT));
            if (isset($item['id'])) {
                $orderItem = OrderItem::find($item['id']);
            } else {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
            }
            $orderItem->product_id = $item['product_id'];
            $oldCount = $orderItem->count;
            $orderItem->count = $item['count'];
            $orderItem->discount = $item['discount'];
            $orderItem->cost = $item['cost'];
            //собираем данные по продуктам т.к. может быть несколько одинаковых позиций в заказе
            if (isset($p[$orderItem->product_id]['newCount'])) {
                $p[$orderItem->product_id]['newCount'] += $orderItem->count;
            } else {
                $p[$orderItem->product_id]['newCount'] = $orderItem->count;
            }
            if (isset($p[$orderItem->product_id]['oldCount'])) {
                $p[$orderItem->product_id]['oldCount'] += $oldCount;
            } else {
                $p[$orderItem->product_id]['oldCount'] = $oldCount;
            }
            if ($item['isRemove'] == "true") {
                $orderItem->delete();
            } else {
                $orderItem->save();
            }
        }
        //Log::info(json_encode($p, JSON_PRETTY_PRINT));
        foreach ($p as $id => $count) {
            $oldCount = $count['oldCount'];
            $newCount = $count['newCount'];
            Log::info("$id new1= $newCount ");
            $isReturn = ($oldStatus != $order->status) && ($order->status == 'canceled');
            if (!$isReturn && $oldStatus != 'canceled') {
                $newCount = $newCount - $oldCount;
            }
            //Если было итоговое изменение кол-во по позиции
            if ($oldCount != $newCount || $isReturn || $oldStatus == 'canceled') {
                /** @var $product Product */
                $product = Product::find($id);
                Log::info("$id new2= $newCount ");
                if (isset($product)) {
                    //возврат товара в остатки при изменении статуса
                    if (!$product->changeStock($newCount,  $isReturn)){
                        $result['status'] = 'fail';
                        $result['message'] = "<b>{$product->name}</b> нет на складе. Уменьшите все позиции этого товара в заказе сумарно до {$product->stock} шт.";
                        DB::rollBack();
                        break;
                    }
                } else {
                    $result['status'] = 'fail';
                    DB::rollBack();
                }
            }
        }

        DB::commit();
        return json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
    }

    public function report(Request $r){
        $orders = [];
        $date = date("Y-m-d", time());
        if (isset($r->date)) {
            $date = $r->date;
            $sql = "select id, status, completed_at  from `orders` where (`completed_at` between '$date' and '$date 23:59:59') and `status` = 'completed'";
            $orders = DB::select($sql);
            $ids = [];
            foreach ($orders as $order){
                $ids[] = $order->id;
            }

            if (count($ids) > 0) {
                $sql = "SELECT SUM(cost) as total from order_items
                        WHERE order_id IN (" . implode(",", $ids) . ")";
                $total = DB::select($sql);
            }

        }
        return AdminSection::view(view('report', ['orders' => count($ids), 'price' => $total[0]->total ?? 0, 'date' => $date ]), "Отчет");
    }
}
