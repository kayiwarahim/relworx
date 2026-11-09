<?php

namespace KayiwaRahim\Relworx\Tests\Feature;

use Illuminate\Support\Facades\Http;
use KayiwaRahim\Relworx\Exceptions\RelworxException;
use KayiwaRahim\Relworx\Facades\Relworx;
use KayiwaRahim\Relworx\Tests\TestCase;

class ApiErrorTest extends TestCase
{
    public function test_it_marks_server_errors_from_payment_initiation_as_an_unknown_outcome(): void
    {
        Http::fake(['*request-payment' => Http::response(['message' => 'Temporary provider error.'], 500)]);

        try {
            Relworx::collections()->request([
                'reference' => 'ORDER-100005',
                'msisdn' => '+256701345672',
                'currency' => 'UGX',
                'amount' => 10000,
            ]);
            $this->fail('Expected a RelworxException to be thrown.');
        } catch (RelworxException $exception) {
            $this->assertSame(500, $exception->httpStatus());
            $this->assertTrue($exception->outcomeUnknown());
        }
    }

    public function test_it_exposes_http_status_and_provider_error_body(): void
    {
        Http::fake([
            '*request-payment' => Http::response([
                'success' => false,
                'message' => 'Invalid account number.',
                'code' => 'INVALID_ACCOUNT',
            ], 422),
        ]);

        try {
            Relworx::collections()->request([
                'reference' => 'ORDER-100004',
                'msisdn' => '+256701345672',
                'currency' => 'UGX',
                'amount' => 10000,
            ]);
            $this->fail('Expected a RelworxException to be thrown.');
        } catch (RelworxException $exception) {
            $this->assertSame(422, $exception->httpStatus());
            $this->assertSame('INVALID_ACCOUNT', $exception->relworxCode());
            $this->assertSame('Invalid account number.', $exception->responseBody()['message']);
            $this->assertFalse($exception->outcomeUnknown());
        }
    }
}
