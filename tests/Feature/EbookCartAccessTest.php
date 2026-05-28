<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EbookCartAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_when_accessing_cart(): void
    {
        $response = $this->get('/ebook/cart');

        $response->assertRedirect('/login');
    }

    public function test_mahasiswa_can_access_cart_page(): void
    {
        $user = User::factory()->create([
            'role' => 'mahasiswa',
        ]);

        $response = $this->actingAs($user)->get('/ebook/cart');

        $response->assertOk();
        $response->assertSee('Keranjang eBook');
    }

    public function test_cart_trailing_slash_redirects_to_canonical_path(): void
    {
        $user = User::factory()->create([
            'role' => 'mahasiswa',
        ]);

        $response = $this->actingAs($user)->get('/ebook/cart/');

        $response->assertRedirect('/ebook/cart');
    }
}
