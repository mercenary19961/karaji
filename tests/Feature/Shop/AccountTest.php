<?php

namespace Tests\Feature\Shop;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shop = Shop::factory()->create();
        $this->user = User::factory()->create([
            'shop_id' => $this->shop->id,
            'password' => Hash::make('old-password'),
        ]);
    }

    public function test_shop_user_can_view_the_account_page()
    {
        $this->actingAs($this->user)
            ->get('/shop/account')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('shop/account')->where('account.email', $this->user->email));
    }

    public function test_shop_user_can_change_their_password()
    {
        $this->actingAs($this->user)
            ->put('/shop/account/password', [
                'current_password' => 'old-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-password-123', $this->user->fresh()->password));
    }

    public function test_the_current_password_must_be_correct()
    {
        $this->actingAs($this->user)
            ->put('/shop/account/password', [
                'current_password' => 'wrong',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('old-password', $this->user->fresh()->password));
    }

    public function test_the_new_password_must_be_confirmed()
    {
        $this->actingAs($this->user)
            ->put('/shop/account/password', [
                'current_password' => 'old-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'mismatch',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_a_shop_user_can_log_out()
    {
        $this->actingAs($this->user)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_admins_cannot_reach_the_shop_account_page()
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/shop/account')
            ->assertRedirect('/admin');
    }
}
