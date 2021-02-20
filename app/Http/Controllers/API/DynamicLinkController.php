<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DynamicLinkController extends Controller
{
    public function getValue($envVarName)
    {
        return [
            'value' => env($envVarName)
        ];
    }
}
