<?php

namespace Database\Seeders;

use App\Enums\PricePlanEnum;
use App\Models\Category;
use App\Models\PricePlan;
use App\Models\Product;
use App\Models\Step;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StepProductsSeeder extends Seeder
{
    /**
     * Seed the application"s database.
     *
     * @return void
     */
    public function run()
    {
        // Migratieng from categories to steps
        $products = [
            [
                'code' => 'basic',
                'name' => 'Basic',
                'description' => 'Basic / 5 GB / 100min',
                'price' => 10,
            ],
            [
                'code' => 'premium',
                'name' => 'Premium',
                'description' => 'Premium / 20 GB / 500min',
                'price' => 20,
            ],
            [
                'code' => 'traveller',
                'name' => 'Traveller',
                'description' => 'Traveller / Unlimited / 1000min',
                'price' => 30,
            ],
            [
                'code' => '500_uk_minute_bundle',
                'name' => '500 UK minute bundle',
                'description' => null,
                'price' => 10,
            ],
            [
                'code' => 'share_bundle',
                'name' => 'Share bundle',
                'description' => null,
                'price' => 20,
            ],
        ];

        foreach (Tenant::all() as $tenant) {
            $pricePlan = PricePlan::where('code', PricePlanEnum::MONTHLY->name)->first();
            $solutionType = $tenant->solutionTypes()->where('code', 4)->first();
            $step = Step::updateOrCreate(['name' => 'Plans', 'tenant_id' => $tenant->id], [
                'solution_type_id' => $solutionType->id,
            ]);

            foreach ($products as $temp) {
                $product = Product::updateOrCreate([
                    'sku' => $temp['code'],
                    'tenant_id' => $tenant->id,
                ], [
                    'name' => $temp['name'],
                    'description' => $temp['description'],
                    'price_plan_id' => $pricePlan->id,
                    'is_active' => 1,
                ]);

                $price = $product->prices()->updateOrCreate(
                    [
                        'code' => 1,
                        'tenant_id' => $tenant->id,
                    ],
                    [
                        'net' => $temp['price'] ?? 0,
                    ]
                );

                $step->products()->attach($product->id);
            }
        }

        // crate new steps, categories and products
        $cloudSolutionType = $tenant->solutionTypes()->where('code', 7)->first();
        $newSteps = [];
        $steps = [
            'Productivity Apps',
            'Collaboration & Communication',
            'Security & Compliance',
            'Enterprise Suites',
        ];

        foreach ($steps as $step) {
            $newSteps[] = Step::updateOrCreate([
                'name' => $step,
                'tenant_id' => 1,
                'solution_type_id' => $cloudSolutionType->id,
            ]);
        }

        $categories = [
            [
                'category' => 'Office Apps',
                'products' => [],
                'children' => [
                    [
                        'category' => 'Desktop Apps',
                        'products' => [
                            [
                                'name' => 'Microsoft 365 Apps for Business',
                                'steps' => $newSteps[0]->id,
                            ],
                            [
                                'name' => 'Microsoft 365 Apps for Enterprise',
                                'steps' => $newSteps[0]->id,
                            ],
                        ],
                    ],
                    [
                        'category' => 'Web Apps',
                        'products' => [
                            [
                                'name' => 'Office Web Apps',
                                'steps' => $newSteps[0]->id,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'category' => 'Communication Tools',
                'products' => [],
                'children' => [
                    [
                        'category' => 'Email & Calendars',
                        'products' => [
                            [
                                'name' => 'Exchange Online Plan 1',
                                'steps' => $newSteps[1]->id,
                            ],
                            [
                                'name' => 'Exchange Online Plan 2',
                                'steps' => $newSteps[1]->id,
                            ],
                        ],
                    ],
                    [
                        'category' => 'Meetings & Chat',
                        'products' => [
                            [
                                'name' => 'Microsoft Teams Standard',
                                'steps' => $newSteps[1]->id,
                            ],
                            [
                                'name' => 'Teams Phone System Add-on',
                                'steps' => $newSteps[1]->id,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'category' => 'File Storage',
                'products' => [],
                'children' => [
                    [
                        'category' => 'Personal Storage',
                        'products' => [
                            [
                                'name' => 'OneDrive 1TB',
                                'steps' => $newSteps[1]->id,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'category' => 'Extras',
                'products' => [
                    [
                        'name' => 'Loop',
                        'steps' => $newSteps[1]->id,
                    ],
                ],
            ],
            [
                'category' => 'Identity & Access',
                'products' => [],
                'children' => [
                    [
                        'category' => 'Authentication',
                        'products' => [
                            [
                                'name' => 'Azure AD Premium P1',
                                'steps' => $newSteps[2]->id,
                            ],
                            [
                                'name' => 'Azure AD Premium P2',
                                'steps' => $newSteps[2]->id,
                            ],
                        ],
                    ],
                    [
                        'category' => 'Endpoint Management',
                        'products' => [
                            [
                                'name' => 'Intune Device Standard License',
                                'steps' => $newSteps[2]->id,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'category' => 'Bundles',
                'products' => [],
                'children' => [
                    [
                        'category' => 'Business Plans',
                        'products' => [
                            [
                                'name' => 'Microsoft 365 Business Basic',
                                'steps' => $newSteps[3]->id,
                            ],
                            [
                                'name' => 'Microsoft 365 Business Premium',
                                'steps' => $newSteps[3]->id,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($categories as $category) {
            $newCategory = Category::updateOrCreate([
                'code' => str_replace(' ', '_', strtolower($category['category'])),
                'name' => $category['category'],
                'tenant_id' => 1,
            ]);

            if (isset($category['products']) and ! empty($category['products'])) {
                foreach ($category['products'] as $product) {
                    $newProd = Product::updateOrCreate([
                        'sku' => Str::random(12),
                        'name' => $product['name'],
                        'description' => $product['name'],
                        'tenant_id' => 1,
                        'is_active' => 1,
                    ]);

                    $newProd->prices()->updateOrCreate(
                        [
                            'code' => 1,
                            'tenant_id' => $tenant->id,
                        ],
                        [
                            'net' => $newProd['price'] ?? 0,
                        ]
                    );

                    $newProd->categories()->sync($newCategory->id);
                    $newProd->steps()->sync($product['steps']);
                }
            }

            if (isset($category['children']) and ! empty($category['children'])) {
                foreach ($category['children'] as $childCategory) {
                    $newChildCategory = Category::updateOrCreate([
                        'code' => str_replace(' ', '_', strtolower($childCategory['category'])),
                        'name' => $childCategory['category'],
                        'tenant_id' => 1,
                        'parent_id' => $newCategory->id,
                    ]);

                    if (isset($childCategory['products']) and ! empty($childCategory['products'])) {
                        foreach ($childCategory['products'] as $product) {
                            $newProd = Product::updateOrCreate([
                                'sku' => Str::random(12),
                                'name' => $product['name'],
                                'description' => $product['name'],
                                'tenant_id' => 1,
                                'is_active' => 1,
                            ]);

                            $newProd->prices()->updateOrCreate(
                                [
                                    'code' => 1,
                                    'tenant_id' => $tenant->id,
                                ],
                                [
                                    'net' => $newProd['price'] ?? 0,
                                ]
                            );

                            $newProd->categories()->sync($newChildCategory->id);
                            $newProd->steps()->sync($product['steps']);
                        }
                    }
                }
            }
        }
    }
}
