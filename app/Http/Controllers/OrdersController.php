<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\UserAddress;
use App\Services\OrderService;
use App\Exceptions\InvalidRequestException;
use Carbon\Carbon;
use App\Http\Requests\SendReviewRequest;
use App\Events\OrderReviewed;
use App\Http\Requests\ApplyRefundRequest;

class OrdersController extends Controller
{
    public function store(OrderRequest $request,OrderService $orderService)
    {
    	$user=$request->user();
        $address = UserAddress::find($request->input('address_id'));

    	return $orderService->store($user,$address,$request->remark,$request->items);
    }


    public function index(Request $request)
    {
    	$user=$request->user();
    	$orders=$user->orders()->with(['items.product', 'items.productSku'])->orderBy('created_at', 'desc')->paginate(10);
    	// $orders = Order::query()
     //        // 使用 with 方法预加载，避免N + 1问题
     //        ->with(['items.product', 'items.productSku']) 
     //        ->where('user_id', $request->user()->id)
     //        ->orderBy('created_at', 'desc')
     //        ->get();
    	
    	return view('orders.index',compact('orders'));
    }

    public function show(Order $order)
    {
    	$this->authorize('own',$order);
        
    	return view('orders.show',['order'=>$order->load(['items.product','items.productSku'])]);
    }

    public function received(Order $order, Request $request)
    {
        $this->authorize('own', $order);
        
        // 如果订单未发货 || 订单已签收
        if ($order->ship_status!==Order::SHIP_STATUS_DELIVERED) {
            throw new InvalidRequestException('订单状态不正确！');
        }

        $order->update(['ship_status' => Order::SHIP_STATUS_RECEIVED]);

        return redirect()->back();
    }

    public function review(Order $order)
    {
        // 校验权限
        $this->authorize('own', $order);
        // 判断是否已经支付
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }
        // 使用 load 方法加载关联数据，避免 N + 1 性能问题
        return view('orders.review', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    public function sendReview(Order $order, SendReviewRequest $request)
    {
        // 校验权限
        $this->authorize('own', $order);
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }
        // 判断是否已经评价
        if ($order->reviewed) {
            throw new InvalidRequestException('该订单已评价，不可重复提交');
        }
        $reviews = $request->input('reviews');
        // 开启事务
        \DB::transaction(function () use ($reviews, $order) {
            // 遍历用户提交的数据
            foreach ($reviews as $review) {
                $orderItem = $order->items()->find($review['id']);
                // 保存评分和评价
                $orderItem->update([
                    'rating'      => $review['rating'],
                    'review'      => $review['review'],
                    'reviewed_at' => Carbon::now(),
                ]);

            }
            // 将订单标记为已评价
            $order->update(['reviewed' => true]);
            event(new OrderReviewed($order));
        });    

        return redirect()->back();
    }

    public function applyRefund(Order $order, ApplyRefundRequest $request)
    {
        $this->authorize('own',$order);

        if (!$order->paid_at) {
            throw new InvalidRequestException('订单未付款');
        }

        if ($order->refund_status !== Order::REFUND_STATUS_PENDING) {
            throw new InvalidRequestException('该订单已经申请过退款，请勿重复申请');
        }

        // 清空额外字段
        $extra                  = $order->extra ?: [];
        $extra['refund_reason'] = $request->input('reason');

        $order->update([
            'refund_status' => Order::REFUND_STATUS_APPLIED,
            'extra'         => $extra,
        ]);
        
        return $order;
    }



}
