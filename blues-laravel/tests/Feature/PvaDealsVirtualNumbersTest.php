<?php

namespace Tests\Feature;

use App\Models\{AdminUser, Notification, User, VirtualNumberOrder, Wallet, WalletTransaction};
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Cache, Http};
use Tests\TestCase;

class PvaDealsVirtualNumbersTest extends TestCase
{
    use RefreshDatabase;

    private const SERVICE_ID = '697139f7fe5460ddc2f27214';
    private const REQUEST_ID = '697a90d25ef1873ef44f48bc';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Setting::set('pvadeals_api_key', 'test-pvadeals-key');
        Setting::set('pvadeals_enabled', '1');
        Setting::set('herosms_exchange_rate', '1000');
        Setting::set('herosms_commission_type', 'flat');
        Setting::set('herosms_number_price', '0');
    }

    public function test_server3_catalog_uses_pvadeals_country_stock_and_price_data(): void
    {
        Http::fake([
            'prod-v3.pvadeals.com/v3/api/services/all' => Http::response($this->servicesResponse()),
        ]);
        $buyer = $this->createBuyer(1000);

        $this->actingAs($buyer)
            ->getJson(route('dashboard.virtual-numbers.services', ['server' => 3, 'country' => 'US']))
            ->assertOk()
            ->assertJsonCount(1, 'services')
            ->assertJsonPath('services.0.code', self::SERVICE_ID)
            ->assertJsonPath('services.0.name', 'Airbnb')
            ->assertJsonPath('services.0.country_code', 'US')
            ->assertJsonPath('services.0.count', 4)
            ->assertJsonPath('services.0.usd_cost', 0.2)
            ->assertJsonPath('services.0.price', 200);

        $this->actingAs($buyer)
            ->getJson(route('dashboard.virtual-numbers.countries', ['server' => 3]))
            ->assertOk()
            ->assertJsonCount(2, 'countries');

        $this->actingAs($buyer)
            ->get(route('dashboard.virtual-numbers', ['server' => 3]))
            ->assertOk();
    }

    public function test_admin_can_configure_server3_without_the_saved_api_key_being_rendered(): void
    {
        Setting::set('pvadeals_api_key', 'test-pvadeals-key');
        Http::fake([
            'prod-v3.pvadeals.com/v3/api/balance' => Http::response([
                'success' => true,
                'data' => ['credits' => 20],
            ]),
        ]);
        $admin = AdminUser::create([
            'email' => 'pva-admin@example.test',
            'password_hash' => 'unused',
            'display_name' => 'PVA Admin',
        ]);

        $this->withSession(['admin_id' => $admin->id])
            ->get(route('admin.virtual-number-settings'))
            ->assertOk()
            ->assertSee('PVADeals')
            ->assertDontSee('test-pvadeals-key');
    }

    public function test_server3_order_charges_wallet_and_sends_the_bearer_authenticated_short_term_request(): void
    {
        Http::fake([
            'prod-v3.pvadeals.com/v3/api/services/all' => Http::response($this->servicesResponse()),
            'prod-v3.pvadeals.com/v3/api/purchase' => Http::response([
                'success' => true,
                'data' => [
                    'requests' => [[
                        '_id' => self::REQUEST_ID,
                        'serviceName' => 'Airbnb',
                        'serviceId' => self::SERVICE_ID,
                        'number' => '+13135550123',
                        'status' => 'RESERVED',
                        'country' => 'US',
                        'amount' => 0.2,
                        'numberType' => 'STR',
                        'endTime' => now()->addMinutes(20)->toIso8601String(),
                    ]],
                ],
            ]),
        ]);
        $buyer = $this->createBuyer(1000);

        $response = $this->actingAs($buyer)->postJson(route('dashboard.virtual-numbers.order'), [
            'service' => self::SERVICE_ID,
            'country' => 0,
            'country_name' => 'United States',
            'server_pref' => 3,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.phone_number', '13135550123');
        $this->assertDatabaseHas('virtual_number_orders', [
            'user_id' => $buyer->id,
            'activation_id' => self::REQUEST_ID,
            'service' => self::SERVICE_ID,
            'provider' => 'server3',
            'status' => 'waiting',
        ]);
        $this->assertSame(800.0, (float) Wallet::where('user_id', $buyer->id)->value('balance'));
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $buyer->id,
            'type' => 'purchase',
            'amount' => -200,
        ]);

        Http::assertSent(fn ($request) =>
            $request->url() === 'https://prod-v3.pvadeals.com/v3/api/purchase'
            && $request->hasHeader('Authorization', 'Bearer test-pvadeals-key')
            && $request['services'] === [['serviceId' => self::SERVICE_ID]]
        );
    }

    public function test_uncertain_purchase_stays_reserved_and_is_reconciled_only_when_history_match_is_unique(): void
    {
        Http::fake([
            'prod-v3.pvadeals.com/v3/api/services/all' => Http::response($this->servicesResponse()),
            'prod-v3.pvadeals.com/v3/api/purchase' => Http::response([
                'success' => false,
                'message' => 'Provider unavailable',
            ], 500),
        ]);
        $buyer = $this->createBuyer(1000);

        $response = $this->actingAs($buyer)->postJson(route('dashboard.virtual-numbers.order'), [
            'service' => self::SERVICE_ID,
            'country' => 0,
            'country_name' => 'United States',
            'server_pref' => 3,
        ]);
        $response->assertStatus(503)->assertJsonPath('pending_confirmation', true);
        $pending = VirtualNumberOrder::where('user_id', $buyer->id)->sole();
        $this->assertSame('pending', $pending->status);
        $this->assertSame('server3', $pending->provider);
        $this->assertSame('test-pvadeals-key', Setting::get('pvadeals_api_key'));
        $this->assertSame(800.0, (float) Wallet::where('user_id', $buyer->id)->value('balance'));
        $this->assertSame(0, WalletTransaction::where('user_id', $buyer->id)->where('type', 'refund')->count());

        Setting::set('pvadeals_webhook_secret', 'test-webhook-secret');
        $this->postJson(route('dashboard.virtual-numbers.status', $pending))
            ->assertOk()
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('phone_number', null);

        $this->postJson(route('pvadeals.webhook', ['token' => 'test-webhook-secret']), [
            'event' => 'number_purchased',
            'timestamp' => now()->toIso8601String(),
            'requestId' => self::REQUEST_ID,
            'serviceId' => self::SERVICE_ID,
            'number' => '+13135550123',
            'message' => '',
        ])->assertOk();

        $this->postJson(route('dashboard.virtual-numbers.status', $pending))
            ->assertOk()
            ->assertJsonPath('status', 'waiting')
            ->assertJsonPath('phone_number', '13135550123');

        $this->assertSame(self::REQUEST_ID, $pending->fresh()->activation_id);
    }

    public function test_server3_webhook_saves_the_full_sms_body_and_requires_its_secret(): void
    {
        Setting::set('pvadeals_webhook_secret', 'test-webhook-secret');
        $buyer = $this->createBuyer(800);
        $order = $this->createServer3Order($buyer, self::REQUEST_ID, 'waiting', 200);
        $payload = [
            'event' => 'sms_received',
            'timestamp' => now()->toIso8601String(),
            'requestId' => self::REQUEST_ID,
            'serviceId' => self::SERVICE_ID,
            'number' => '+13135550123',
            'message' => 'Your Airbnb verification code is 2198.',
        ];

        $this->postJson(route('pvadeals.webhook'), $payload)->assertForbidden();
        $this->postJson(route('pvadeals.webhook', ['token' => 'test-webhook-secret']), $payload)
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertSame('received', $order->fresh()->status);
        $this->assertSame($payload['message'], $order->fresh()->sms_code);
        $this->assertSame(1, Notification::where('user_id', $buyer->id)->count());
    }

    public function test_purchase_webhook_does_not_attach_an_ambiguous_request_to_two_pending_orders(): void
    {
        Setting::set('pvadeals_webhook_secret', 'test-webhook-secret');
        $buyer = $this->createBuyer(1000);
        $first = $this->createServer3Order($buyer, null, 'pending', 200);
        $second = $this->createServer3Order($buyer, null, 'pending', 200);

        $this->postJson(route('pvadeals.webhook', ['token' => 'test-webhook-secret']), [
            'event' => 'number_purchased',
            'timestamp' => now()->toIso8601String(),
            'requestId' => self::REQUEST_ID,
            'serviceId' => self::SERVICE_ID,
            'number' => '+13135550123',
            'message' => '',
        ])->assertStatus(503);

        $this->assertSame('pending', $first->fresh()->status);
        $this->assertSame('pending', $second->fresh()->status);
        $this->assertNull($first->fresh()->activation_id);
        $this->assertNull($second->fresh()->activation_id);
    }

    public function test_server3_cancellation_refunds_only_after_provider_confirms_flagging(): void
    {
        Setting::set('herosms_cancel_refund_pct', '50');
        $buyer = $this->createBuyer(800);
        $order = $this->createServer3Order($buyer, self::REQUEST_ID, 'waiting', 200);
        $flags = Http::sequence()
            ->push(['success' => false, 'message' => 'Flag not allowed', 'status' => 422], 422)
            ->push(['success' => true, 'data' => true, 'message' => 'Number flagged successfully'], 200);
        Http::fake(['prod-v3.pvadeals.com/v3/api/flag/*' => $flags]);

        $this->actingAs($buyer)
            ->postJson(route('dashboard.virtual-numbers.cancel', $order))
            ->assertStatus(422);
        $this->assertSame('waiting', $order->fresh()->status);
        $this->assertSame(800.0, (float) Wallet::where('user_id', $buyer->id)->value('balance'));

        $this->actingAs($buyer)
            ->postJson(route('dashboard.virtual-numbers.cancel', $order))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('refunded', 100);
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(900.0, (float) Wallet::where('user_id', $buyer->id)->value('balance'));
    }

    private function createBuyer(float $balance): User
    {
        $buyer = User::create([
            'name' => 'PVA Buyer',
            'email' => 'pva-buyer-' . bin2hex(random_bytes(4)) . '@example.test',
            'password' => 'password',
            'email_notifications' => false,
        ]);
        $buyer->forceFill(['email_verified_at' => now()])->save();
        Wallet::create(['user_id' => $buyer->id, 'balance' => $balance]);

        return $buyer;
    }

    private function createServer3Order(User $buyer, ?string $requestId, string $status, float $cost): VirtualNumberOrder
    {
        return VirtualNumberOrder::create([
            'user_id' => $buyer->id,
            'activation_id' => $requestId,
            'phone_number' => '13135550123',
            'service' => self::SERVICE_ID,
            'service_name' => 'Airbnb',
            'country' => 0,
            'country_name' => 'United States',
            'cost' => $cost,
            'status' => $status,
            'provider' => 'server3',
            'expires_at' => now()->addMinutes(20),
        ]);
    }

    private function servicesResponse(): array
    {
        return [
            'success' => true,
            'data' => [
                'services' => [
                    [
                        '_id' => self::SERVICE_ID,
                        'name' => 'Airbnb',
                        'country' => 'USA',
                        'STRprice' => 0.2,
                        'LTR3price' => 0.7,
                        'STRstock' => 4,
                        'LTRstock' => 2,
                    ],
                    [
                        '_id' => '697139f7fe5460ddc2f27215',
                        'name' => 'Discord',
                        'country' => 'GB',
                        'STRprice' => 0.3,
                        'STRstock' => 3,
                        'LTRstock' => 1,
                    ],
                ],
            ],
        ];
    }
}
