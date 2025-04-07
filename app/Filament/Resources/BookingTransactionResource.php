<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Twilio\Rest\Client;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\BookingTransaction;
use Illuminate\Support\Facades\Cache;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BookingTransactionResource\Pages;
use App\Filament\Resources\BookingTransactionResource\RelationManagers;

class BookingTransactionResource extends Resource
{
    protected static ?string $model = BookingTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function clearCache(BookingTransaction $bookingTransaction): void
    {
        Cache::forget('booking_transaction_' . $bookingTransaction->id);
        Cache::forget('booking_transactions_list');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255)
                    ->required(),

                Forms\Components\TextInput::make('booking_trx_id')
                    ->maxLength(255)
                    ->required(),

                Forms\Components\TextInput::make('phone_number')
                    ->maxLength(255)
                    ->required(),

                Forms\Components\TextInput::make('total_amount')
                    ->numeric()
                    ->prefix('IDR')
                    ->required(),

                Forms\Components\TextInput::make('duration')
                    ->numeric()
                    ->prefix('Days')
                    ->required(),

                Forms\Components\DatePicker::make('started_date')
                    ->required(),

                Forms\Components\DatePicker::make('ended_date')
                    ->required(),

                Forms\Components\Select::make('is_paid')
                    ->options([
                        true => 'Paid',
                        false => 'Not Paid',
                    ])
                    ->required(),

                Forms\Components\Select::make('office_space_id')
                    ->relationship('officeSpace', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_trx_id')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('officeSpace.name'),

                Tables\Columns\TextColumn::make('started_date')
                    ->date(),

                Tables\Columns\IconColumn::make('is_paid')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->label('Paid'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->action(function (BookingTransaction $record) {
                        $record->is_paid = true;
                        $record->save();

                        Notification::make()
                            ->title('Booking Transaction Approved')
                            ->body('The booking transaction has been approved.')
                            ->success()
                            ->send();

                        // kirim sms atau whatsapp
                        $sid = config('twilio.account_sid');
                        $token = config('twilio.auth_token');
                        $twilio = new Client($sid, $token);

                        // Create the message with line breaks
                        $messageBody = "Hi {$record->name}, pemesanan Anda dengan kode {$record->booking_trx_id} sudah terkonfirmasi.\n\n";
                        $messageBody .= "Silahkan datang ke lokasi {$record->officeSpace->name} untuk mulai menggunakan kantor.\n\n";
                        $messageBody .= "Jika ada pertanyaan, silahkan hubungi kami.\n\n";

                        // Send to whatsapp
                        $twilio->messages->create(
                            "whatsapp:+{$record->phone_number}",
                            [
                                "from" => "whatsapp:" . config('twilio.phone_number'),
                                "body" => $messageBody
                            ]
                        );
                    })
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (BookingTransaction $record) => !$record->is_paid),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookingTransactions::route('/'),
            'create' => Pages\CreateBookingTransaction::route('/create'),
            'edit' => Pages\EditBookingTransaction::route('/{record}/edit'),
        ];
    }
}
