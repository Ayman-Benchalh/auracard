<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Enums\RoleName;
use App\Models\CardDesign;
use App\Models\CommercialPackage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommercialOrderUiTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $otherCustomer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->customer = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->customer->assignRole(RoleName::CUSTOMER);

        $this->otherCustomer = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->otherCustomer->assignRole(RoleName::CUSTOMER);
    }

    public function test_pricing_page_renders_commercial_packages_and_card_designs(): void
    {
        $response = $this->get('/pricing');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Customer/Commercial/Pricing')
            ->has('packages', 4)
            ->has('cardDesigns', 4)
        );
    }

    public function test_authenticated_customer_can_render_checkout_page_for_package(): void
    {
        $soloPackage = CommercialPackage::where('key', 'solo')->firstOrFail();

        $response = $this->actingAs($this->customer)->get("/checkout/package/{$soloPackage->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Customer/Commercial/PackageCheckout')
            ->where('package.key', 'solo')
            ->where('package.physical_cards_included', 1)
            ->has('cardDesigns', 4)
            ->has('paymentSettings')
        );
    }

    public function test_customer_can_submit_order_with_valid_card_selections_and_payment_proof(): void
    {
        Storage::fake('local');

        $soloPackage = CommercialPackage::where('key', 'solo')->firstOrFail();
        $design = CardDesign::where('key', 'matte_black')->firstOrFail();

        $file = UploadedFile::fake()->create('recu_virement.jpg', 400, 'image/jpeg');

        $response = $this->actingAs($this->customer)->post("/checkout/package/{$soloPackage->id}", [
            'card_selections' => [$design->key],
            'payment_method' => 'bank_transfer',
            'reference_number' => 'VIR-ATT-982103',
            'customer_notes' => 'Livraison Agdal Rabat',
            'proof_file' => $file,
        ]);

        $order = Order::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('under_review', $order->status);
        $this->assertEquals(150.00, (float) $order->total_amount);
        $this->assertEquals($design->key, $order->card_selections[0]['key']);

        $response->assertRedirect("/orders/{$order->id}");

        $paymentRequest = $order->paymentRequests()->first();
        $this->assertNotNull($paymentRequest);
        $this->assertEquals('under_review', $paymentRequest->status->value);
        $this->assertEquals('VIR-ATT-982103', $paymentRequest->reference_number);
    }

    public function test_order_submission_validates_card_selections_count(): void
    {
        $trioPackage = CommercialPackage::where('key', 'trio')->firstOrFail();

        // Trio requires 3 cards, pass only 1
        $response = $this->actingAs($this->customer)->post("/checkout/package/{$trioPackage->id}", [
            'card_selections' => ['matte_black'],
            'payment_method' => 'bank_transfer',
            'reference_number' => 'VIR-12345',
            'proof_file' => UploadedFile::fake()->create('receipt.png', 200, 'image/png'),
        ]);

        $response->assertSessionHasErrors('card_selections');
    }

    public function test_customer_can_view_orders_index_and_order_detail(): void
    {
        $soloPackage = CommercialPackage::where('key', 'solo')->firstOrFail();
        $design = CardDesign::where('key', 'metal_gold')->firstOrFail();

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'user_id' => $this->customer->id,
            'commercial_package_id' => $soloPackage->id,
            'total_amount' => 150.00,
            'currency' => 'MAD',
            'status' => 'under_review',
            'package_snapshot' => $soloPackage->toArray(),
            'card_selections' => [$design->key],
            'customer_notes' => 'Test notes',
        ]);

        // Customer views own orders list
        $response = $this->actingAs($this->customer)->get('/orders');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Customer/Commercial/OrdersIndex')
            ->has('orders.data', 1)
        );

        // Customer views own order detail
        $detailResponse = $this->actingAs($this->customer)->get("/orders/{$order->id}");
        $detailResponse->assertStatus(200);
        $detailResponse->assertInertia(fn ($page) => $page
            ->component('Customer/Commercial/OrderDetail')
            ->where('order.order_number', $order->order_number)
            ->where('order.total_amount', 150)
        );

        // Another customer is forbidden from viewing this order
        $forbiddenResponse = $this->actingAs($this->otherCustomer)->get("/orders/{$order->id}");
        $forbiddenResponse->assertStatus(403);
    }
}
