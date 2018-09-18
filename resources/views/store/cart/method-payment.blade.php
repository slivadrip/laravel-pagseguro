@extends('store.layouts.main')

@section('content')
<h1 class="title">Escolha o meio de pagamento</h1>

<div class="text-center">
    <div class="col-md-6">
    	<a href="{{ route('payment.billet') }}">
	        <img src="{{url('assets/imgs/billet.png')}}" alt="Boleto" style="max-width: 100px;">
	        <p>Boleto</p>
	    </a>
    </div>
    <div class="col-md-6">
    	<a href="{{ route('payment.card') }}">
	        <img src="{{url('assets/imgs/credit-card.png')}}" alt="Cartão" style="max-width: 100px;">
	        <p>Cartão</p>
	    </a>
    </div>
</div>

@endsection