<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlayerUrlResource\Pages;
use App\Models\PlayerUrl;
use App\Services\StreamTestService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PlayerUrlResource extends Resource
{
    protected static ?string $model = PlayerUrl::class;

    protected static ?string $navigationIcon = 'heroicon-o-play';

    protected static ?string $navigationLabel = 'URLs del Player';

    protected static ?string $modelLabel = 'URL del Player';

    protected static ?string $pluralModelLabel = 'URLs del Player';

    protected static ?string $navigationGroup = 'Streaming';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Principal')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ej. Stream Principal, Stream de Respaldo')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('url')
                            ->label('URL del Stream')
                            ->required()
                            ->url()
                            ->maxLength(2048)
                            ->placeholder('https://example.com/stream.m3u8')
                            ->columnSpanFull()
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('test')
                                    ->icon('heroicon-m-signal')
                                    ->tooltip('Probar conexión')
                                    ->action(function (Forms\Get $get, Forms\Set $set) {
                                        $url = $get('url');
                                        if (!$url) {
                                            Notification::make()
                                                ->title('Error')
                                                ->body('Debe ingresar una URL antes de probar')
                                                ->danger()
                                                ->send();
                                            return;
                                        }

                                        $testService = app(StreamTestService::class);
                                        $result = $testService->testUrl($url);
                                        
                                        if ($result['success']) {
                                            $set('test_result', $result['message']);
                                            $set('test_response_time', $result['response_time']);
                                            $set('last_tested_at', now());
                                            
                                            Notification::make()
                                                ->title('Test Exitoso')
                                                ->body("Conexión exitosa ({$result['response_time']} ms)")
                                                ->success()
                                                ->send();
                                        } else {
                                            $set('test_result', $result['message']);
                                            $set('test_response_time', $result['response_time']);
                                            $set('last_tested_at', now());
                                            
                                            Notification::make()
                                                ->title('Test Fallido')
                                                ->body($result['message'])
                                                ->danger()
                                                ->send();
                                        }
                                    })
                            ),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Estado')
                                    ->options(PlayerUrl::getStatuses())
                                    ->required()
                                    ->default(PlayerUrl::STATUS_INACTIVE)
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                                        if ($state === PlayerUrl::STATUS_ACTIVE) {
                                            $set('is_active', true);
                                        } elseif ($state === PlayerUrl::STATUS_INACTIVE) {
                                            $set('is_active', false);
                                        }
                                    }),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Activa')
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                                        if ($state) {
                                            $set('status', PlayerUrl::STATUS_ACTIVE);
                                        } else {
                                            $set('status', PlayerUrl::STATUS_INACTIVE);
                                        }
                                    })
                                    ->helperText('Solo una URL puede estar activa a la vez'),
                            ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Información de Testing')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('last_tested_at')
                                    ->label('Último Test')
                                    ->disabled()
                                    ->displayFormat('d/m/Y H:i:s'),

                                Forms\Components\TextInput::make('test_response_time')
                                    ->label('Tiempo de Respuesta (ms)')
                                    ->disabled()
                                    ->suffix('ms'),
                            ]),

                        Forms\Components\Textarea::make('test_result')
                            ->label('Resultado del Test')
                            ->disabled()
                            ->rows(2),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Información Adicional')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->placeholder('Notas adicionales sobre esta URL...'),

                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metadatos')
                            ->keyLabel('Clave')
                            ->valueLabel('Valor')
                            ->helperText('Información adicional como formato, bitrate, etc.'),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn (PlayerUrl $record) => $record->url)
                    ->copyable()
                    ->copyMessage('URL copiada al portapapeles'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'testing' => 'warning',
                        'inactive' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (PlayerUrl $record) => $record->status_label)
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_tested_at')
                    ->label('Último Test')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Sin probar')
                    ->tooltip(fn (PlayerUrl $record) => $record->last_tested_human),

                Tables\Columns\TextColumn::make('test_response_time')
                    ->label('Tiempo Resp.')
                    ->formatStateUsing(fn (?int $state) => $state ? $state . ' ms' : 'N/A')
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('test_status')
                    ->label('Estado Test')
                    ->badge()
                    ->color(fn (PlayerUrl $record): string => match ($record->test_status) {
                        'Test exitoso' => 'success',
                        'Test fallido' => 'danger',
                        'Sin probar' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(PlayerUrl::getStatuses())
                    ->multiple(),

                Tables\Filters\Filter::make('is_active')
                    ->label('Solo URLs Activas')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->toggle(),

                Tables\Filters\Filter::make('recently_tested')
                    ->label('Recientemente Probadas')
                    ->query(fn (Builder $query): Builder => $query->where('last_tested_at', '>=', now()->subHour()))
                    ->toggle(),

                Tables\Filters\Filter::make('needs_testing')
                    ->label('Necesitan Prueba')
                    ->query(fn (Builder $query): Builder => $query->where(function ($q) {
                        $q->whereNull('last_tested_at')
                          ->orWhere('last_tested_at', '<', now()->subHour());
                    }))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('test')
                    ->label('Probar')
                    ->icon('heroicon-m-signal')
                    ->color('info')
                    ->action(function (PlayerUrl $record) {
                        $testService = app(StreamTestService::class);
                        $result = $testService->testPlayerUrl($record);
                        
                        if ($result['success']) {
                            Notification::make()
                                ->title('Test Exitoso')
                                ->body("Conexión exitosa ({$result['response_time']} ms)")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Test Fallido')
                                ->body($result['message'])
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation(false),

                Tables\Actions\Action::make('activate')
                    ->label('Activar')
                    ->icon('heroicon-m-play')
                    ->color('success')
                    ->action(function (PlayerUrl $record) {
                        $record->activate();
                        
                        Notification::make()
                            ->title('URL Activada')
                            ->body("La URL '{$record->name}' ahora es la activa")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Activar URL')
                    ->modalDescription(fn (PlayerUrl $record) => "¿Estás seguro de que deseas activar '{$record->name}'? Esto desactivará la URL actualmente en uso.")
                    ->visible(fn (PlayerUrl $record) => !$record->is_active),

                Tables\Actions\Action::make('deactivate')
                    ->label('Desactivar')
                    ->icon('heroicon-m-pause')
                    ->color('warning')
                    ->action(function (PlayerUrl $record) {
                        $record->deactivate();
                        
                        Notification::make()
                            ->title('URL Desactivada')
                            ->body("La URL '{$record->name}' ha sido desactivada")
                            ->warning()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Desactivar URL')
                    ->modalDescription(fn (PlayerUrl $record) => "¿Estás seguro de que deseas desactivar '{$record->name}'? Esto dejará el sitio sin stream activo.")
                    ->visible(fn (PlayerUrl $record) => $record->is_active),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('test_selected')
                        ->label('Probar Seleccionadas')
                        ->icon('heroicon-m-signal')
                        ->color('info')
                        ->action(function (Collection $records) {
                            $testService = app(StreamTestService::class);
                            $results = [];
                            
                            foreach ($records as $record) {
                                $result = $testService->testPlayerUrl($record);
                                $results[] = $result['success'];
                            }
                            
                            $successCount = count(array_filter($results));
                            $totalCount = count($results);
                            
                            if ($successCount === $totalCount) {
                                Notification::make()
                                    ->title('Tests Completados')
                                    ->body("Todas las URLs ({$totalCount}) pasaron el test exitosamente")
                                    ->success()
                                    ->send();
                            } else {
                                $failedCount = $totalCount - $successCount;
                                Notification::make()
                                    ->title('Tests Completados')
                                    ->body("{$successCount} exitosas, {$failedCount} fallidas de {$totalCount} URLs")
                                    ->warning()
                                    ->send();
                            }
                        }),

                    Tables\Actions\BulkAction::make('change_status')
                        ->label('Cambiar Estado')
                        ->icon('heroicon-m-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Nuevo Estado')
                                ->options(PlayerUrl::getStatuses())
                                ->required(),
                        ])
                        ->action(function (array $data, Collection $records) {
                            $records->each(function (PlayerUrl $record) use ($data) {
                                $record->update(['status' => $data['status']]);
                                
                                if ($data['status'] === PlayerUrl::STATUS_INACTIVE) {
                                    $record->update(['is_active' => false]);
                                }
                            });
                            
                            Notification::make()
                                ->title('Estados Actualizados')
                                ->body("Se actualizaron {$records->count()} URLs")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar URLs Seleccionadas')
                        ->modalDescription('¿Estás seguro de que deseas eliminar las URLs seleccionadas? Esta acción no se puede deshacer.')
                        ->action(function (Collection $records) {
                            $activeCount = $records->where('is_active', true)->count();
                            $records->each->delete();
                            
                            if ($activeCount > 0) {
                                Notification::make()
                                    ->title('URLs Eliminadas')
                                    ->body("Se eliminaron {$records->count()} URLs. ATENCIÓN: Se eliminó(ron) {$activeCount} URL(s) activa(s).")
                                    ->warning()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('URLs Eliminadas')
                                    ->body("Se eliminaron {$records->count()} URLs exitosamente")
                                    ->success()
                                    ->send();
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlayerUrls::route('/'),
            'create' => Pages\CreatePlayerUrl::route('/create'),
            'edit' => Pages\EditPlayerUrl::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $activeCount = static::getModel()::active()->count();
        return $activeCount > 0 ? (string) $activeCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $activeCount = static::getModel()::active()->count();
        
        return match (true) {
            $activeCount === 0 => 'danger',
            $activeCount === 1 => 'success',
            $activeCount > 1 => 'warning',
            default => null,
        };
    }
}
