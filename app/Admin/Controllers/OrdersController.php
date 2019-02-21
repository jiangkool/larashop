<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Admin\HandleRefundRequest;

class OrdersController extends Controller
{
    use HasResourceActions;

    public function handleRefund(Order $order, HandleRefundRequest $request)
    {
        if ($order->refund_status!==Order::REFUND_STATUS_APPLIED) {
            throw new InvalidRequestException('该订单状态不正确');
        }

        if ($request->input('agree')) {

            $extra = $order->extra ?: [];
            unset($extra['refund_disagree_reason']);
            $order->update([
                'extra' => $extra,
            ]);
            $this->_orderRefund($order);
            
        }else{

            $extra = $order->extra ?:[];
            $extra['refund_disagree_reason']=$request->input('reason');

            $order->update([
                'refund_status'=>Order::REFUND_STATUS_PENDING,
                'extra'        =>$extra,
            ]);

        }

        return $order;
    }

    protected function _orderRefund(Order $order)
    {
        if ($order->refund_status==Order::REFUND_STATUS_PROCESSING || $order->refund_status==Order::REFUND_STATUS_SUCCESS) {
            throw new InvaliedRequestException('订单已退款');
        }

        if ($order->payment_method=='alipay') {
            $refundNo= Order::getAvailableRefundNo();
            $ret=app('alipay')->refund([
                'out_trade_no' => $order->no, // 之前的订单流水号
                'refund_amount' => $order->total_amount, // 退款金额，单位元
                'out_request_no' => $refundNo, // 退款订单号
            ]);
        /// 根据支付宝的文档，如果返回值里有 sub_code 字段说明退款失败
         if ($ret->sub_code) {
                // 将退款失败的保存存入 extra 字段
                $extra = $order->extra;
                $extra['refund_failed_code'] = $ret->sub_code;
                // 将订单的退款状态标记为退款失败
                $order->update([
                    'refund_no' => $refundNo,
                    'refund_status' => Order::REFUND_STATUS_FAILED,
                    'extra' => $extra,
                ]);
            } else {
                // 将订单的退款状态标记为退款成功并保存退款订单号
                $order->update([
                    'refund_no' => $refundNo,
                    'refund_status' => Order::REFUND_STATUS_SUCCESS,
                ]);
            }

        }elseif($order->payment_method=='wechat_pay'){
             $refundNo = Order::getAvailableRefundNo();
             app('wechat_pay')->refund([
                'out_trade_no' => $order->no, // 之前的订单流水号
                'total_fee' => $order->total_amount * 100, //原订单金额，单位分
                'refund_fee' => $order->total_amount * 100, // 要退款的订单金额，单位分
                'out_refund_no' => $refundNo, // 退款订单号
                // 微信支付的退款结果并不是实时返回的，而是通过退款回调来通知，因此这里需要配上退款回调接口地址
                'notify_url' => 'http://requestbin.fullcontact.com/******' // 由于是开发环境，需要配成 requestbin 地址
             ]);

             $order->update([
                    'refund_no' => $refundNo,
                    'refund_status' => Order::REFUND_STATUS_PROCESSING,
                ]);

        }else{
            throw new InvaliedRequestException('付款方式不正确！');
        }

    }

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show(Order $order, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body(view('admin.orders.show', ['order' => $order]));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order);
        $grid->model()->whereNotNull('paid_at')->orderBy('paid_at', 'desc');
        $grid->no('订单流水号');
        $grid->column('user.name', '买家');
        $grid->total_amount('总金额')->sortable();
        $grid->paid_at('支付时间')->sortable();
        $grid->ship_status('物流')->display(function($value) {
            return Order::$shipStatusMap[$value];
        });
        $grid->refund_status('退款状态')->display(function($value) {
            return Order::$refundStatusMap[$value];
        });
        $grid->created_at('下单时间');
        //$grid->updated_at('Updated at');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Order::findOrFail($id));

        $show->id('Id');
        $show->no('No');
        $show->user_id('User id');
        $show->address('Address');
        $show->total_amount('Total amount');
        $show->remark('Remark');
        $show->paid_at('Paid at');
        $show->payment_method('Payment method');
        $show->payment_no('Payment no');
        $show->refund_status('Refund status');
        $show->refund_no('Refund no');
        $show->closed('Closed');
        $show->reviewed('Reviewed');
        $show->ship_status('Ship status');
        $show->ship_data('Ship data');
        $show->extra('Extra');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Order);

        $form->text('no', 'No');
        $form->number('user_id', 'User id');
        $form->textarea('address', 'Address');
        $form->decimal('total_amount', 'Total amount');
        $form->textarea('remark', 'Remark');
        $form->datetime('paid_at', 'Paid at')->default(date('Y-m-d H:i:s'));
        $form->text('payment_method', 'Payment method');
        $form->text('payment_no', 'Payment no');
        $form->text('refund_status', 'Refund status')->default('pending');
        $form->text('refund_no', 'Refund no');
        $form->switch('closed', 'Closed');
        $form->switch('reviewed', 'Reviewed');
        $form->text('ship_status', 'Ship status')->default('pending');
        $form->textarea('ship_data', 'Ship data');
        $form->textarea('extra', 'Extra');

        return $form;
    }

    public function ship(Order $order, Request $request)
    {
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未付款');
        }

        if ($order->ship_status !== Order::SHIP_STATUS_PENDING) {
            throw new InvalidRequestException('该订单已发货');
        }

        $data=$this->validate($request,[
            'express_company'=>['required'],
            'express_no'=>['required'],
        ],[],[
            'express_company' => '物流公司',
            'express_no'      => '物流单号',
        ]);

        $order->update([
            'ship_status'=>Order::SHIP_STATUS_DELIVERED,
            'ship_data'=>$data
        ]);

        return redirect()->back();
    }

}
