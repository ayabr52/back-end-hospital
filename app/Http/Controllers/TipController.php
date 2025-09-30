<?php

namespace App\Http\Controllers;

use App\Models\Tip;
use Illuminate\Http\Request;

class TipController extends Controller
{
    // عرض جميع النصائح
    public function index()
    {
        $tips = Tip::with('items')->get();
        return response()->json([
            'message' => 'تم جلب النصائح بنجاح.',
            'tips' => $tips,
            'status' => 'success'
        ]);
    }

    // عرض نصيحة واحدة
    public function show(Tip $tip)
    {
        return response()->json([
            'message' => 'تم جلب النصيحة بنجاح.',
            'tip' => $tip->load('items'),
            'status' => 'success'
        ]);
    }

    // إضافة نصيحة جديدة (للطبيب والأدمن فقط)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*' => 'required|string'
        ]);

        $tip = Tip::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            $tip->items()->create(['content' => $item]);
        }

        return response()->json([
            'message' => 'تم إنشاء النصيحة بنجاح.',
            'tip' => $tip->load('items'),
            'status' => 'success'
        ]);
    }

    // تعديل نصيحة موجودة
    public function update(Request $request, Tip $tip)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*' => 'required|string'
        ]);

        $tip->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        // إذا تم إرسال عناصر جديدة، نحذف القديمة ونضيف الجديدة
        if (isset($validated['items'])) {
            $tip->items()->delete();
            foreach ($validated['items'] as $item) {
                $tip->items()->create(['content' => $item]);
            }
        }

        return response()->json([
            'message' => 'تم تحديث النصيحة بنجاح.',
            'tip' => $tip->load('items'),
            'status' => 'success'
        ]);
    }

    // حذف نصيحة
    public function destroy(Tip $tip)
    {
        $tip->items()->delete();
        $tip->delete();

        return response()->json([
            'message' => 'تم حذف النصيحة بنجاح.',
            'status' => 'success'
        ]);
    }
}
