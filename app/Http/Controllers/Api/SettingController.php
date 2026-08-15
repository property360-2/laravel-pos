<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        foreach ($request->array('settings') as $key => $value) {
            Setting::set($key, $value);
        }

        return response()->json(Setting::allAsArray());
    }
}
