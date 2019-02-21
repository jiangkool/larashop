@extends('layouts.app')
@section('title', '订单列表')

@section('content')
<div class="row">
<div class="col-lg-10 offset-lg-1">
<div class="card">
  <div class="card-header">订单列表</div>
  <div class="card-body">
    <ul class="list-group">
      @foreach($orders as $order)
        <li class="list-group-item">
          <div class="card">
            <div class="card-header">
              订单号：{{ $order->no }}
              <span class="float-right">{{ $order->created_at->format('Y-m-d H:i:s') }}</span>
            </div>
            <div class="card-body">
              <table class="table">
                <thead>
                <tr>
                  <th>商品信息</th>
                  <th class="text-center">单价</th>
                  <th class="text-center">数量</th>
                  <th class="text-center">订单总价</th>
                  <th class="text-center">状态</th>
                  <th class="text-center">操作</th>
                </tr>
                </thead>
                @foreach($order->items as $index => $item)
                  <tr data-id="{{ $order->id }}">
                    <td class="product-info">
                      <div class="preview">
                        <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">
                          <img src="{{ $item->product->image_url }}" width="80">
                        </a>
                      </div>
                      <div>
                        <span class="product-title">
                           <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">{{ $item->product->title }}</a>
                        </span>
                        <span class="sku-title">{{ $item->productSku->title }}</span>
                      </div>
                    </td>
                    <td class="sku-price text-center">￥{{ $item->price }}</td>
                    <td class="sku-amount text-center">{{ $item->amount }}</td>
                    @if($index === 0)
                      <td rowspan="{{ count($order->items) }}" class="text-center total-amount">￥{{ $order->total_amount }}</td>
                      <td rowspan="{{ count($order->items) }}" class="text-center">
                        @if($order->paid_at)
                          @if($order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING)
                            @if($order->ship_status==\App\Models\Order::SHIP_STATUS_RECEIVED)
                            已收货
                            @else
                            已支付
                            @endif
                          @else
                            {{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}
                          @endif
                        @elseif($order->closed)
                          已关闭
                        @else
                          未支付<br>
                          请于 {{ $order->created_at->addSeconds(config('app.order_ttl'))->format('H:i') }} 前完成支付<br>
                          否则订单将自动关闭
                        @endif

                      </td>
                      <td rowspan="{{ count($order->items) }}" class="text-center"><a class="btn btn-primary btn-sm" href="{{ route('orders.show',$order->id) }}">查看订单</a>  
                      @if($order->paid_at)
                        @if($order->ship_status==\App\Models\Order::SHIP_STATUS_DELIVERED && $order->ship_data)
                            <div class="line-label">物流信息：</div>
                            <div class="line-value">{{ $order->ship_data['express_company'] }} {{ $order->ship_data['express_no'] }}</div>
                            <div class="receive-button">
                                <button type="submit" id="btn-receive" class="btn btn-sm btn-success">确认收货</button>
                            </div>
                        @elseif($order->ship_status==\App\Models\Order::SHIP_STATUS_RECEIVED)
                          <button class="btn btn-danger btn-sm btn-apply-refund">申请退款</button> <a class="btn btn-danger btn-sm" href="{{ route('orders.review.show',$order->id) }}">写评价</a>
                        @else
                         <a class="btn btn-warning btn-sm " href="" disabled>等待发货</a>
                        @endif
                
                      @else
                       <a href="{{ route('payment.alipay',$order->id) }}" class="btn btn-success btn-sm">支付宝付款</a>
                      @endif
                     @if(isset($order->extra['refund_disagree_reason']))
                    <div style="border:1px solid #ddd;border-radius: 5px;margin: 10px;padding: 10px">
                      <span>拒绝退款理由：</span>
                      <div class="value">{{ $order->extra['refund_disagree_reason'] }}</div>
                    </div>
                    @endif
                     </td>
                    @endif
                  </tr>
                @endforeach
              </table>
            </div>
          </div>
        </li>
      @endforeach
    </ul>
    <div class="float-right">{{ $orders->render() }}</div>
  </div>
</div>
</div>
</div>
@endsection

@section('scriptsAfterJs')
<script>
  $(document).ready(function() {
    // 确认收货按钮点击事件
    $('#btn-receive').click(function() {
      var id=$(this).closest('tr').data('id');
      // 弹出确认框
      swal({
        title: "确认已经收到商品？",
        icon: "warning",
        dangerMode: true,
        buttons: ['取消', '确认收到'],
      })
      .then(function(ret) {
        // 如果点击取消按钮则不做任何操作
        if (!ret) {
          return;
        }
        // ajax 提交确认操作
        axios.post('/orders/'+id+'/received')
          .then(function () {
            // 刷新页面
            location.reload();
          })
      });
    });

     $('.btn-apply-refund').click(function () {
      var id=$(this).closest('tr').data('id');
      swal({
        text: '请输入退款理由',
        content: "input",
      }).then(function (input) {
        // 当用户点击 swal 弹出框上的按钮时触发这个函数
        if(!input) {
          swal('退款理由不可空', '', 'error');
          return;
        }
        // 请求退款接口
        axios.post('/orders/'+id+'/apply_refund', {reason: input})
          .then(function () {
            swal('申请退款成功', '', 'success').then(function () {
              // 用户点击弹框上按钮时重新加载页面
              location.reload();
            });
          });
      });
    });

  });
</script>
@endsection