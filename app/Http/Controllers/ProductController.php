<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\OrderItem;
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


        $data=OrderItem::query()
            ->where(function($query){
                $query->where('id',1)->orWhere('id',2);
            })->get();
            
            
        $reviews = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at') // 筛选出已评价的
            ->with(['order.user', 'productSku']) // 预先加载关联关系
            ->orderBy('reviewed_at', 'desc') // 按评价时间倒序
            ->limit(10) // 取出 10 条
            ->get();
    	return view('products.show', ['product' => $product,'favored'=>$favored,'reviews'=>$reviews]);
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
