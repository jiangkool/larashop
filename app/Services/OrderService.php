<?php 
namespace App\Services;

use App\Models\User;
use App\Models\UserAddress;
use App\Models\Order;
use App\Models\ProductSku;
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseOrder;
use Carbon\Carbon;


class OrderService
{
	public function store(User $user, UserAddress $address, $remark, $items)
	{
		$order=\DB::transaction(function () use($user, $address, $remark, $items) {
		    
			//更新地址最后使用时间
			$address->update(['last_used_at',Carbon::now()]);

			$order=new Order([
				'address'      => [ // 将地址信息放入订单中
	                    'address'       => $address->full_address,
	                    'zip'           => $address->zip,
	                    'contact_name'  => $address->contact_name,
	                    'contact_phone' => $address->contact_phone,
	                ],
	            'remark'      => $remark,
	            'total_amount'=> 0
			]);

			// 关联 user 模型
			$order->user()->associate($user);

			$order->save();

			//计算订单总额
			$totalAmount = 0;

			foreach ($items as $item) {

				$sku=ProductSku::find($item['sku_id']);
            	// 创建一个 OrderItem 并直接与当前订单关联
	            $order_item = $order->items()->make([
	                   'amount' => $item['amount'],
	                   'price'  => $sku->price,
	            ]);	

	            $order_item->product()->associate($sku->product_id);
	            $order_item->productSku()->associate($sku->id);
	            $order_item->save();

	            //计算总价
                $totalAmount += $sku->price * $item['amount'];
                if ($sku->decreaseStock($item['amount']) <= 0) {
			        throw new InvalidRequestException('该商品库存不足');
			    }

			}

			$order->update(['total_amount'=>$totalAmount]);

			// 将下单的商品从购物车中移除
            $skuIds = collect($items)->pluck('sku_id')->all();
            app(CartService::class)->remove($skuIds);

            return $order;

		});

		dispatch(new CloseOrder($order, config('app.order_ttl')));
		
		return $order;
	}

}