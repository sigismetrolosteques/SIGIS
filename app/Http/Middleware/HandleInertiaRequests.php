<?php

namespace App\Http\Middleware;

use App\Services\AlertSummary;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => optional($request->user(), function ($user) {
                    return $user->loadMissing(['roles:id,name', 'gerencia:id,name']);
                }),
            ],
            'errors' => function () use ($request) {
                return $request->session()->get('errors')
                    ? $request->session()->get('errors')->getBag('default')->getMessages()
                    : (object) [];
            },
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'showAlertsModal' => fn () => $request->session()->get('show_alerts_modal'),
            ],
            'alerts' => function () use ($request) {
                return app(AlertSummary::class)->forUser($request->user());
            },
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
        ]);
    }
}
