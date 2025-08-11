<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RssFeedResource\Pages;
use App\Models\Category;
use App\Models\RssFeed;
use App\Services\RssImportService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RssFeedResource extends Resource
{
    protected static ?string $model = RssFeed::class;

    protected static ?string $navigationIcon = 'heroicon-o-rss';
    protected static ?string $navigationLabel = 'Fuentes RSS';
    protected static ?string $navigationGroup = 'RSS/Importación';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Fuente RSS';
    protected static ?string $pluralModelLabel = 'Fuentes RSS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Principal')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre de la Fuente')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ej. El País - Noticias')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('url')
                            ->label('URL del RSS')
                            ->required()
                            ->url()
                            ->maxLength(2048)
                            ->placeholder('https://example.com/rss.xml')
                            ->columnSpanFull()
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('test_rss')
                                    ->icon('heroicon-m-signal')
                                    ->tooltip('Probar RSS')
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

                                        try {
                                            $importService = app(RssImportService::class);
                                            $feed = new RssFeed(['url' => $url]);
                                            $result = $importService->importFeed($feed);
                                            
                                            if ($result['success']) {
                                                Notification::make()
                                                    ->title('RSS Test Exitoso')
                                                    ->body("Se encontraron artículos para importar")
                                                    ->success()
                                                    ->send();
                                                
                                                $set('status', RssFeed::STATUS_ACTIVE);
                                            } else {
                                                Notification::make()
                                                    ->title('RSS Test Fallido')
                                                    ->body($result['error'])
                                                    ->danger()
                                                    ->send();
                                            }
                                        } catch (\Exception $e) {
                                            Notification::make()
                                                ->title('Error en Test RSS')
                                                ->body($e->getMessage())
                                                ->danger()
                                                ->send();
                                        }
                                    })
                            ),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('category_id')
                                    ->label('Categoría')
                                    ->required()
                                    ->options(Category::active()->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('status')
                                    ->label('Estado')
                                    ->options(RssFeed::getStatuses())
                                    ->required()
                                    ->default(RssFeed::STATUS_ACTIVE),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('priority')
                                    ->label('Prioridad')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->helperText('1 = Mayor prioridad, 100 = Menor prioridad'),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true)
                                    ->helperText('Activar/desactivar importación automática'),
                            ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuración Avanzada')
                    ->schema([
                        Forms\Components\KeyValue::make('settings')
                            ->label('Configuraciones Adicionales')
                            ->keyLabel('Configuración')
                            ->valueLabel('Valor')
                            ->helperText('Configuraciones específicas para esta fuente RSS'),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\Section::make('Estadísticas')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Placeholder::make('success_count')
                                    ->label('Importaciones Exitosas')
                                    ->content(fn(?RssFeed $record) => $record?->success_count ?? 0),

                                Forms\Components\Placeholder::make('error_count')
                                    ->label('Errores')
                                    ->content(fn(?RssFeed $record) => $record?->error_count ?? 0),

                                Forms\Components\Placeholder::make('success_rate')
                                    ->label('Tasa de Éxito')
                                    ->content(fn(?RssFeed $record) => $record ? $record->success_rate . '%' : '0%'),
                            ]),

                        Forms\Components\Placeholder::make('last_fetched_at')
                            ->label('Última Importación')
                            ->content(fn(?RssFeed $record) => $record?->last_fetched_human ?? 'Nunca'),

                        Forms\Components\Placeholder::make('last_error')
                            ->label('Último Error')
                            ->content(fn(?RssFeed $record) => $record?->last_error ?? 'Ninguno')
                            ->visible(fn(?RssFeed $record) => $record?->last_error),
                    ])
                    ->visibleOn('edit')
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('url')
                    ->label('URL RSS')
                    ->limit(40)
                    ->tooltip(fn(RssFeed $record) => $record->url)
                    ->copyable()
                    ->copyMessage('URL copiada al portapapeles'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
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
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'failed' => 'danger',
                        'testing' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(RssFeed $record) => $record->status_label)
                    ->sortable(),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioridad')
                    ->sortable()
                    ->alignCenter()
                    ->color(fn(int $state): string => match (true) {
                        $state <= 5 => 'success',
                        $state <= 10 => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('success_count')
                    ->label('Éxitos')
                    ->alignEnd()
                    ->sortable()
                    ->color('success'),

                Tables\Columns\TextColumn::make('error_count')
                    ->label('Errores')
                    ->alignEnd()
                    ->sortable()
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('success_rate')
                    ->label('Tasa Éxito')
                    ->formatStateUsing(fn(RssFeed $record) => $record->success_rate . '%')
                    ->color(fn(RssFeed $record): string => match (true) {
                        $record->success_rate >= 90 => 'success',
                        $record->success_rate >= 70 => 'warning',
                        default => 'danger',
                    })
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('last_fetched_at')
                    ->label('Última Importación')
                    ->since()
                    ->sortable()
                    ->placeholder('Nunca')
                    ->tooltip(fn(RssFeed $record) => $record->last_fetched_at?->format('d/m/Y H:i:s')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(RssFeed::getStatuses())
                    ->multiple(),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\Filter::make('is_active')
                    ->label('Solo Activos')
                    ->query(fn(Builder $query): Builder => $query->where('is_active', true))
                    ->toggle(),

                Tables\Filters\Filter::make('has_errors')
                    ->label('Con Errores')
                    ->query(fn(Builder $query): Builder => $query->where('error_count', '>', 0))
                    ->toggle(),

                Tables\Filters\Filter::make('needs_attention')
                    ->label('Requiere Atención')
                    ->query(fn(Builder $query): Builder => 
                        $query->where(function($q) {
                            $q->where('status', RssFeed::STATUS_FAILED)
                              ->orWhere('error_count', '>', 5)
                              ->orWhere(function($subQ) {
                                  $subQ->where('is_active', true)
                                       ->where('last_fetched_at', '<', now()->subDays(2));
                              });
                        })
                    )
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('import')
                    ->label('Importar')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('info')
                    ->action(function (RssFeed $record) {
                        $importService = app(RssImportService::class);
                        $result = $importService->importFeed($record);
                        
                        if ($result['success']) {
                            Notification::make()
                                ->title('Importación Exitosa')
                                ->body("Importados: {$result['imported']}, Omitidos: {$result['skipped']}")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Error en Importación')
                                ->body($result['error'])
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Importar Feed RSS')
                    ->modalDescription(fn(RssFeed $record) => "¿Desea importar noticias desde '{$record->name}'?"),

                Tables\Actions\Action::make('test')
                    ->label('Probar')
                    ->icon('heroicon-m-signal')
                    ->color('warning')
                    ->action(function (RssFeed $record) {
                        try {
                            $importService = app(RssImportService::class);
                            $result = $importService->importFeed($record);
                            
                            if ($result['success']) {
                                Notification::make()
                                    ->title('Test RSS Exitoso')
                                    ->body("Feed funcional - se pueden importar artículos")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Test RSS Fallido')
                                    ->body($result['error'])
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error en Test')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('activate')
                        ->label('Activar')
                        ->icon('heroicon-m-play')
                        ->color('success')
                        ->action(fn(RssFeed $record) => $record->update(['is_active' => true]))
                        ->requiresConfirmation()
                        ->visible(fn(RssFeed $record) => !$record->is_active),

                    Tables\Actions\Action::make('deactivate')
                        ->label('Desactivar')
                        ->icon('heroicon-m-pause')
                        ->color('danger')
                        ->action(fn(RssFeed $record) => $record->update(['is_active' => false]))
                        ->requiresConfirmation()
                        ->visible(fn(RssFeed $record) => $record->is_active),

                    Tables\Actions\Action::make('reset_errors')
                        ->label('Resetear Errores')
                        ->icon('heroicon-m-arrow-path')
                        ->color('warning')
                        ->action(function (RssFeed $record) {
                            $record->update([
                                'error_count' => 0,
                                'last_error' => null,
                                'status' => RssFeed::STATUS_ACTIVE,
                            ]);
                            
                            Notification::make()
                                ->title('Errores Reseteados')
                                ->body('Los errores han sido limpiados')
                                ->success()
                                ->send();
                        })
                        ->visible(fn(RssFeed $record) => $record->error_count > 0),

                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('import_selected')
                        ->label('Importar Seleccionados')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('info')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $importService = app(RssImportService::class);
                            $totalImported = 0;
                            $errors = 0;
                            
                            foreach ($records as $record) {
                                $result = $importService->importFeed($record);
                                if ($result['success']) {
                                    $totalImported += $result['imported'];
                                } else {
                                    $errors++;
                                }
                            }
                            
                            if ($errors === 0) {
                                Notification::make()
                                    ->title('Importación Masiva Exitosa')
                                    ->body("Importados {$totalImported} artículos desde {$records->count()} feeds")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Importación Masiva Completada')
                                    ->body("Importados {$totalImported} artículos, {$errors} feeds con errores")
                                    ->warning()
                                    ->send();
                            }
                        }),

                    Tables\Actions\BulkAction::make('activate_selected')
                        ->label('Activar')
                        ->icon('heroicon-m-play')
                        ->color('success')
                        ->action(fn(\Illuminate\Database\Eloquent\Collection $records) => 
                            $records->each(fn(RssFeed $record) => $record->update(['is_active' => true]))
                        ),

                    Tables\Actions\BulkAction::make('deactivate_selected')
                        ->label('Desactivar')
                        ->icon('heroicon-m-pause')
                        ->color('danger')
                        ->action(fn(\Illuminate\Database\Eloquent\Collection $records) => 
                            $records->each(fn(RssFeed $record) => $record->update(['is_active' => false]))
                        ),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar Feeds RSS Seleccionados')
                        ->modalDescription('¿Está seguro de eliminar estos feeds RSS? Esta acción no se puede deshacer.'),
                ]),
            ])
            ->defaultSort('priority', 'asc')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession();
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
            'index' => Pages\ListRssFeeds::route('/'),
            'create' => Pages\CreateRssFeed::route('/create'),
            'edit' => Pages\EditRssFeed::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $activeCount = static::getModel()::active()->count();
        return $activeCount > 0 ? (string) $activeCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $failedCount = static::getModel()::failed()->count();
        
        return match (true) {
            $failedCount > 0 => 'danger',
            static::getModel()::active()->count() > 0 => 'success',
            default => 'gray',
        };
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['category']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'url', 'category.name'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Categoría' => $record->category?->name,
            'Estado' => $record->status_label,
            'Última Importación' => $record->last_fetched_human,
        ];
    }
}
