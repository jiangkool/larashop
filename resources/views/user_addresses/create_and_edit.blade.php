@extends('layouts.app')
@section('title',  ($address->id ? '修改': '新增') . '收货地址')
@section('content')
<h2 class="text-center">
  {{ $address->id ? '修改': '新增' }}收货地址
</h2>
<hr>
	@if(count($errors)>0)
	<div class="alert alert-danger">
		 <ul>
		@foreach($errors->all() as $error)
		<li><i class="glyphicon glyphicon-remove"></i> {{ $error }}</li>
		@endforeach
		</ul>
	</div>
	@endif
  @if($address->id)
    <form class="form-horizontal" action="{{ route('user_addresses.update', ['user_address' => $address->id]) }}" method="post">
      {{ method_field('PUT') }}
  @else
    <form class="form-horizontal" action="{{ route('user_addresses.store') }}" method="post">
  @endif
	 {{ csrf_field() }}
<div id="distpicker"  class="form-group row">
	    <label class="col-form-label col-sm-2 text-md-right">省市区</label>
	    <div class="col-sm-3">
	    	<select name="province" class="form-control"></select>
	    </div>
	    <div class="col-sm-3">
	    	<select name="city" class="form-control"></select>
	    </div>
	    <div class="col-sm-3">
	    	<select name="district" class="form-control"></select>
	    </div>
</div>
	<div class="form-group row">
	  <label class="col-form-label text-md-right col-sm-2">详细地址</label>
	  <div class="col-sm-9">
	    <input type="text" class="form-control" name="address" value="{{ old('address', $address->address) }}">
	  </div>
	</div>
	<div class="form-group row">
	  <label class="col-form-label text-md-right col-sm-2">邮编</label>
	  <div class="col-sm-9">
	    <input type="text" class="form-control" name="zip" value="{{ old('zip', $address->zip) }}">
	  </div>
	</div>
	<div class="form-group row">
	  <label class="col-form-label text-md-right col-sm-2">姓名</label>
	  <div class="col-sm-9">
	    <input type="text" class="form-control" name="contact_name" value="{{ old('contact_name', $address->contact_name) }}">
	  </div>
	</div>
	<div class="form-group row">
	  <label class="col-form-label text-md-right col-sm-2">电话</label>
	  <div class="col-sm-9">
	    <input type="text" class="form-control" name="contact_phone" value="{{ old('contact_phone', $address->contact_phone) }}">
	  </div>
	</div>
	<div class="form-group row text-center">
	  <div class="col-12">
	    <button type="submit" class="btn btn-primary">提交</button>
	  </div>
	</div>

</form>
@endsection

@section('scriptsAfterJs')

<script src="/js/distpicker.data.js"></script>
<script src="/js/distpicker.js"></script>
<script>
	$("#distpicker").distpicker({
		 province: "{{ old('province', $address->province) }}",
  		 city: "{{ old('city', $address->city) }}",
  		 district: "{{ old('district', $address->district)}}"
	});

</script>

@endsection