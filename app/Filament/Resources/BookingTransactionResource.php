<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingTransactionResource\Pages;
use App\Filament\Resources\BookingTransactionResource\RelationManagers;
use App\Models\BookingTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingTransactionResource extends Resource
{
    protected static ?string $model = BookingTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                ->trueColor('danger')
                ->falseColor('success')
                ->trueIcon('heroicon-o-x-circle')
                ->falseIcon('heroicon-o-check-circle')
                ->label('Paid'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
