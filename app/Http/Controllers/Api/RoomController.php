<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room; // استيراد موديل Room
use App\Models\Department; // استيراد موديل Department (للوجود)
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // يمكن للمدير وموظف الاستقبال رؤية جميع الغرف
        $this->authorize('viewAny', Room::class);

        $rooms = Room::with('department')->get();
        return response()->json([
            'message' => 'تم جلب الغرف بنجاح.',
            'rooms' => $rooms,
            'status' => 'success'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // يمكن للمدير فقط إضافة غرف جديدة
        $this->authorize('create', Room::class);

        try {
            $request->validate([
                'room_number' => ['required', 'string', 'max:255', 'unique:rooms'],
                'type' => ['required', 'string', 'max:255'],
                'capacity' => ['required', 'integer', 'min:1'],
                'status' => ['nullable', 'string', 'in:available,occupied,maintenance,cleaning'],
                'notes' => ['nullable', 'string'],
                'department_id' => ['nullable', 'exists:departments,id'],
            ]);

            $room = Room::create($request->all());

            return response()->json([
                'message' => 'تم إنشاء الغرفة بنجاح.',
                'room' => $room->load('department'),
                'status' => 'success'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'خطأ في التحقق من صحة البيانات.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ غير متوقع: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Room $room)
    {
        // يمكن للمدير وموظف الاستقبال رؤية تفاصيل الغرفة
        $this->authorize('view', $room);

        return response()->json([
            'message' => 'تم جلب الغرفة بنجاح.',
            'room' => $room->load('department'),
            'status' => 'success'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Room $room)
    {
        // يمكن للمدير وموظف الاستقبال تحديث الغرفة
        $this->authorize('update', $room);

        try {
            $request->validate([
                'room_number' => ['required', 'string', 'max:255', 'unique:rooms,room_number,' . $room->id],
                'type' => ['required', 'string', 'max:255'],
                'capacity' => ['required', 'integer', 'min:1'],
                'status' => ['required', 'string', 'in:available,occupied,maintenance,cleaning'],
                'notes' => ['nullable', 'string'],
                'department_id' => ['nullable', 'exists:departments,id'],
            ]);

            $room->update($request->all());

            return response()->json([
                'message' => 'تم تحديث الغرفة بنجاح.',
                'room' => $room->load('department'),
                'status' => 'success'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'خطأ في التحقق من صحة البيانات.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ غير متوقع: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Room $room)
    {
        // يمكن للمدير فقط حذف الغرف
        $this->authorize('delete', $room);

        try {
            $room->delete();

            return response()->json([
                'message' => 'تم حذف الغرفة بنجاح.',
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء حذف الغرفة: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
}
