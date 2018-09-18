<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client as Guzzle;

// Exceptions
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Throwable;
use Config;

class PagSeguro extends Model
{
    use PagSeguroTrait;

    private $cart, $reference, $user;
    private $currency = 'BRL';


    public function __construct(Cart $cart)
    {
        $this->cart = $cart;

        $this->reference = uniqid(date('YmdHis'));

        $this->user = auth()->user();
    }

    public function getSessionId()
    {
        $params = $this->getConfigs();
        $params = http_build_query($params);

        try {
            $guzzle = new Guzzle;
            $response = $guzzle->request('POST', config('pagseguro.url_transparent_session'), [
                'query' => $params,
                'verify' => false,
            ]);
            $body = $response->getBody();
            $contents = $body->getContents();

            $xml = simplexml_load_string($contents);

            return $xml->id;
        } catch (ClientException $e) {
            return $e->getResponse();
        }
    }

    public function paymentBillet($sendHash)
    {
        $params = [
            'senderHash' => $sendHash,
            'paymentMode' => 'default',
            'paymentMethod' => 'boleto',
            'currency' => $this->currency,
            'reference' => $this->reference,
        ];
        //$params = http_build_query($params);
        $params = array_merge($params, $this->getConfigs());
        $params = array_merge($params, $this->getItems());
        $params = array_merge($params, $this->getSender());
        $params = array_merge($params, $this->getShipping());


        try {
            $guzzle = new Guzzle;
            $response = $guzzle->request('POST', config('pagseguro.url_payment_transparent'), [
                'form_params' => $params,
                'verify' => false,
            ]);
            $body = $response->getBody();
            $contents = $body->getContents();

            $xml = simplexml_load_string($contents);

            return [
                'success' => true,
                'payment_link' => (string)$xml->paymentLink,
                'reference' => $this->reference,
                'code' => (string)$xml->code,
            ];
        } catch (ClientException $e) {
            return [
                'success' => false,
                'reference' => (string)$e->getMessage(),
                'code' => (string)$e->getCode(),
            ];
        }
    }

    public function paymentCredCard($request)
    {
        // Pega as informações de parcelas (installments) selecionada pelo usuário
        $installments = explode(' / ', $request->installments);
        // Quantidade de parcelas
        $installmentQuantity = $installments[0];
        // Valor da parcela
        $installmentValue = number_format($installments[1], 2, '.', ''); // (O valor da parcela também pode ser calculado dividindo o total do carrinho pela quantidade de parcelas: $this->cart->total() / $installmentQuantity)

        // Opções enviadas para a API do PagSeguro
        $params = [
            'senderHash' => $request->senderHash,
            'paymentMode' => 'default',
            'paymentMethod' => 'creditCard',
            'currency' => $this->currency,
            'reference' => $this->reference,
            'creditCardToken' => $request->card_token,
            'installmentQuantity' => $installmentQuantity,
            'installmentValue' => $installmentValue,
            'noInterestInstallmentQuantity' => 12,// Quantidade de parcelas sem juros
        ];
        $params = array_merge($params, $this->getConfigs());
        $params = array_merge($params, $this->getItems());
        $params = array_merge($params, $this->getSender());
        $params = array_merge($params, $this->getShipping());
        $params = array_merge($params, $this->getCreditCard($request->card_holder_name));
        $params = array_merge($params, $this->billingAddress());


        try {
            $guzzle = new Guzzle;
            $response = $guzzle->request('POST', config('pagseguro.url_payment_transparent'), [
                'form_params' => $params,
                'verify' => false,
            ]);
            $body = $response->getBody();
            $contents = $body->getContents();

            $xml = simplexml_load_string($contents);
            return [
                'success' => true,
                'reference' => $this->reference,
                'code' => (string)$xml->code,
                'status' => (string)$xml->status,
            ];
        } catch (Throwable | ServerException | ClientException $e) {
            return [
                'success' => false,
                'reference' => (string)$e->getMessage(),
                'code' => (string)$e->getCode(),
            ];
        }
    }


    public function getStatusTransaction($notificationCode)
    {
        $configs = $this->getConfigs();
        $params = http_build_query($configs);

        $guzzle = new Guzzle;
        $response = $guzzle->request('GET', config('pagseguro.url_notification') . "/{$notificationCode}", [
            'query' => $params,
            'verify' => false,
        ]);
        $body = $response->getBody();
        $contents = $body->getContents();

        $xml = simplexml_load_string($contents);

        return [
            'status' => (string)$xml->status,
            'reference' => (string)$xml->reference,
        ];
    }
}
