<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Exceptions\InvalidRequestException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()->where('on_sale', true)->paginate();

        return view('products.index', ['products' => $products]);
    }

    public function show(Product $product,Request $request)
    {
    	$favored = false;

    	if (!$product->on_sale) {
    		throw new InvalidRequestException('该商品未上架！');
    	}
    	if ($user=$request->user()) {

    		$favored = boolval($user->favoriteProducts()->find($product->id));
    	}
    	return view('products.show', ['product' => $product,'favored'=>$favored]);
    }

    public function favor(Product $product,Request $request)
    {
    	if (!$user=$request->user()) {
    		throw new InvalidRequestException('请先登录',401);
    	}
    	if ($user->favoriteProducts()->find($product->id)) {
    		return ;
    	}

    	$user->favoriteProducts()->attach($product);

    	return ;

    }

    public function disfavor(Product $product,Request $request)
    {
    	if (!$user=$request->user()) {
    		throw new InvalidRequestException('请先登录',401);
    	}
    	$user->favoriteProducts()->detach($product);

    	return ;

    }

    public function favorites(Request $request)
    {
    	$products=$request->user()->favoriteProducts()->paginate(16);

    	return view('products.favorites',compact('products'));
    }

}
