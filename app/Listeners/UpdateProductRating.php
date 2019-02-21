<?php

namespace App\Listeners;

use App\Events\OrderReviewed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\OrderItem;

class UpdateProductRating
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
     * @param  OrderReviewed  $event
     * @return void
     */
    public function handle(OrderReviewed $event)
    {
        $order=$event->getOrder();
        $items=$order->items()->with(['product'])->get();
        foreach ($items as $item) {
            $data=OrderItem::where('product_id',$item->product_id)->whereNotNull('reviewed_at')->get();
             $result = OrderItem::query()
                ->where('product_id', $item->product_id)
                ->whereHas('order', function ($query) {
                    $query->whereNotNull('paid_at');
                })
                ->first([
                    \DB::raw('count(*) as review_count'),
                    \DB::raw('avg(rating) as rating')
                ]);
             $item->product->update([
                'rating'       => $data->avg('rating'),
                'review_count' => $data->count(),
            ]);
        }

    }
}
