<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdResource\Pages;
use App\Filament\Resources\AdResource\RelationManagers;
use App\Models\Ad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AdResource extends Resource
{
    protected static ?string $model = Ad::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Anuncio')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('url')
                            ->label('URL de Destino')
                            ->url()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('location')
                            ->label('Ubicación')
                            ->options([
                                'header' => 'Cabecera (Header)',
                                'sidebar_home' => 'Sidebar Inicio',
                                'sidebar_category' => 'Barra Lateral (Categorías)',
                                'sidebar_article' => 'Barra Lateral (Artículos)',
                                'footer' => 'Pie de Página (Footer)',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->default(true),
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Imagen del Banner')
                            ->image()
                            ->directory('ads')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Programación y Estadísticas')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Fecha Inicio'),
                        Forms\Components\DateTimePicker::make('end_date')
                            ->label('Fecha Fin'),
                        Forms\Components\TextInput::make('views')
                            ->label('Vistas')
                            ->disabled()
                            ->numeric(),
                        Forms\Components\TextInput::make('clicks')
                            ->label('Clics')
                            ->disabled()
                            ->numeric(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Imagen'),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Ubicación')
                    ->badge()
                    ->colors([
                        'primary' => 'header',
                        'warning' => 'sidebar_category',
                        'success' => 'sidebar_article',
                        'gray' => 'footer',
                    ]),
                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('views')
                    ->label('Vistas')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('clicks')
                    ->label('Clics')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ctr')
                    ->label('CTR')
                    ->state(function (Ad $record): string {
                        if ($record->views > 0) {
                            return number_format(($record->clicks / $record->views) * 100, 2) . '%';
                        }
                        return '0%';
                    }),
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
            'index' => Pages\ListAds::route('/'),
            'create' => Pages\CreateAd::route('/create'),
            'edit' => Pages\EditAd::route('/{record}/edit'),
        ];
    }
}
