<?php

namespace KayiwaRahim\Relworx\Events;

class RelworxPaymentSucceeded
{
    public function __construct(public readonly array $payload)
    {
    }
}
