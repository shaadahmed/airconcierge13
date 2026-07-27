<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DynamicContent;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('manageContent', User::class);

        return response()->json([
            'data' => DynamicContent::query()->orderBy('page_id')->get(),
        ]);
    }

    public function update(Request $request, string $pageId): JsonResponse
    {
        $this->authorize('manageContent', User::class);

        $content = DynamicContent::query()->updateOrCreate(
            ['page_id' => $pageId],
            $request->validate(['content' => ['required', 'string']])
        );

        return response()->json(['data' => $content]);
    }
}
