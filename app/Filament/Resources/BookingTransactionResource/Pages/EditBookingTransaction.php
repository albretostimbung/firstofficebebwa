<?php

namespace App\Filament\Resources\BookingTransactionResource\Pages;

use Filament\Actions;
use App\Models\BookingTransaction;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\BookingTransactionResource;

class EditBookingTransaction extends EditRecord
{
    protected static string $resource = BookingTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->before(function (BookingTransaction $bookingTransaction) {
                BookingTransactionResource::clearCache($bookingTransaction);
            }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $bookingTransaction = parent::handleRecordUpdate($record, $data);
        BookingTransactionResource::clearCache($bookingTransaction);
        return $bookingTransaction;
    }
}
