<?php

Auth::routes();

$this->group(['middleware' => 'auth'], function(){
    /*
     * Routes Cart
     */
    $this->get('meio-pagamento', 'StoreController@methodPayment')
            ->middleware('check.qty.cart')
            ->name('method.payment');
    $this->get('pagamento-boleto', 'StoreController@paymentBillet')
            ->middleware('check.qty.cart')
            ->name('payment.billet');
    $this->post('pagseguro-getcode', 'PagSeguroController@getCode')->name('pagseguro.code.transparent');
    $this->post('pagseguro-payment-billet', 'PagSeguroController@billet')->name('pagseguro.billet');

    // Cartão
    $this->get('pagamento-cartao', 'StoreController@paymentCard')
            ->middleware('check.qty.cart')
            ->name('payment.card');
    $this->post('pagseguro-payment-card', 'PagSeguroController@paymentCard')->name('pagseguro.card');
    
    
    
    /*
     * Routes Profile
     */
    $this->get('meu-perfil', 'UserController@profile')->name('profile');
    $this->post('atualizar-perfil', 'UserController@profileUpdate')->name('profile.update');
    $this->get('minha-senha', 'UserController@password')->name('password');
    $this->post('atualizar-senha', 'UserController@passwordUpdate')->name('password.update');
    $this->get('logout', 'UserController@logout')->name('logout');
    
    $this->get('meus-pedidos', 'UserController@myOrders')->name('my.orders');
    $this->get('pedido/{reference}', 'UserController@detailsOrder')->name('order.details');
});

$this->get('remove-cart/{id}', 'CartController@remove')->name('remove.cart');
$this->get('add-cart/{id}', 'CartController@add')->name('add.cart');

$this->get('carrinho', 'StoreController@cart')->name('cart');

$this->get('/', 'StoreController@index')->name('home');