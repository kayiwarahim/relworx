<?php

namespace KayiwaRahim\Relworx\Events;

class RelworxWebhookReceived
{
    public function __construct(public readonly array $payload)
    {
    }
}
