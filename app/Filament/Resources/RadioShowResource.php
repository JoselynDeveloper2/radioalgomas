<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RadioShowResource\Pages;
use App\Models\RadioShow;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RadioShowResource extends Resource
{
    protected static ?string $model = RadioShow::class;

    protected static ?string $navigationIcon = 'heroicon-o-radio';
    protected static ?string $navigationLabel = 'Programación';
    protected static ?string $navigationGroup = 'Streaming';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Programa';
    protected static ?string $pluralModelLabel = 'Programación';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del programa')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('host')
                    ->label('Conductor')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TimePicker::make('start_time')
                            ->label('Hora de inicio')
                            ->seconds(false)
                            ->required(),

                        Forms\Components\TimePicker::make('end_time')
                            ->label('Hora de fin')
                            ->seconds(false)
                            ->required()
                            ->after('start_time'),
                    ]),

                Forms\Components\Toggle::make('is_active')
                    ->label('Visible en la parrilla')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Inicio')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Fin')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Programa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('host')
                    ->label('Conductor')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Visible')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('start_time')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRadioShows::route('/'),
            'create' => Pages\CreateRadioShow::route('/create'),
            'edit' => Pages\EditRadioShow::route('/{record}/edit'),
        ];
    }
}
