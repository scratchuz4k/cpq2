<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;
use App\Models\Quote;
use App\Models\SharedLink;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuoteResource;

class SharedLinkController extends Controller
{
    private $sharedModels = [
        Quote::class =>  'Quotes'
    ];

    public function show(Request $request, $token)
    {
        $sharedLink = SharedLink::where("token", $token)->firstOrFail();

        if ($sharedLink->isExpired()) {
            abort(410, "This link has expired.");
        }

        // Check if a password is required
        if ($sharedLink->password) {
            if (!$request->has("password") || !$sharedLink->verifyPassword($request->input("password"))) {
                abort(403, "Invalid or missing password.");
            }
        }

        $component = $this->sharedModels[$sharedLink->shareable_type];
        $resource = $this->getResource($component, $sharedLink->shareable);

        return Inertia::render("Shareable/$component", [
            'model' => $resource,
        ]);
    }

    private function getResource($component, $item)
    {
        switch ($component) {
            case 'Quotes':
                return new QuoteResource($item->load(['items']));
            default:
                return $item;
        }
    }
}
