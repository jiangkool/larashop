@extends('layouts.app')
@section('title', '商品列表')

@section('content')
<div class="row">
<div class="col-lg-10 offset-lg-1">
<div class="card">
  <div class="card-body">
    <div class="row products-list">
      @foreach($products as $product)
        <div class="col-3 product-item">
          <div class="product-content">
            <div class="top">
              <a href="{{ route('products.show',['id'=>$product->id]) }}">
              <div class="img"><img src="{{ $product->image_url }}" width="150" alt=""></div>
              <div class="price"><b>￥</b>{{ $product->price }}</div>
              <div class="title">{{ $product->title }}</div>
              </a>
            </div>
            <div class="bottom">
              <div class="sold_count">销量 <span>{{ $product->sold_count }}笔</span></div>
              <div class="review_count">评价 <span>{{ $product->review_count }}</span></div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
</div>
</div>
@endsection