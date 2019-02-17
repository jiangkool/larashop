<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use Carbon\Carbon;
use App\Models\ProductSku;
use App\Models\Order;

class OrdersController extends Controller
{
    public function store(OrderRequest $request)
    {
    	$user=$request->user();

    	$order=\DB::transaction(function () use ($user,$request) {
    	    	
    	    	// 查找地址
    	    	$address=$user->userAddresses()->find($request->address_id);

    	    	$address->update(['last_used_at',Carbon::now()]);

    	    	//创建订单
    	    	$order=new Order([
    	    		 'address'      => [ // 将地址信息放入订单中
	                    'address'       => $address->full_address,
	                    'zip'           => $address->zip,
	                    'contact_name'  => $address->contact_name,
	                    'contact_phone' => $address->contact_phone,
	                ],
	                'remark'       => $request->input('remark'),
	                'total_amount' => 0,
    	    	]);

    	    	//关联当前用户
    	    	$order->user()->associate($user);

    	    	$order->save();

    	    	$totalAmount = 0;
            	$items       = $request->input('items');
            	// 遍历用户提交的 SKU
            	foreach ($items as $item) {
            		$sku=ProductSku::find($item['sku_id']);
            		// 创建一个 OrderItem 并直接与当前订单关联
	                $order_item = $order->items()->make([
	                    'amount' => $item['amount'],
	                    'price'  => $sku->price,
	                ]);

	                $order_item->product()->associate($sku);
	                $order_item->productSku()->associate($sku);
	                $order_item->save();

	                //计算总价
	                $totalAmount += $sku->price * $item['amount'];
	                if ($sku->decreaseStock($item['amount']) <= 0) {
				        throw new InvalidRequestException('该商品库存不足');
				    }
            	}

            	$order->update(['total_amount'=>$totalAmount]);

            	// 将下单的商品从购物车中移除
            	$skuIds = collect($items)->pluck('sku_id');
            	$user->cartItems()->whereIn('product_sku_id',$skuIds)->delete();
            	
            	return $order;	

    	});

    	return $order;
    }
}
