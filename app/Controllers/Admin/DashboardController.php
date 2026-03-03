<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return $this->view('admin.dashboard.index');
    }
}
