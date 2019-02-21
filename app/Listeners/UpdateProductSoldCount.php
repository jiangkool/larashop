<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\OrderItem;

class UpdateProductSoldCount
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderPaid  $event
     * @return void
     */
    public function handle(OrderPaid $event)
    {
        $order=$event->getOrder();

        $order->load('items.product');

        // 循环 order items
        foreach ($order->items as $item) {
            
            $product=$item->product;

            // 计算对应商品的销量
            $pro_amount=OrderItem::query()
                       ->where('product_id',$product->id)
                       ->whereHas('order',function($query){
                            $query->whereNotNull('paid_at');
                       })
                       ->sum('amount');
            // 更新销量
            $product->update([
                'sold_count' => $pro_amount,
            ]);
        }


    }
}
