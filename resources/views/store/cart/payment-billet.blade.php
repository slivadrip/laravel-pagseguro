@extends('store.layouts.main')

@section('content')
<h1 class="title">
    <a href="{{ route('method.payment') }}"><i class="fa fa-chevron-circle-left" aria-hidden="true"></i></a>
    Pagamento por boleto
</h1>

<div class="text-center">
    <a href="#" id="payment-billet">
        <img src="{{url('assets/imgs/billet.png')}}" alt="Boleto" style="max-width: 100px;">
        <p>Gerar Boleto</p>
    </a>
</div>

<div class="preloader" style="display: none;">
    <img src="{{url('assets/imgs/preloader.gif')}}" alt="Preloader" style="max-width: 50px;">
    <p>Gerando Pedido, aguarde!</p>
</div>

@include('store.includes.preloader')

@endsection

@push('scripts')
<!--URL PagSeguro Transparent-->
<script src="{{config('pagseguro.url_transparent_js')}}"></script>

<script>
    $(function(){
        $("#payment-billet").click(function(){
            setSessionId();
            
            $(".preloader").show();
            
            return false;
        });
    });
    
    function setSessionId()
    {
        $.ajax({
            url: "{{route('pagseguro.code.transparent')}}",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function(data){
            console.log(data);
            PagSeguroDirectPayment.setSessionId(data);
            paymentBillet();
        }).fail(function(){
            $(".preloader").hide();
            alert("Fail request... :-(");
        }).always(function(){
            
        });
    }
    
    function paymentBillet()
    {
        var sendHash = PagSeguroDirectPayment.getSenderHash();

        $.ajax({
            url: "{{route('pagseguro.billet')}}",
            method: "POST",
            data: {sendHash: sendHash},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function(data){
            console.log(data);
            
            if(data.success) {
                location.href=data.payment_link;
            } else {
                alert("Falha!");
            }
        }).fail(function(){
            alert("Fail request... :-(");
        }).always(function(){
            $(".preloader").hide();
        });
    }
</script>
@endpush