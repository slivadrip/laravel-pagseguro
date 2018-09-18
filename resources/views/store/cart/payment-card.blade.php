@extends('store.layouts.main')

@section('content')
<h1 class="title">
    <a href="{{ route('method.payment') }}"><i class="fa fa-chevron-circle-left" aria-hidden="true"></i></a>
    Pagamento por cartão
</h1>

<div class="col-md-12">
    <div class="alert alert-danger" id="error" style="display: none;">
        Algum erro inesperado...
    </div>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {!! Form::open(['id' => 'payment_form']) !!}


        {!! Form::hidden('brand') !!}
        {!! Form::hidden('card_token') !!}

        <div class="form-group col-md-4">
            <div class="input-group mb-2 mr-sm-2 mb-sm-0">
              <div class="input-group-addon"><i class="fa fa-id-card-o" aria-hidden="true"></i></div>
              {!! Form::text('cardNumber', null, ['placeholder' => 'Número do Cartão', 'class' => 'form-control', 'id' => 'cardNumber', 'required']) !!}
            </div>
        </div>

        <div class="form-group col-md-4">
          <div class="input-group mb-2 mr-sm-2 mb-sm-0">
            <div class="input-group-addon"><i class="fa fa-user-o" aria-hidden="true"></i></div>
            {!! Form::text('card_holder_name', null, ['placeholder' => 'Nome no Cartão', 'class' => 'form-control', 'id' => 'card_holder_name', 'required']) !!}
          </div>
        </div>

        <div class="form-group col-md-4">
          <div class="input-group mb-2 mr-sm-2 mb-sm-0">
            <div class="input-group-addon"><i class="fa fa-address-card-o" aria-hidden="true"></i></div>
            {!! Form::select('installments', ['' => 'Parcelamento'], null, ['class' => 'form-control', 'required', 'disabled']) !!}
          </div>
        </div>


        <div class="form-group col-md-4">
          <div class="input-group mb-2 mr-sm-2 mb-sm-0">
            <div class="input-group-addon"><i class="fa fa-calendar" aria-hidden="true"></i></div>
            {!! Form::text('card_expiration_month', null, ['placeholder' => 'Expiração MM', 'class' => 'form-control', 'id' => 'card_expiration_month', 'required']) !!}
          </div>
        </div>

        <div class="form-group col-md-4">
          <div class="input-group mb-2 mr-sm-2 mb-sm-0">
            <div class="input-group-addon"><i class="fa fa-calendar-o" aria-hidden="true"></i></div>
            {!! Form::text('card_expiration_year', null, ['placeholder' => 'Ano AAAA', 'class' => 'form-control', 'id' => 'card_expiration_year', 'required']) !!}
          </div>
        </div>

        <div class="form-group col-md-4">
          <div class="input-group mb-2 mr-sm-2 mb-sm-0">
            <div class="input-group-addon"><i class="fa fa-credit-card" aria-hidden="true"></i></div>
            {!! Form::text('card_cvv', null, ['placeholder' => 'Código de Segurança do Cartão', 'class' => 'form-control', 'id' => 'card_cvv', 'required']) !!}
          </div>
        </div>

        <div class="float-left text-center">
            <button type="submit" class="btn btn-success botao-pagamento">
                Pagar com Cartão
                <i class="fa fa-credit-card" aria-hidden="true"></i>
            </button>
        </div>

    {!! Form::close() !!}
</div>

@include('store.includes.preloader')

@endsection

@push('scripts')
<!--URL PagSeguro Transparent-->
<script src="{{config('pagseguro.url_transparent_js')}}"></script>

