<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Placeholder for owner terms agreement (domain logic later).
 */
class TermsController extends Controller
{
    public function show(): View
    {
        return view('admin.terms');
    }
}
