<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Resolves the final set of group ids to persist for an Account or a User when
 * it is created/edited by $actor, applying the CPQ-530 rules:
 *
 *  - An ungrouped actor may set any groups (or none).
 *  - A grouped actor may only add/remove groups they belong to; group
 *    memberships outside the actor's groups are preserved untouched.
 *  - A grouped actor may not leave the record in zero groups.
 */
class GroupAssignment
{
    /**
     * @param  iterable  $currentIds  group ids the record currently belongs to
     * @param  array|null  $submitted  group ids submitted in the request (null = not supplied)
     * @return array<int, int> group ids to sync
     */
    public static function resolve(User $actor, iterable $currentIds, ?array $submitted, string $label = 'record', ?string $emptyMessage = null): array
    {
        $current = collect($currentIds)->map(fn ($id) => (int) $id)->unique();

        if (! $actor->isGrouped()) {
            // Not supplied → leave as-is; supplied → full replace (may be empty).
            return $submitted === null
                ? $current->values()->all()
                : collect($submitted)->map(fn ($id) => (int) $id)->unique()->values()->all();
        }

        $actorGroups = $actor->groupIds();

        // Groups the actor cannot see/manage are always preserved.
        $preserved = $current->diff($actorGroups);

        if ($submitted === null) {
            // Non-UI caller: floor the record at the actor's groups.
            return $preserved->merge($actorGroups)->unique()->values()->all();
        }

        $chosen = collect($submitted)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->intersect($actorGroups);

        $result = $preserved->merge($chosen)->unique();

        if ($result->isEmpty()) {
            throw ValidationException::withMessages([
                'groups' => $emptyMessage ?? "This {$label} must remain in at least one group.",
            ]);
        }

        return $result->values()->all();
    }

    public static function collect(User $actor, iterable $currentIds, ?array $submitted, string $label = 'record', ?string $emptyMessage = null): Collection
    {
        return collect(self::resolve($actor, $currentIds, $submitted, $label, $emptyMessage));
    }
}
