<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SystemSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function __construct(
        protected SystemSettingService $settingService
    ) {}

    /**
     * Get all settings (grouped).
     * GET /api/v1/settings
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->settingService->getAll(),
        ]);
    }

    /**
     * Get settings by group.
     * GET /api/v1/settings/{group}
     */
    public function show(string $group): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->settingService->getGroup($group),
        ]);
    }

    /**
     * Bulk update settings.
     * PUT /api/v1/settings
     *
     * Body: { "currency": "EUR", "currency_symbol": "€", "tax_rate": 7.5 }
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'settings' => ['required', 'array'],
        ]);

        $this->settingService->bulkUpdate($request->settings);

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully.',
        ]);
    }
}
