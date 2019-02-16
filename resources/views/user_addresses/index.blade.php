@extends('layouts.app')
@section('title', '收货地址列表')

@section('content')
  <div class="row">
    <div class="col-md-10 offset-md-1">
      <div class="card panel-default">
        <div class="card-header">
      收货地址列表
      <a href="{{ route('user_addresses.create') }}" class="float-right">新增收货地址</a>
    </div>
        <div class="card-body">
          <table class="table table-bordered table-striped">
            <thead>
            <tr>
              <th>收货人</th>
              <th>地址</th>
              <th>邮编</th>
              <th>电话</th>
              <th>操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($addresses as $address)
              <tr>
                <td>{{ $address->contact_name }}</td>
                <td>{{ $address->full_address }}</td>
                <td>{{ $address->zip }}</td>
                <td>{{ $address->contact_phone }}</td>
                <td>
                  <a class="btn btn-primary" href="{{ route('user_addresses.edit',['id'=>$address->id]) }}">修改</a>
                  <button class="btn btn-danger btn-del-address" data-id="{{$address->id}}" type="button"  >删除</button>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scriptsAfterJs')
<script>
  let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}
  $(function(){
    $(".btn-del-address").click(function(){
      var id = $(this).data('id');
      swal({
        title: "确认要删除该地址？",
        icon: "warning",
        buttons: ['取消', '确定'],
        dangerMode: true,
      }).then(function(rs){
        if (rs) {
           axios.delete('/user_addresses/' + id)
          .then(function () {
            // 请求成功之后重新加载页面
            location.reload();
          })
        }
      })
    })
  })
</script>
@endsection