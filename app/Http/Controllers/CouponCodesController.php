<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CouponCode;
use App\Exceptions\InvalidRequestException;
use Carbon\Carbon;

class CouponCodesController extends Controller
{
    public function show($code,Request $request)
    {
    	if (!$coupon=CouponCode::where('code',$code)->where('enabled',true)->first()) {
    		
    		throw new InvalidRequestException('优惠券不存在！',404);
    	}	
		$coupon->checkAvailable($request->user());

    	return $coupon;
    }


}
