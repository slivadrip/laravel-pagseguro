@extends('store.layouts.main')

@section('content')
<h1 class="title">Escolha o meio de pagamento</h1>

<a href="#" id="payment-billet">Boleto</a>
{!! Form::open(['id' => 'form']) !!}
{!! Form::close() !!}

@endsection

@push('scripts')
<!--URL PagSeguro Transparent-->
<script src="{{config('pagseguro.url_transparent_js')}}"></script>

<script>
    $(function(){
        $("#payment-billet").click(function(){
            setSessionId();
            
            return false;
        });
    });
    
    function setSessionId()
    {
        var data = $('form#form').serialize();

        $.ajax({
            url: "{{route('pagseguro.code.transparent')}}",
            method: "POST",
            data: data
        }).done(function(data){
            console.log(data);
            PagSeguroDirectPayment.setSessionId(data);
            paymentBillet();
        }).fail(function(){
            alert("Fail request 1... :-(");
        });
    }
    
    function paymentBillet()
    {
        var sendHash = PagSeguroDirectPayment.getSenderHash();

        var data = $('#form').serialize()+"&sendHash="+sendHash;

        $.ajax({
            url: "{{route('pagseguro.billet')}}",
            method: "POST",
            data: data
        }).done(function(url){
            console.log(url);

            location.href=url;
        }).fail(function(){
            alert("Fail request 2... :-(");
        });
    }
</script>
@endpush