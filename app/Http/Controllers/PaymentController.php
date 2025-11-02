<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Http\Resources\PaymentResource;
use App\Models\Booking;
use App\Models\Payment;
use App\Traits\CommonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class PaymentController extends Controller
{
    use CommonResponse;

    public function create(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'status' => ['required', new Enum(PaymentStatus::class)],
        ]);

        if ($validator->fails()) {
            $this->status = false;
            $this->status_message = 'Validation failed';
            $this->status_code = 422;
            $this->data = $validator->errors();
            return $this->commonApiResponse();
        }
        $booking = Booking::findOrFail($id);

        try {
            DB::beginTransaction();
            $paymentMethod = Payment::create([
                'booking_id' => $booking->id,
                'amount' => $request->input('amount', 0),
                'status' => $request->input('status', PaymentStatus::PENDING),
            ]);
            $this->status_message = 'Payment created successfully';
            $this->data = PaymentResource::make($paymentMethod);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            $this->status = false;
            $this->status_message = 'Failed to create payment';
            $this->status_code = 500;
        }
        return $this->commonApiResponse();
    }

    public function paymentDetails(string $id)
    {
        try {
            $payment = Payment::findOrFail($id);
            $this->status_message = 'Payment details';
            $this->data = PaymentResource::make($payment);

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            $this->status_message = 'Failed to fetch payment details';
            $this->status_code = 500;
            $this->status = false;
        }
        return $this->commonApiResponse();
    }
}
