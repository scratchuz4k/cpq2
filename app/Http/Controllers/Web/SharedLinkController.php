<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuoteResource;
use App\Models\PricePlan;
use App\Models\Quote;
use App\Models\SharedLink;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SharedLinkController extends Controller
{
    private $sharedModels = [
        Quote::class => 'Quotes',
    ];

    public function show(Request $request, $token)
    {
        $sharedLink = SharedLink::where('token', $token)->firstOrFail();

        $sharedLink->tenant?->makeCurrent();

        if ($sharedLink->isExpired()) {
            abort(410, 'This link has expired and is no longer available.');
        }

        // Check if a password is required
        if ($sharedLink->password) {
            return Inertia::render('Shareable/Index', [
                'isAuthorized' => false,
                'url' => $sharedLink->url,
            ]);
        }

        $component = $this->sharedModels[$sharedLink->shareable_type];
        $resource = $this->getResource($component, $sharedLink->shareable);

        return Inertia::render("Shareable/$component", $resource);
    }

    public function verifyPassword(Request $request, $token)
    {
        $sharedLink = SharedLink::where('token', $token)->firstOrFail();

        if ($sharedLink->isExpired()) {
            abort(410, 'This link has expired.');
        }

        // Check if a password is required
        if ($sharedLink->password) {
            if (! $request->has('password') || ! $sharedLink->verifyPassword($request->input('password'))) {
                return redirect()->back()->withErrors(['Invalid password.']);
            }
        }

        $component = $this->sharedModels[$sharedLink->shareable_type];
        $resource = $this->getResource($component, $sharedLink->shareable);

        return Inertia::render("Shareable/$component", $resource);
    }

    private function getResource($component, $item)
    {
        return match ($component) {
            'Quotes' => [
                'pricePlans' => PricePlan::all(),
                'model' => new QuoteResource($item->load(['items', 'tenant.logo'])),
            ],
            default => $item,
        };
    }
}
