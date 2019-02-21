<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\ProductSku;
use App\Http\Requests\AddCartRequest;
use App\Services\CartService;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService=$cartService;
    }

    public function add(AddCartRequest $request)
    {
    	$user=$request->user();
    	$skuId=$request->input('sku_id');
    	$amount=$request->input('amount');

    	$this->cartService->add($skuId,$amount);

    	return [];
    }

    public function index(Request $request)
    {
    	$cartItems=$this->cartService->get();
    	$addresses=$request->user()->userAddresses()->orderBy('last_used_at')->get();

    	return view('cart.index',compact('cartItems','addresses'));
    }

    public function remove(ProductSku $sku, Request $request)
    {
    	$this->cartService->remove($sku->id);

    	return [];
    }
}
