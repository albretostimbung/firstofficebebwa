<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\OfficeSpace;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OfficeSpaceResource\Pages;
use App\Filament\Resources\OfficeSpaceResource\RelationManagers;

class OfficeSpaceResource extends Resource
{
    protected static ?string $model = OfficeSpace::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state)))
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->hidden(),
                Forms\Components\FileUpload::make('thumbnail')
                    ->image()
                    ->required(),
                Forms\Components\Textarea::make('address')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('is_open')
                    ->options([
                        'true' => 'Open',
                        'false' => 'Closed'
                    ])
                    ->required(),
                Forms\Components\Select::make('is_full_booked')
                    ->options([
                        'true' => 'Full Booked',
                        'false' => 'Not Full Booked'
                    ])
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->live(debounce: 500)
                    ->afterStateUpdated(function ($set, ?string $state) {
                        if ($state !== null) {
                            // Remove any non-numeric characters
                            $cleanValue = preg_replace('/[^\d]/', '', $state);

                            if (is_numeric($cleanValue)) {
                                $set('price', number_format((float)$cleanValue, 0, ',', '.'));
                            }
                        }
                    })
                    ->required()
                    ->placeholder('Enter price in Rp')
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('duration')
                    ->minValue(0)
                    ->numeric()
                    ->suffix('days')
                    ->required(),
                Forms\Components\Textarea::make('about')
                    ->required(),
                Forms\Components\Select::make('city_id')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->options(function () {
                        return \App\Models\City::all()->pluck('name', 'id');
                    })
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListOfficeSpaces::route('/'),
            'create' => Pages\CreateOfficeSpace::route('/create'),
            'edit' => Pages\EditOfficeSpace::route('/{record}/edit'),
        ];
    }
}
