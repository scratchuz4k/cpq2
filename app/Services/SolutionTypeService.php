<?php

namespace App\Services;

use App\Models\Category;
use App\Models\SolutionType;
use App\Models\SolutionTypeTenant;
use Illuminate\Support\Facades\Auth;

class SolutionTypeService
{
    public function store(array $array)
    {
        $user = Auth::user();
        $solution_type = SolutionType::findOrFail($array['solution_type_id']);

        if (array_key_exists('configurations', $array) && is_array($array['configurations'])) {
            $solution_type->syncConfigurations($array['configurations']);
        }

        $solution_type->tenants()->attach($user->tenant_id);

        $solutionTypeTenant = SolutionTypeTenant::where(['tenant_id' => Auth::user()->tenant_id, 'solution_type_id' => $solution_type->id])->first();
        $solutionTypeTenant->update([
            'estimated_time' => array_key_exists('estimated_time', $array) ? $array['estimated_time'] : $solution_type->estimated_time,
            'is_active' => array_key_exists('is_active', $array) ? $array['is_active'] : $solution_type->is_active,
            'updated_at' => now(),
        ]);

        return $solution_type;
    }

    public function update(SolutionType $solution_type, array $array): SolutionType
    {
        if (array_key_exists('configurations', $array) && is_array($array['configurations'])) {
            $solution_type->syncConfigurations($array['configurations']);
        }

        $solutionTypeTenant = SolutionTypeTenant::where(['tenant_id' => Auth::user()->tenant_id, 'solution_type_id' => $solution_type->id])->first();
        $solutionTypeTenant->update([
            'estimated_time' => array_key_exists('estimated_time', $array) ? $array['estimated_time'] : $solution_type->estimated_time,
            'is_active' => array_key_exists('is_active', $array) ? $array['is_active'] : $solution_type->is_active,
            'updated_at' => now(),
        ]);

        if (array_key_exists('categories', $array) && is_array($array['categories'])) {
            foreach ($array['categories'] as $id => $data) {
                $category = Category::find($id);
                $category->update(['solution_type_id' => $solution_type->id, 'sort_order' => $data['order']]);
            }
        }

        if (array_key_exists('tables', $array)) {
            $olderItems = collect($solution_type->tables);
            foreach ($array['tables'] as $table) {
                $table = $solution_type->tables()->updateOrCreate([
                    'code' => $table['code'],
                ], [
                    'group' => $table['group'] ?? null,
                    'group_name' => $table['group_name'] ?? null,
                    'columns' => $table['columns'],
                ]);
                $olderItems = $olderItems->reject(fn ($temp) => $temp->id === $table->id);
            }
        }

        if (
            array_key_exists('configurations', $array)
            && is_array($array['configurations'])
        ) {
            $solution_type->syncConfigurations($array['configurations']);
        }

        return $solution_type;
    }
}
