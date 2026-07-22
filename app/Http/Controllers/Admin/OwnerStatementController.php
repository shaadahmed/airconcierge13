<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

/**
 * Placeholder for owner-statements routes allowed when active-access is restricted.
 */
class OwnerStatementController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewOwnerStatements', User::class);

        return view('admin.owner-statements');
    }
}
