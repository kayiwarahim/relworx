<?php

namespace KayiwaRahim\Relworx\Services;

use InvalidArgumentException;

trait PaymentRequestValidator
{
    protected function validatePaymentRequest(array $data): void
    {
        foreach (['reference', 'msisdn', 'amount', 'currency'] as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                throw new InvalidArgumentException("The {$field} field is required.");
            }
        }

        $reference = (string) $data['reference'];
        $referenceLength = strlen($reference);
        if ($referenceLength < 8 || $referenceLength > 36) {
            throw new InvalidArgumentException('The reference must contain between 8 and 36 characters.');
        }

        if (!is_string($data['msisdn']) || !preg_match('/^\\+[1-9][0-9]{7,14}$/', $data['msisdn'])) {
            throw new InvalidArgumentException('The msisdn must be an internationally formatted phone number, e.g. +256701345678.');
        }

        if (!is_numeric($data['amount']) || !is_finite((float) $data['amount']) || (float) $data['amount'] <= 0) {
            throw new InvalidArgumentException('The amount must be a finite positive number.');
        }

        if (!is_string($data['currency']) || !preg_match('/^[A-Za-z]{3}$/', $data['currency'])) {
            throw new InvalidArgumentException('The currency must be a three-letter ISO currency code.');
        }

        if (isset($data['description']) && !is_string($data['description'])) {
            throw new InvalidArgumentException('The description must be a string.');
        }
    }
}
