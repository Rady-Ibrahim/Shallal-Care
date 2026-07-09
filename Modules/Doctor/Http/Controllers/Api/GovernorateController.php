<?php

namespace Modules\Doctor\Http\Controllers\Api;

use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Doctor\Models\Governorate;

class GovernorateController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $items = Governorate::where('is_active', true)
            ->orderBy('name_ar')
            ->get(['id', 'name_ar', 'name_en']);

        return $this->success($items);
    }
}
