<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\JsonResponse;

class SliderApiController extends Controller
{
    public function index(): JsonResponse
    {
        $sliders = Slider::active()
            ->ordered()
            ->get()
            ->map(function ($slider) {
                return [
                    'id' => $slider->id,
                    'title' => $slider->title,
                    'subtitle' => $slider->subtitle,
                    'tag' => $slider->tag,
                    'cta_text' => $slider->cta_text,
                    'cta_link' => $slider->cta_link,
                    'image_url' => $slider->image_url,
                    'image_mobile_url' => $slider->image_mobile_url,
                    'bg_color' => $slider->bg_color,
                    'order' => $slider->order,
                ];
            });

        return response()->json($sliders);
    }
}
