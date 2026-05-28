<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuoteResource;
use App\Models\PricePlan;
use App\Models\Quote;
use App\Models\QuoteApproval;
use App\Models\SharedLink;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SharedLinkController extends Controller
{
    private $sharedModels = [
        Quote::class => 'Quotes',
        QuoteApproval::class => 'QuoteApprovals',
    ];

    public function show(Request $request, $token)
    {
        try {
            $sharedLink = SharedLink::where('token', $token)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            abort(404, 'The quote you are looking for could not be found.');
        } catch (\Exception $e) {
            return handleWebException($e);
        }

        $sharedLink->tenant?->makeCurrent();

        if ($sharedLink->isExpired()) {
            abort(410, 'This link has expired and is no longer available.');
        }

        $component = $this->sharedModels[$sharedLink->shareable_type];
        $configs = $sharedLink->tenant?->configs ?? [];

        // Check if a password is required
        if ($sharedLink->password) {
            return Inertia::render('Shareable/Index', [
                'isAuthorized' => false,
                'url' => $sharedLink->url,
                'shareableType' => $component === 'QuoteApprovals' ? 'quote-approval' : 'shared-link',
                'customization' => $this->getCustomizationColors($configs),
            ]);
        }

        $resource = $this->getResource($component, $sharedLink->shareable);

        return Inertia::render("Shareable/$component", $resource);
    }

    public function verifyPassword(Request $request, $token)
    {
        $sharedLink = SharedLink::where('token', $token)->firstOrFail();

        $sharedLink->tenant?->makeCurrent();

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
        $tenant = $component == 'QuoteApprovals' ? $item->quote->tenant : $item->tenant;

        if ($tenant !== Tenant::current()) {
            $temp = Tenant::findOrFail($tenant->id);
            $temp->makeCurrent();
            $tenant = $temp;
        }

        $configs = $tenant->configs ?? [];

        return match ($component) {
            'Quotes' => [
                'pricePlans' => PricePlan::all(),
                'model' => new QuoteResource($item->load(['items', 'tenant.logo', 'tenant.configurations'])),
                'customization' => [
                    'colors' => json_decode(array_key_exists('customization_colors', $configs) ? $configs['customization_colors'] : '[]'),
                ],
                'shareableType' => 'shared-link',
            ],

            'QuoteApprovals' => [
                'pricePlans' => PricePlan::all(),
                'model' => new QuoteResource($item->quote->load(['items', 'tenant.logo', 'tenant.configurations'])),
                'customization' => $this->getCustomizationColors($configs),
                'shareableType' => 'quote-approval',
            ],
            default => $item,
        };
    }

    private function getCustomizationColors(array $configs)
    {
        return [
            'colors' => json_decode(array_key_exists('customization_colors', $configs) ? $configs['customization_colors'] : '[]'),
        ];
    }
}
