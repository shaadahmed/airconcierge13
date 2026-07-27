<?php

namespace App\Services\Navigation;

use App\Enums\UserRole;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Support\Collection;

final class AdminNavigationService
{
    /**
     * Build the six-group admin sidebar for the authenticated user.
     *
     * @return list<array<string, mixed>>
     */
    public function forUser(User $user): array
    {
        $role = $user->role?->value;

        if (! in_array($role, [
            UserRole::SuperAdmin->value,
            UserRole::Admin->value,
            UserRole::Manager->value,
        ], true)) {
            return [];
        }

        $groups = $this->resourceGroups();

        $administration = $this->administrationGroup($role);
        if ($administration !== null) {
            $groups[] = $administration;
        }

        return $groups;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function resourceGroups(): array
    {
        /** @var Collection<int, Resource> $parents */
        $parents = Resource::query()
            ->topLevel()
            ->with(['children' => fn ($query) => $query->notDeleted()->orderBy('order')])
            ->get();

        $iconMap = config('admin_navigation.icon_map', []);

        return $parents
            ->map(function (Resource $parent) use ($iconMap): array {
                $children = $parent->children
                    ->filter(fn (Resource $child): bool => $child->spaPath() !== null)
                    ->values()
                    ->map(fn (Resource $child): array => [
                        'id' => $child->id,
                        'title' => $child->name,
                        'to' => $child->spaPath(),
                        'icon' => 'bxs-circle',
                    ])
                    ->all();

                return [
                    'id' => $parent->id,
                    'title' => $parent->name,
                    'icon' => $iconMap[$parent->icon_class] ?? 'bx-folder',
                    'children' => $children,
                ];
            })
            ->filter(fn (array $group): bool => $group['children'] !== [])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function administrationGroup(?string $role): ?array
    {
        $config = config('admin_navigation.administration');

        if (! is_array($config) || $role === null) {
            return null;
        }

        if (! in_array($role, $config['roles'] ?? [], true)) {
            return null;
        }

        $children = collect($config['children'] ?? [])
            ->filter(fn (array $item): bool => in_array($role, $item['roles'] ?? [], true))
            ->map(fn (array $item): array => array_filter([
                'title' => $item['title'],
                'to' => $item['to'],
                'icon' => 'bxs-circle',
                'badgeContent' => $item['badgeContent'] ?? null,
                'badgeClass' => $item['badgeClass'] ?? null,
            ], fn ($value) => $value !== null))
            ->values()
            ->all();

        if ($children === []) {
            return null;
        }

        return [
            'id' => 'administration',
            'title' => $config['title'],
            'icon' => $config['icon'] ?? 'bx-cog',
            'children' => $children,
        ];
    }
}
