<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

/**
 * Placeholder for owner terms agreement (domain logic later).
 */
class TermsController extends Controller
{
    public function show(): View
    {
        $this->authorize('viewOwnerTerms', User::class);

        return view('admin.terms');
    }
}
