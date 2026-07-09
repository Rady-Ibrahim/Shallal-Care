<?php

namespace Modules\Auth\Http\Controllers\Api;

use App\Support\MobileFeatures;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class MobileConfigController extends Controller
{
    use ApiResponse;

    public function config(): JsonResponse
    {
        return $this->success(MobileFeatures::publicConfig());
    }
}
