<?php

namespace App\Http\Controllers\Web;

use App\Actions\Dashboard\GetDashboardOverview;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main operational dashboard.
     */
    public function __invoke(Request $request, GetDashboardOverview $getDashboardOverview): View
    {
        $overview = $getDashboardOverview->handle($request->user());

        return view('dashboard', compact('overview'));
    }
}
