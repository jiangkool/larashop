<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\ProductSku;
use App\Http\Requests\AddCartRequest;

class CartController extends Controller
{
    public function add(AddCartRequest $request)
    {
    	$user=$request->user();
    	$skuId=$request->input('sku_id');
    	$amount=$request->input('amount');

    	if ($cart=$user->cartItems()->where('product_sku_id',$skuId)->first()) {
    		// 如果存在则直接叠加商品数量
            $cart->update([
                'amount' => $cart->amount + $amount,
            ]);

    	}else{

    		$cart=new CartItem(['amount'=>$amount]);
    		// 关联 user
    		$cart->user()->associate($user);
    		// 关联 sku 
    		$cart->productSku()->associate($skuId);

    		$cart->save();

    	}

    	return [];
    }

    public function index(Request $request)
    {
    	$cartItems=$request->user()->cartItems()->with('productSku.product')->get();
    	$addresses=$request->user()->userAddresses()->orderBy('last_used_at')->get();

    	return view('cart.index',compact('cartItems','addresses'));
    }

    public function remove(ProductSku $sku, Request $request)
    {
    	$request->user()->cartItems()->where('product_sku_id', $sku->id)->delete();

    	return [];
    }
}