<script>
    /*
    * DOM carregado (ready)
    */
    $(function(){
        // Recupera o session id que o PagSeguro precisa para validar a transação
        setSessionId();

        // Quando o formulário com os dados do cartão é submetido
        $("#payment_form").submit(function() {
            // Inicia o preloader        
            startPreloader();

            // Cria o token do cartão
            createCardToken();
            
            // Não permite que o formulário seja submetido
            return false;
        });

        $('input[name=cardNumber]').blur(function(){
            if ( $(this).val() != "" )
                getBrand();
        });
    });

    /*
    * Recupera a bandeira do cartão
    * Documentação: https://dev.pagseguro.uol.com.br/documentacao/pagamento-online/pagamentos/pagamento-transparente#obter-bandeira-cartao
    */
    function getBrand()
    {
        PagSeguroDirectPayment.getBrand({
            cardBin: $('input[name=cardNumber]').val().replace(/ /g, ''), // Pega o número do cartão, informado pelo usuário (no input)
            success: function(response) {
                // Nome da bandeira do cartão: visa, master, elo e etc.
                var brand = response.brand.name;

                // Armazena o nome da bandeira do cartão em um campo oculto (hidden)
                $('input[name=brand]').val(brand);

                // Função que mostra as opções de parlamento
                getInstallments();
            },
            error: function (response) {
                console.log(response);
                showError();
            }//showError()// Caso dê erros, apresenta para o usuário
        });
    }

    /*
    * Exibe as opções de parcelamento de acordo com o total da compra
    * Documentação: https://dev.pagseguro.uol.com.br/documentacao/pagamento-online/pagamentos/pagamento-transparente#opcoes-parcelamento
    */
    function getInstallments()
    {
        // Mostra um "preloader"
        $('select[name=installments]').append('<option value="">Carregando...</option>');

        // Recupera as opções de parcelamento
        PagSeguroDirectPayment.getInstallments({
            amount: {{ Session::get('cart')->total() }}, // Total do carrinho $$
            maxInstallmentNoInterest: 12,/*{quantidade de parcelas sem juros}*/
            brand: $('input[name=brand]').val(), // Nome da bandeira do cartão
            success: function (response) {
                // Recupera o nome da bandeira do cartão
                var brand = $('input[name=brand]').val();

                // Array com as opções de parcelamento
                var installments = response.installments[brand];

                // Habilita o campo de parcelas, que por padrão é desativado
                $('select[name=installments]').removeAttr('disabled');

                // Retira o preloader
                $('select[name=installments]').find('option').remove();

                // Cria os options no <select> com as opções de parcelamento
                $.each( installments, function( index, value ) {
                    // Texto a ser exibido no <option>, ex: 12x de 12.20 - Total: 50.00
                    var textOption = value.quantity+' x de '+value.installmentAmount+' - total: '+value.totalAmount;

                    // Value option (quantidade de parcelas / valor)
                    var valueOption = value.quantity + ' / ' +  value.installmentAmount;

                    // Adiciona os options no select
                    $('select[name=installments]').append('<option value="'+ valueOption +'">'+ textOption +'</option>');
                });
            },
            error: function (response) {
                console.log(response);
                showError();
            },//, // Exibe erros
            complete: endPreloader() // Finaliza o preloader
        });
    }

    
    /*
    * Cria uma sessão para valida essa transação
    * Documentação: https://dev.pagseguro.uol.com.br/documentacao/pagamento-online/pagamentos/pagamento-transparente#sessao
    */
    function setSessionId()
    {
        // Inicia o preloader
        startPreloader();

        // Envia a requisição AJAX para a API do PagSeguro para retornar o token de sessão.
        $.ajax({
            url: "{{route('pagseguro.code.transparent')}}", // URL da requisição
            method: "POST", // Método de requisição (verbo http)
            headers: {
                // Pega o content do meta csrf-token, para proteção com ataques CSRF
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function(data){
            // Define a sessão com o token retornado da API do PagSeguro
            PagSeguroDirectPayment.setSessionId(data);
        }).fail(function() {
            // Exibe os erros  
            showError();
        }).always(function() {
            // Finaliza o preloader
            endPreloader();
        });
    }


    /*
    * Obter o token do cartão de crédito
    * Documentação: https://dev.pagseguro.uol.com.br/documentacao/pagamento-online/pagamentos/pagamento-transparente#obter-token-cartao
    */
    function createCardToken()
    {
        PagSeguroDirectPayment.createCardToken({
            cardNumber: $('input[name=cardNumber]').val().replace(/ /g, ''), // Número do cartão informado sem espaços
            brand: $('input[name=brand]').val(), // Bandeira do cartão
            cvv: $('input[name=card_cvv]').val(), // Código de segurança
            expirationMonth: $('input[name=card_expiration_month]').val(), // Mês de expiração
            expirationYear: $('input[name=card_expiration_year]').val(), // Ano de expiração
            success: function(response){
                // Coloca o token retornado da API no campo oculto
                $('input[name=card_token]').val(response.card.token);

                // Chama o método que faz o pagamento com cartão
                paymentCard();
            },
            error: function (response) {
                console.log(response);
                showError();
            }
        });
    }


    /*
    * Inicia o pagamento por cartão
    * Documentação: https://dev.pagseguro.uol.com.br/documentacao/pagamento-online/pagamentos/pagamento-transparente#efetuar-pagamento
    */    
    function paymentCard()
    {
        startPreloader();

        // Recupera todos os dados do formulário
        var data = $('#payment_form').serialize();

        // Recupera a hash da transação
        var senderHash = PagSeguroDirectPayment.getSenderHash();

        // Envia a requisição AJAX com os dados do cartão
        $.ajax({
            url: "{{route('pagseguro.card')}}", // URL da requisição
            method: "POST", // Método de requisição (verbo http)
            headers: {
                // Pega o content do meta csrf-token, para proteção com ataques CSRF
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: data+'&senderHash='+senderHash // Passa todos os dados do formulário para a requisição
        }).done(function(data){
            if( data.success ) {
                // Redireciona para ver os pedidos
                window.location.href = "{{ route('my.orders') }}";
            } else {
                showError();
            }
        }).fail(function() {
            // Exibe os erros  
            showError();
        }).always(function() {
            // Finaliza o preloader
            endPreloader();
        });
    }


    /*
    * Função de preloader (exibe)
    */
    function startPreloader()
    {
        $(".preloader").show();

        // Oculta algum erro se tiver exibindo:
        $("#error").hide();
    }


    /*
    * Função de preloader (oculta)
    */
    function endPreloader()
    {
        $(".preloader").hide();
    }


    /*
    * Exibe uma mensagem de erro
    */
    function showError()
    {
        $("#error").show();

        // Finaliza o preloader em caso de erros
        endPreloader();
    }
</script>
@endpush