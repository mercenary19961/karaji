<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminDemoData;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin portal screens fed by AdminDemoData (static demo props) — same
 * contract-first approach as the shop portal. Swapped for real queries
 * at schema v1.
 */
class AdminScreensController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/shops', [
            'shops' => AdminDemoData::shops(),
        ]);
    }

    public function show(): Response
    {
        return Inertia::render('admin/shop-detail', [
            'shop' => AdminDemoData::shopDetail(),
        ]);
    }
}
