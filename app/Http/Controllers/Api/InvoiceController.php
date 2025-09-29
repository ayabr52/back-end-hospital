<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice; // استيراد موديل Invoice
use App\Models\Patient; // استيراد موديل Patient
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth; // لاستخدام المستخدم المصادق عليه

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // يمكن للمدير والمحاسب وموظف الاستقبال رؤية جميع الفواتير
        // المريض يرى فواتيره فقط
        $user = Auth::user();
        $invoices = collect();

        if ($user->role->name === 'admin' || $user->role->name === 'accountant' || $user->role->name === 'receptionist') {
            $invoices = Invoice::with('patient.user', 'issuer')->get();
        } elseif ($user->role->name === 'patient') {
            $patient = $user->patient;
            if ($patient) {
                $invoices = $patient->invoices()->with('patient.user', 'issuer')->get();
            }
        } else {
            return response()->json([
                'message' => 'ليس لديك الصلاحيات الكافية لعرض الفواتير.',
                'status' => 'error'
            ], 403);
        }

        return response()->json([
            'message' => 'تم جلب الفواتير بنجاح.',
            'invoices' => $invoices,
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
        // يمكن للمدير والمحاسب وموظف الاستقبال إنشاء فواتير
        $this->authorize('create', Invoice::class);

        try {
            $request->validate([
                'patient_id' => ['required', 'exists:patients,id'],
                'invoice_number' => ['required', 'string', 'max:255', 'unique:invoices'],
                'total_amount' => ['required', 'numeric', 'min:0'],
                'paid_amount' => ['nullable', 'numeric', 'min:0', 'lte:total_amount'],
                'status' => ['nullable', 'string', 'in:pending,paid,partially_paid,cancelled'],
                'description' => ['nullable', 'string'],
            ]);

            $invoice = Invoice::create([
                'patient_id' => $request->patient_id,
                'issued_by_user_id' => Auth::id(), // الموظف المصادق عليه هو من يصدر الفاتورة
                'invoice_number' => $request->invoice_number,
                'total_amount' => $request->total_amount,
                'paid_amount' => $request->paid_amount ?? 0.00,
                'status' => $request->status ?? 'pending',
                'description' => $request->description,
            ]);

            return response()->json([
                'message' => 'تم إنشاء الفاتورة بنجاح.',
                'invoice' => $invoice->load('patient.user', 'issuer'),
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
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Invoice $invoice)
    {
        // يمكن للمدير، المحاسب، موظف الاستقبال، أو المريض المرتبط رؤية تفاصيل الفاتورة
        $this->authorize('view', $invoice);

        return response()->json([
            'message' => 'تم جلب الفاتورة بنجاح.',
            'invoice' => $invoice->load('patient.user', 'issuer'),
            'status' => 'success'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Invoice $invoice)
    {
        // يمكن للمدير والمحاسب وموظف الاستقبال تحديث الفاتورة
        $this->authorize('update', $invoice);

        try {
            $request->validate([
                'patient_id' => ['required', 'exists:patients,id'],
                'invoice_number' => ['required', 'string', 'max:255', 'unique:invoices,invoice_number,' . $invoice->id],
                'total_amount' => ['required', 'numeric', 'min:0'],
                'paid_amount' => ['nullable', 'numeric', 'min:0', 'lte:total_amount'],
                'status' => ['required', 'string', 'in:pending,paid,partially_paid,cancelled'],
                'description' => ['nullable', 'string'],
            ]);

            $invoice->update($request->all());

            return response()->json([
                'message' => 'تم تحديث الفاتورة بنجاح.',
                'invoice' => $invoice->load('patient.user', 'issuer'),
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
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Invoice $invoice)
    {
        // يمكن للمدير والمحاسب فقط حذف الفواتير
        $this->authorize('delete', $invoice);

        try {
            $invoice->delete();

            return response()->json([
                'message' => 'تم حذف الفاتورة بنجاح.',
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء حذف الفاتورة: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
}
