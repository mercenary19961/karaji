<?php

namespace App\Http\Middleware;

use App\Models\Message;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return array_merge(parent::share($request), [
            ...parent::share($request),
            // Locale-dependent props are closures on purpose: share() runs in the
            // web group BEFORE the route middleware (SetShopLocale/SetAdminLocale)
            // sets the per-user locale, so a direct value would capture 'ar' too
            // early. Closures are resolved at render time, after that middleware.
            'name' => fn () => config('brand.name.'.(app()->getLocale() === 'en' ? 'en' : 'ar'), config('app.name')),
            'locale' => fn () => app()->getLocale(),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'impersonating' => $request->session()->has('impersonator_id'),
            'shopUnread' => function () use ($request) {
                $user = $request->user();

                return $user && $user->shop_id
                    ? Message::query()->where('shop_id', $user->shop_id)->whereNull('read_at')->count()
                    : 0;
            },
        ]);
    }
}
