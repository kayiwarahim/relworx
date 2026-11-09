<?php

namespace KayiwaRahim\Relworx\Events;

class RelworxPaymentFailed
{
    public function __construct(public readonly array $payload)
    {
    }
}
