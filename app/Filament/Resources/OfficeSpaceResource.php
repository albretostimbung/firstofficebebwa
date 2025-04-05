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

                Forms\Components\FileUpload::make('thumbnail')
                    ->image()
                    ->required(),

                Forms\Components\Textarea::make('address')
                    ->rows(10)
                    ->cols(20)
                    ->required(),


                Forms\Components\Textarea::make('about')
                    ->rows(10)
                    ->cols(20)
                    ->required(),

                Forms\Components\Repeater::make('photos')
                    ->relationship('photos')
                    ->schema([
                        Forms\Components\FileUpload::make('photo')
                            ->image()
                            ->required(),
                    ])
                    ->required(),

                Forms\Components\Repeater::make('benefits')
                    ->relationship('benefits')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                    ])
                    ->required(),

                Forms\Components\Select::make('city_id')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->prefix('IDR')
                    ->required(),

                Forms\Components\TextInput::make('duration')
                    ->numeric()
                    ->prefix('Days')
                    ->required(),

                Forms\Components\Select::make('is_open')
                    ->options([
                        true => 'Open',
                        false => 'Closed',
                    ])
                    ->required(),

                Forms\Components\Select::make('is_full_booked')
                    ->options([
                        true => 'Not Available',
                        false => 'Available',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\ImageColumn::make('thumbnail'),

                Tables\Columns\TextColumn::make('city.name'),

                Tables\Columns\IconColumn::make('is_full_booked')
                ->boolean()
                ->trueColor('danger')
                ->falseColor('success')
                ->trueIcon('heroicon-o-x-circle')
                ->falseIcon('heroicon-o-check-circle')
                ->label('Available'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('city_id')
                ->relationship('city', 'name')
                ->label('City'),
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
