@extends('bootstrap')

@section('title', 'Fake Deposit')

@section('header')
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
@endsection

@section('content')
    <div style="height: 50px;">
    </div>
    <div class="container">
        @foreach ($orders as $order)
            <div class="row border-top">
                <div class="col" style="height: 40px;">
                    {{ $order->order_id }}
                </div>
                <div class="col">
                    {{ $order->amount }}
                </div>
                <div class="col">
                    <button type="button" class="btn btn-primary btn-sm" onclick="notify({{ $order->id }}, 1)">成功</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="notify({{ $order->id }}, 0)">失敗</button>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        function notify(id, success) {
            var data = {success: success};
            $.post({
                url: "{{$url}}" + id,
                dataType: "json",
                cache: false,
                data: data,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        alert('通知成功')
                    } else {
                        alert('通知失敗')
                    }
                }
            })
        }
    </script>
@endsection
