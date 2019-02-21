<?php 
namespace App\Services;

use Auth;
use App\Models\CartItem;

class CartService
{
   public function get()
   {
   	  return Auth::user()->cartItems()->with(['productSku.product'])->get();
   }

   public function add($skuId,$amount)
   {
   		$user=Auth::user();

   		if ($cart=$user->cartItems()->where('product_sku_id',$skuId)->first()) {

   			$cart->update([
   				'amount'=>$cart->amount+$amount
   			]);

   		}else{

   			$cart=new CartItem(['amount'=>$amount]);
    		// 关联 user
    		$cart->user()->associate($user);
    		// 关联 sku 
    		$cart->productSku()->associate($skuId);
    		$cart->save();
   		}

   		return $cart;
   }

   public function remove($skuIds)
   {
   		if (!is_array($skuIds)) {
   			$skuIds=[$skuIds];
   		}
   		return Auth::user()->cartItems()->whereIn('product_sku_id',$skuIds)->delete();
   }

}

