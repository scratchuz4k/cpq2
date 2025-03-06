<?php

namespace App\Services;

use App\Models\SolutionType;
use App\Models\Table;
use Illuminate\Support\Facades\DB;

class SolutionTypeService
{

    public function update(SolutionType $solution_type, array $array): SolutionType
    {
        $solution_type->update($array);

        if (array_key_exists('tables', $array)) {
            $olderItems = collect($solution_type->tables);
            foreach ($array['tables'] as $table) {
                $table = $solution_type->tables()->updateOrCreate([
                    'code' => $table['code']
                ], [
                    'group' => isset($table['group']) ? $table['group'] : null,
                    'columns' => $table['columns']
                ]);
                $olderItems = $olderItems->reject(function ($temp) use ($table) {;
                    return $temp->id === $table->id;
                });
            }

            foreach ($olderItems as $temp) {
                $temp->delete();
            }
        }
        if (array_key_exists('configurations', $array) && is_array($array['configurations'])) {
            $solution_type->syncConfigurations($array['configurations']);
        }

        return $solution_type;
    }
}
