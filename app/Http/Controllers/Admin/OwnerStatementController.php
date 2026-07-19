<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Placeholder for owner-statements routes allowed when active-access is restricted.
 */
class OwnerStatementController extends Controller
{
    public function index(): View
    {
        return view('admin.owner-statements');
    }
}
