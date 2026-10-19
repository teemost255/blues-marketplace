<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Listing;
use App\Models\ListingCredential;
use App\Models\Purchase;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MarketplaceQuantityAndCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_purchase_multiple_local_credentials_in_one_order(): void
    {
        $user = User::create([
            'name' => 'Buyer',
            'email' => 'buyer@example.test',
            'password' => 'password',
            'email_notifications' => false,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        $listing = Listing::create([
            'title' => 'Streaming Accounts',
            'category' => 'streaming',
            'price' => 120,
            'stock' => 0,
            'is_active' => true,
        ]);
        ListingCredential::create(['listing_id' => $listing->id, 'details' => 'account-one@example.test']);
        ListingCredential::create(['listing_id' => $listing->id, 'details' => 'account-two@example.test']);
        $listing->syncStock();
        Wallet::create(['user_id' => $user->id, 'balance' => 500]);
        Setting::set('low_balance_threshold', '0');

        $this->actingAs($user)
            ->get(route('dashboard.marketplace'))
            ->assertOk()
            ->assertSee('name="quantity"', false)
            ->assertSee('max="2"', false);

        $this->post(route('dashboard.marketplace.buy', $listing->id), ['quantity' => 2])
            ->assertRedirect(route('dashboard.orders'));

        $purchase = Purchase::where('user_id', $user->id)->sole();
        $this->assertSame(2, $purchase->quantity);
        $this->assertSame(240.0, (float) $purchase->amount);
        $this->assertSame(260.0, (float) Wallet::where('user_id', $user->id)->value('balance'));
        $this->assertSame(0, $listing->fresh()->stock);
        $this->assertSame(2, ListingCredential::where('purchase_id', $purchase->id)->where('is_used', true)->count());

        $this->get(route('dashboard.orders'))
            ->assertOk()
            ->assertSee('Quantity: 2')
            ->assertSee('account-one@example.test')
            ->assertSee('account-two@example.test');
    }

    public function test_catalog_selection_hides_unselected_products_and_preserves_api_images(): void
    {
        Cache::flush();
        Http::fake([
            'sameehasocialhub.com/api/v1/categories' => Http::response([
                'results' => [['id' => 9, 'name' => 'Streaming']],
            ]),
            'sameehasocialhub.com/api/v1/products*' => Http::response([
                'count' => 2,
                'results' => [
                    [
                        'id' => 77,
                        'name' => 'Selected video plan',
                        'category_id' => 9,
                        'price' => 100,
                        'stock' => 5,
                        'image_url' => 'https://cdn.example.test/video.png',
                    ],
                    [
                        'id' => 88,
                        'name' => 'Hidden catalog plan',
                        'category_id' => 9,
                        'price' => 80,
                        'stock' => 4,
                        'image' => 'https://cdn.example.test/hidden.png',
                    ],
                ],
            ]),
        ]);
        Setting::set('sameeha_api_key', 'test-provider-token');
        Setting::set('api_catalog_enabled', '1');

        $admin = AdminUser::create([
            'email' => 'admin@example.test',
            'password_hash' => 'unused',
            'display_name' => 'Admin',
        ]);
        $this->withSession(['admin_id' => $admin->id])
            ->get(route('admin.api-catalog'))
            ->assertOk()
            ->assertSee('Selected video plan')
            ->assertSee('Hidden catalog plan')
            ->assertSee('name="product_ids[]"', false);

        $this->withSession(['admin_id' => $admin->id])
            ->post(route('admin.api-catalog.update'), ['product_ids' => ['77']])
            ->assertRedirect(route('admin.api-catalog'));

        $this->assertSame([77], Setting::apiCatalogProductIds());

        $buyer = User::create([
            'name' => 'Buyer',
            'email' => 'catalog-buyer@example.test',
            'password' => 'password',
            'email_notifications' => false,
        ]);
        $buyer->forceFill(['email_verified_at' => now()])->save();
        $this->actingAs($buyer)
            ->from(route('dashboard.marketplace'))
            ->post(route('dashboard.marketplace.buy-api', 88), ['quantity' => 1])
            ->assertRedirect(route('dashboard.marketplace'))
            ->assertSessionHas('error');

        $this->actingAs($buyer)
            ->get(route('dashboard.marketplace'))
            ->assertOk()
            ->assertSee('Selected video plan')
            ->assertDontSee('Hidden catalog plan')
            ->assertSee('https://cdn.example.test/video.png');
    }

    public function test_admin_can_see_a_working_whatsapp_support_link(): void
    {
        $admin = AdminUser::create([
            'email' => 'support-admin@example.test',
            'password_hash' => 'unused',
            'display_name' => 'Support Admin',
        ]);
        Setting::set('whatsapp_number', '+234 801-234-5678');

        $this->withSession(['admin_id' => $admin->id])
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('WhatsApp Support')
            ->assertSee('href="https://wa.me/2348012345678"', false);
    }
}
