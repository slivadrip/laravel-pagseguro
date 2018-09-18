<?php

namespace App\Models;
use GuzzleHttp\Client as Guzzle;

trait PagSeguroTrait
{
    public function getConfigs()
    {
        return [
            'email' => config('pagseguro.email'),
            'token' => config('pagseguro.token'),
        ];
    }
    
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }
    
    public function getItems()
    {
        $items = [];
        $itemsCart = $this->cart->getItems();
        
        $posistion = 1;
        foreach ($itemsCart as $item) {
            $items["itemId{$posistion}"]            = $item['item']->id;
            $items["itemDescription{$posistion}"]   = $item['item']->description;
            $items["itemAmount{$posistion}"]        = number_format($item['item']->price, 2, '.', '');
            $items["itemQuantity{$posistion}"]      = $item['qtd'];
            
            $posistion++;
        }
        
        return $items;
        /*
        return [
            'itemId1' => '0001',
            'itemDescription1' => 'Produto PagSeguroI',
            'itemAmount1' => '99999.99',
            'itemQuantity1' => '1',
            'itemWeight1' => '1000',
            'itemId2' => '0002',
            'itemDescription2' => 'Produto PagSeguroII',
            'itemAmount2' => '99999.98',
            'itemQuantity2' => '2',
            'itemWeight2' => '750',
        ];
         */
    }
    
    
    public function getSender()
    {
        return [
            'senderName'        => $this->user->name,
            'senderAreaCode'    => $this->user->area_code,
            'senderPhone'       => $this->user->phone,
            'senderEmail'       => $this->user->email,
            'senderCPF'         => $this->user->cpf,
        ];
    }
    
    public function getShipping()
    {
        return [
            'shippingType'                  => '1',
            'shippingAddressStreet'         => $this->user->street,
            'shippingAddressNumber'         => $this->user->number,
            'shippingAddressComplement'     => $this->user->complement,
            'shippingAddressDistrict'       => $this->user->district,
            'shippingAddressPostalCode'     => $this->user->postal_code,
            'shippingAddressCity'           => $this->user->city,
            'shippingAddressState'          => $this->user->state,
            'shippingAddressCountry'        => 'BRL',
        ];
    }


    public function getCreditCard($holderName)
    {
        return [
            'creditCardHolderName'      => $holderName,
            'creditCardHolderCPF'       => $this->user->cpf,
            'creditCardHolderBirthDate' => '01/01/1900',
            'creditCardHolderAreaCode'  => '99',
            'creditCardHolderPhone'     => '99999999',
        ];
    }


    public function billingAddress()
    {
        return [
            'billingAddressStreet'      => $this->user->street,
            'billingAddressNumber'      => $this->user->number,
            'billingAddressComplement'  => $this->user->complement,
            'billingAddressDistrict'    => $this->user->district,
            'billingAddressPostalCode'  => $this->user->postal_code,
            'billingAddressCity'        => $this->user->city,
            'billingAddressState'       => $this->user->state,
            'billingAddressCountry'     => 'BRL',
        ];
    }
}