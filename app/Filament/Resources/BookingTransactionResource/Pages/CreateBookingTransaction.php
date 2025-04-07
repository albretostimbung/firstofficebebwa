<?php

namespace App\Filament\Resources\BookingTransactionResource\Pages;

use Filament\Actions;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\BookingTransactionResource;

class CreateBookingTransaction extends CreateRecord
{
    protected static string $resource = BookingTransactionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $bookingTransaction = parent::handleRecordCreation($data);
        BookingTransactionResource::clearCache($bookingTransaction);
        return $bookingTransaction;
    }
}
