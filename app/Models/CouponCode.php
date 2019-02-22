<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Exceptions\InvalidRequestException;

class CouponCode extends Model
{
    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENT ='percent';

    public static $typeMap=[
    	self::TYPE_FIXED =>'固定金额',
    	self::TYPE_PERCENT=>'比例'
    ];

    protected $fillable=[
		'name',	
		'code',	
		'type',	
		'value',	
		'total',	
		'used',	
		'min_amount',	
		'not_before',	
		'not_after',	
		'enabled',	
    ];

    protected $dates=[
    	'not_before','not_after'
    ];

    protected $casts=[
    	'enabled'=>'boolean'
    ];

    protected $appends = ['description'];

    public static function findAvailableCode($length = 16)
    {
        do {
            // 生成一个指定长度的随机字符串，并转成大写
            $code = strtoupper(Str::random($length));
        // 如果生成的码已存在就继续循环
        } while (self::query()->where('code', $code)->exists());

        return $code;
    }

    public function getDescriptionAttribute()
    {
        $str = '';

        if ($this->min_amount > 0) {
            $str = '满'.str_replace('.00', '', $this->min_amount);
        }
        if ($this->type === self::TYPE_PERCENT) {
            return $str.'优惠'.str_replace('.00', '', $this->value).'%';
        }

        return $str.'减'.str_replace('.00', '', $this->value);
    }

    public function checkAvailable(User $user,$orderAmount=null)
    {
        $used=Order::where('user_id',$user->id)
            ->where('coupon_code_id',$this->id)
            ->where(function($query){
                $query->where(function($query){
                    $query->whereNull('paid_at')->where('closed',false);
                })->orWhere(function($query) {
                    $query->whereNotNull('paid_at')
                        ->where('refund_status', '!=', Order::REFUND_STATUS_SUCCESS);
                });
            })->exists();

        if ($used) {
            throw new InvalidRequestException('你已经使用过这张优惠券了',403);
        }

        if (!$this->enabled) {
            throw new InvalidRequestException('优惠券不存在',403);
        }

        if ($this->total - $this->used <= 0) {
            throw new InvalidRequestException('该优惠券已被兑完',403);
        }

        if ($this->not_before && $this->not_before->gt(Carbon::now())) {
            throw new InvalidRequestException('该优惠券现在还不能使用',403);
        }

        if ($this->not_after && $this->not_after->lt(Carbon::now())) {
            throw new InvalidRequestException('该优惠券已过期',403);
        }

        if (!is_null($orderAmount) && $orderAmount < $this->min_amount) {
            throw new InvalidRequestException('订单金额不满足该优惠券最低金额',403);
        }
    }

    public function getAdjustedPrice($orderAmount)
    {
        // 固定金额
        if ($this->type === self::TYPE_FIXED) {
            // 为了保证系统健壮性，我们需要订单金额最少为 0.01 元
            return max(0.01, $orderAmount - $this->value);
        }

        return number_format($orderAmount * (100 - $this->value) / 100, 2, '.', '');
    }

    public function changeUsed($increase = true)
    {
        // 传入 true 代表新增用量，否则是减少用量
        if ($increase) {
            // 与检查 SKU 库存类似，这里需要检查当前用量是否已经超过总量
            return $this->newQuery()->where('id', $this->id)->where('used', '<', $this->total)->increment('used');
        } else {
            return $this->decrement('used');
        }
    }
}
