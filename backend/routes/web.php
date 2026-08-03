<?php

use Illuminate\Support\Facades\Route;
use App\Support\QiHelp;

Route::get('/', function () {
    return QiHelp::apiResponse([
        'system' => 'Document Approval API Engine',
        'status' => 'Operational',
    ], 'Qi Platform Service Active');
});
