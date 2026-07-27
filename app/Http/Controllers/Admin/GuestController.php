<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuestController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Guest::class, 'guest');
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => Guest::query()
            ->where(fn ($query) => $query->where('deleted', false)->orWhereNull('deleted'))
            ->orderByDesc('id')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Guest::class);
        $guest = Guest::query()->create($this->validated($request));

        return response()->json(['data' => $guest], 201);
    }

    public function update(Request $request, Guest $guest): JsonResponse
    {
        $this->authorize('update', $guest);
        $guest->update($this->validated($request));

        return response()->json(['data' => $guest->fresh()]);
    }

    public function destroy(Guest $guest): JsonResponse
    {
        $this->authorize('delete', $guest);
        $guest->update(['deleted' => true]);

        return response()->json(['data' => $guest->fresh()]);
    }

    public function exportEmails(): StreamedResponse
    {
        $this->authorize('viewAny', Guest::class);

        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['email']);
            Guest::query()->whereNotNull('email')
                ->where(fn ($query) => $query->where('deleted', false)->orWhereNull('deleted'))
                ->orderBy('email')->pluck('email')->each(fn (string $email) => fputcsv($output, [$email]));
            fclose($output);
        }, 'guest-emails.csv', ['Content-Type' => 'text/csv']);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'guest_name' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'blacklisted' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('name', $validated)) {
            $validated['guest_name'] = $validated['name'];
            unset($validated['name']);
        }

        return $validated;
    }
}
