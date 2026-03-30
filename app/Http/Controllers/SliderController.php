<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::ordered()->paginate(10);
        return view('sliders.index', compact('sliders'));
    }

    public function create()
    {
        return view('sliders.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'subtitle' => 'nullable|max:500',
            'tag' => 'nullable|max:100',
            'cta_text' => 'nullable|max:100',
            'cta_link' => 'nullable|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'image_mobile' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'bg_color' => 'nullable|max:20',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['order'] = $validated['order'] ?? 0;
        $validated['cta_text'] = $validated['cta_text'] ?? 'Ver más';
        $validated['cta_link'] = $validated['cta_link'] ?? '/productos';
        $validated['bg_color'] = $validated['bg_color'] ?? '#333333';

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('sliders', 'public');
        }

        if ($request->hasFile('image_mobile')) {
            $validated['image_mobile'] = $request->file('image_mobile')->store('sliders', 'public');
        }

        Slider::create($validated);

        return redirect()
            ->route('sliders.index')
            ->with('success', 'Slider creado exitosamente.');
    }

    public function edit(Slider $slider)
    {
        return view('sliders.edit', compact('slider'));
    }

    public function update(Request $request, Slider $slider)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'subtitle' => 'nullable|max:500',
            'tag' => 'nullable|max:100',
            'cta_text' => 'nullable|max:100',
            'cta_link' => 'nullable|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'image_mobile' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'bg_color' => 'nullable|max:20',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            if ($slider->image) {
                Storage::disk('public')->delete($slider->image);
            }
            $validated['image'] = $request->file('image')->store('sliders', 'public');
        }

        if ($request->boolean('delete_image') && !$request->hasFile('image')) {
            if ($slider->image) {
                Storage::disk('public')->delete($slider->image);
            }
            $validated['image'] = null;
        }

        if ($request->hasFile('image_mobile')) {
            if ($slider->image_mobile) {
                Storage::disk('public')->delete($slider->image_mobile);
            }
            $validated['image_mobile'] = $request->file('image_mobile')->store('sliders', 'public');
        }

        if ($request->boolean('delete_image_mobile') && !$request->hasFile('image_mobile')) {
            if ($slider->image_mobile) {
                Storage::disk('public')->delete($slider->image_mobile);
            }
            $validated['image_mobile'] = null;
        }

        $slider->update($validated);

        return redirect()
            ->route('sliders.index')
            ->with('success', 'Slider actualizado exitosamente.');
    }

    public function destroy(Slider $slider)
    {
        $slider->delete();

        return redirect()
            ->route('sliders.index')
            ->with('success', 'Slider eliminado exitosamente.');
    }
}
