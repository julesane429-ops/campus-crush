<?php

namespace Tests\Unit;

use App\Services\PayDunyaService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayDunyaServiceTest extends TestCase
{
    public function test_softpay_failure_does_not_fallback_to_checkout_by_default(): void
    {
        config([
            'paydunya.base_url' => 'https://app.paydunya.com/api/v1',
            'paydunya.allow_checkout_fallback' => false,
            'paydunya.wave_enabled' => true,
        ]);

        Http::fake([
            'https://app.paydunya.com/api/v1/checkout-invoice/create' => Http::response([
                'response_code' => '00',
                'response_text' => 'https://app.paydunya.com/checkout/invoice/test-token',
                'token' => 'test-token',
            ]),
            'https://app.paydunya.com/api/v1/softpay/wave-senegal' => Http::response([
                'success' => false,
                'message' => 'SoftPay unavailable',
            ]),
        ]);

        $result = app(PayDunyaService::class)->payDirect(
            1,
            'Campus Tester',
            'tester@example.test',
            '771234567',
            'wave',
            500,
            'Test',
            'https://example.test/success',
            'https://example.test/cancel',
            'boost'
        );

        $this->assertFalse($result['success']);
        $this->assertSame('wave_softpay_failed', $result['method']);
        $this->assertNull($result['url']);
        $this->assertSame('test-token', $result['token']);
        $this->assertSame('SoftPay unavailable', $result['error']);
    }

    public function test_orange_money_prefers_direct_app_url(): void
    {
        config([
            'paydunya.base_url' => 'https://app.paydunya.com/api/v1',
            'paydunya.allow_checkout_fallback' => false,
        ]);

        Http::fake([
            'https://app.paydunya.com/api/v1/checkout-invoice/create' => Http::response([
                'response_code' => '00',
                'response_text' => 'https://app.paydunya.com/checkout/invoice/test-token',
                'token' => 'test-token',
            ]),
            'https://app.paydunya.com/api/v1/softpay/new-orange-money-senegal' => Http::response([
                'success' => true,
                'url' => 'https://app.paydunya.com/recharge-orange-sn?qr=1',
                'other_url' => [
                    'om_url' => 'https://orangemoneysn.page.link/test',
                    'maxit_url' => 'https://sugu.orange-sonatel.com/mp/test',
                ],
            ]),
        ]);

        $result = app(PayDunyaService::class)->payDirect(
            1,
            'Campus Tester',
            'tester@example.test',
            '771234567',
            'orange_money',
            500,
            'Test',
            'https://example.test/success',
            'https://example.test/cancel',
            'boost'
        );

        $this->assertTrue($result['success']);
        $this->assertSame('om_redirect', $result['method']);
        $this->assertSame('https://orangemoneysn.page.link/test', $result['url']);
        $this->assertSame('https://orangemoneysn.page.link/test', $result['om_url']);
        $this->assertSame('https://sugu.orange-sonatel.com/mp/test', $result['maxit_url']);
    }

    public function test_invoice_uses_public_paydunya_url_with_softpay_channels(): void
    {
        config([
            'app.url' => 'http://192.168.1.169:8001',
            'paydunya.base_url' => 'https://app.paydunya.com/api/v1',
            'paydunya.public_url' => 'https://campus-crush.test',
            'paydunya.ipn_url' => 'https://campus-crush.test/webhook/paydunya',
            'paydunya.allow_checkout_fallback' => false,
            'paydunya.wave_enabled' => true,
        ]);

        Http::fake([
            'https://app.paydunya.com/api/v1/checkout-invoice/create' => Http::response([
                'response_code' => '00',
                'response_text' => 'https://app.paydunya.com/checkout/invoice/test-token',
                'token' => 'test-token',
            ]),
            'https://app.paydunya.com/api/v1/softpay/wave-senegal' => Http::response([
                'success' => true,
                'url' => 'https://pay.wave.com/test',
            ]),
        ]);

        $result = app(PayDunyaService::class)->payDirect(
            1,
            'Campus Tester',
            'tester@example.test',
            '221771234567',
            'wave',
            500,
            'Test',
            'http://192.168.1.169:8001/boost/success?token=test-token',
            'http://192.168.1.169:8001/boost',
            'boost'
        );

        $this->assertTrue($result['success']);

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://app.paydunya.com/api/v1/checkout-invoice/create') {
                return false;
            }

            $payload = $request->data();

            return ($payload['channels'] ?? []) === ['orange-money-senegal', 'wave-senegal', 'free-money-senegal']
                && !array_key_exists('items', $payload)
                && ($payload['invoice']['items']['item_0']['name'] ?? null) === 'Test'
                && $payload['actions']['return_url'] === 'https://campus-crush.test/boost/success?token=test-token'
                && $payload['actions']['cancel_url'] === 'https://campus-crush.test/boost'
                && $payload['actions']['callback_url'] === 'https://campus-crush.test/webhook/paydunya';
        });
    }

    public function test_wave_can_be_temporarily_disabled_without_creating_invoice(): void
    {
        config([
            'paydunya.base_url' => 'https://app.paydunya.com/api/v1',
            'paydunya.wave_enabled' => false,
        ]);

        Http::fake();

        $result = app(PayDunyaService::class)->payDirect(
            1,
            'Campus Tester',
            'tester@example.test',
            '777777777',
            'wave',
            500,
            'Test',
            'https://example.test/success',
            'https://example.test/cancel',
            'boost'
        );

        $this->assertFalse($result['success']);
        $this->assertSame('wave_disabled', $result['method']);
        $this->assertNull($result['token']);
        $this->assertStringContainsString('Wave est temporairement indisponible', $result['error']);
        Http::assertNothingSent();
    }

    public function test_disabled_wave_is_not_in_invoice_channels(): void
    {
        config([
            'paydunya.base_url' => 'https://app.paydunya.com/api/v1',
            'paydunya.wave_enabled' => false,
        ]);

        Http::fake([
            'https://app.paydunya.com/api/v1/checkout-invoice/create' => Http::response([
                'response_code' => '00',
                'response_text' => 'https://app.paydunya.com/checkout/invoice/test-token',
                'token' => 'test-token',
            ]),
            'https://app.paydunya.com/api/v1/softpay/new-orange-money-senegal' => Http::response([
                'success' => true,
                'other_url' => [
                    'om_url' => 'https://orangemoneysn.page.link/test',
                ],
            ]),
        ]);

        $result = app(PayDunyaService::class)->payDirect(
            1,
            'Campus Tester',
            'tester@example.test',
            '771234567',
            'orange_money',
            500,
            'Test',
            'https://example.test/success',
            'https://example.test/cancel',
            'boost'
        );

        $this->assertTrue($result['success']);

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://app.paydunya.com/api/v1/checkout-invoice/create') {
                return false;
            }

            return ($request->data()['channels'] ?? []) === ['orange-money-senegal', 'free-money-senegal'];
        });
    }
}
