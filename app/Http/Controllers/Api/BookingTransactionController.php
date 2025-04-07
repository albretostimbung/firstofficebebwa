<?php

namespace App\Http\Controllers\Api;

use Twilio\Rest\Client;
use App\Models\OfficeSpace;
use Illuminate\Http\Request;
use App\Models\BookingTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\ViewBookingTransactionRequest;
use App\Http\Requests\StoreBookingTransactionRequest;
use App\Http\Resources\Api\BookingTransactionResource;

class BookingTransactionController extends Controller
{
    protected function getJsonCacheKey($key)
    {
        return 'json_' . $key;
    }

    protected function cacheJson($key, $minutes, $callback)
    {
        $jsonKey = $this->getJsonCacheKey($key);

        return Cache::remember($jsonKey, $minutes, function () use ($callback) {
            return json_encode($callback());
        });
    }

    public static function clearCache(BookingTransaction $bookingTransaction): void
    {
        Cache::forget('booking_transaction_' . $bookingTransaction->id);
        Cache::forget('booking_transactions_list');
    }

    public function store(StoreBookingTransactionRequest $request)
    {
        $officeSpace = OfficeSpace::find($request->office_space_id);

        $bookingTransaction = BookingTransaction::create($request->validated() + [
            'is_paid' => false,
            'booking_trx_id' => BookingTransaction::generateUniqueTrxId(),
            'duration' => $officeSpace->duration,
            'ended_date' => (new \DateTime($request->started_date))->modify("+{$officeSpace->duration} days")->format('Y-m-d'),
        ]);

        // kirim sms atau whatsapp
        $sid = config('twilio.account_sid');
        $token = config('twilio.auth_token');
        $twilio = new Client($sid, $token);

        // Create the message with line breaks
        $messageBody = "Hi {$bookingTransaction->name}, Terima kasih telah melakukan pemesanan di First Office.\n\n";
        $messageBody .= "Pesanan kantor {$officeSpace->name} Anda sedang di proses dengan booking ID {$bookingTransaction->booking_trx_id}.\n\n";
        $messageBody .= "Kami akan segera menghubungi Anda untuk konfirmasi pemesanan secepat mungkin.\n\n";

        // Send to whatsapp
        $twilio->messages
            ->create(
                "whatsapp:+{$bookingTransaction->phone_number}",
                [
                    "from" => "whatsapp:" . config('twilio.phone_number'),
                    "body" => $messageBody
                ]
            );

        // kembalikan resultnya
        self::clearCache($bookingTransaction);

        $json = $this->cacheJson('booking_transaction_' . $bookingTransaction->id, 60, function () use ($bookingTransaction) {
            $bookingTransaction->load('officeSpace');

            return BookingTransactionResource::make($bookingTransaction)->toArray(request());
        });

        return response()->json(json_decode($json, true));
    }

    public function bookingDetails(ViewBookingTransactionRequest $request)
    {
        $bookingTransaction = BookingTransaction::where('booking_trx_id', $request->booking_trx_id)
            ->where('phone_number', $request->phone_number)
            ->first();

        if (!$bookingTransaction) {
            return response()->json([
                'message' => 'Booking transaction not found',
            ], 404);
        }

        $json = $this->cacheJson('booking_transaction_' . $bookingTransaction->id, 60, function () use ($bookingTransaction) {
            $bookingTransaction->load(['officeSpace', 'officeSpace.city']);

            return BookingTransactionResource::make($bookingTransaction)->toArray(request());
        });

        return response()->json(json_decode($json, true));
    }
}
