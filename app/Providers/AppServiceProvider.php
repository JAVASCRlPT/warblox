<?php

namespace App\Providers;

use App\Models\Cart;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $cartCount = 0;
            $cartTotal = 0;
            $cartDropdownItems = collect();
            $cartRemainingCount = 0;

            if (auth()->check() && auth()->user()->isMahasiswa()) {
                $cartItems = Cart::with('book')
                    ->where('user_id', auth()->id())
                    ->whereHas('book')
                    ->latest()
                    ->get();

                $cartCount = $cartItems->sum(fn ($item) => max((int) $item->qty, 1));
                $cartTotal = $cartItems->sum(fn ($item) => ($item->book->price ?? 0) * max((int) $item->qty, 1));
                $cartDropdownItems = $cartItems->take(5);
                $displayedQty = $cartDropdownItems->sum(fn ($item) => max((int) $item->qty, 1));
                $cartRemainingCount = max($cartCount - $displayedQty, 0);
            }

            $view->with([
                'cartCount' => $cartCount,
                'cartTotal' => $cartTotal,
                'cartDropdownItems' => $cartDropdownItems,
                'cartRemainingCount' => $cartRemainingCount,
            ]);
        });
    }
}
