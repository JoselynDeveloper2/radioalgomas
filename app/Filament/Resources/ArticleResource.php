<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Filament\Resources\ArticleResource\RelationManagers;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\RssFeed;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Set;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Artículos';
    
    protected static ?string $modelLabel = 'artículo';
    
    protected static ?string $pluralModelLabel = 'artículos';
    
    protected static ?string $navigationGroup = 'Gestión de Contenido';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('main')
                    ->tabs([
                        Tab::make('Contenido')
                            ->schema([
                                Section::make('Información Principal')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('Título')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (string $operation, ?string $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                                        
                                        Forms\Components\TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->helperText('URL amigable del artículo'),
                                        
                                        Forms\Components\Select::make('status')
                                            ->label('Estado')
                                            ->options([
                                                'draft' => 'Borrador',
                                                'published' => 'Publicado',
                                                'scheduled' => 'Programado',
                                                'archived' => 'Archivado',
                                            ])
                                            ->default('draft')
                                            ->required(),
                                        
                                        Forms\Components\DateTimePicker::make('published_at')
                                            ->label('Fecha de Publicación')
                                            ->default(now()),
                                        
                                        Forms\Components\Toggle::make('is_featured')
                                            ->label('Artículo Destacado')
                                            ->default(false),
                                    ])
                                    ->columns(2),
                                
                                Section::make('Contenido del Artículo')
                                    ->schema([
                                        Forms\Components\Textarea::make('excerpt')
                                            ->label('Extracto')
                                            ->maxLength(500)
                                            ->helperText('Resumen corto del artículo (máximo 500 caracteres)')
                                            ->columnSpanFull(),
                                        
                                        RichEditor::make('content')
                                            ->label('Contenido')
                                            ->required()
                                            ->columnSpanFull()
                                            ->fileAttachmentsDirectory('articles')
                                            ->toolbarButtons([
                                                'attachFiles',
                                                'blockquote',
                                                'bold',
                                                'bulletList',
                                                'codeBlock',
                                                'h2',
                                                'h3',
                                                'italic',
                                                'link',
                                                'orderedList',
                                                'redo',
                                                'strike',
                                                'underline',
                                                'undo',
                                            ]),
                                    ]),
                                
                                Section::make('Relaciones')
                                    ->schema([
                                        Forms\Components\Select::make('category_id')
                                            ->label('Categoría')
                                            ->relationship('category', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload(),
                                        
                                        Forms\Components\Select::make('user_id')
                                            ->label('Autor')
                                            ->relationship('user', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->default(auth()->id()),
                                        
                                        Forms\Components\TextInput::make('reading_time')
                                            ->label('Tiempo de Lectura (minutos)')
                                            ->numeric()
                                            ->default(3)
                                            ->helperText('Tiempo estimado de lectura en minutos'),
                                    ])
                                    ->columns(3),
                            ]),
                        
                        Tab::make('Imágenes')
                            ->schema([
                                Section::make('Imagen Destacada')
                                    ->schema([
                                        Forms\Components\FileUpload::make('featured_image')
                                            ->label('Imagen Destacada')
                                            ->image()
                                            ->directory('articles/featured')
                                            ->disk('public')
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([
                                                '16:9',
                                                '4:3',
                                                '1:1',
                                            ])
                                            ->maxSize(5120)
                                            ->helperText('Imagen principal del artículo (máximo 5MB)'),
                                    ]),
                                
                                Section::make('Imagen para Redes Sociales')
                                    ->schema([
                                        Forms\Components\FileUpload::make('og_image')
                                            ->label('Imagen Open Graph')
                                            ->image()
                                            ->directory('articles/og')
                                            ->disk('public')
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([
                                                '1.91:1',
                                            ])
                                            ->maxSize(5120)
                                            ->helperText('Imagen para compartir en redes sociales (1200x630px recomendado)'),
                                    ]),
                            ]),
                        
                        Tab::make('SEO y Metadatos')
                            ->schema([
                                Section::make('Meta Tags')
                                    ->schema([
                                        Forms\Components\TextInput::make('meta_title')
                                            ->label('Título SEO')
                                            ->maxLength(60)
                                            ->helperText('Título optimizado para motores de búsqueda (máximo 60 caracteres)'),
                                        
                                        Forms\Components\Textarea::make('meta_description')
                                            ->label('Descripción SEO')
                                            ->maxLength(160)
                                            ->helperText('Descripción para motores de búsqueda (máximo 160 caracteres)'),
                                        
                                        Forms\Components\TextInput::make('meta_keywords')
                                            ->label('Palabras Clave')
                                            ->maxLength(255)
                                            ->helperText('Palabras clave separadas por comas'),
                                    ]),
                                
                                Section::make('Open Graph (Redes Sociales)')
                                    ->schema([
                                        Forms\Components\TextInput::make('og_title')
                                            ->label('Título Open Graph')
                                            ->maxLength(95)
                                            ->helperText('Título para redes sociales (máximo 95 caracteres)'),
                                        
                                        Forms\Components\Textarea::make('og_description')
                                            ->label('Descripción Open Graph')
                                            ->maxLength(200)
                                            ->helperText('Descripción para redes sociales (máximo 200 caracteres)'),
                                    ]),
                                
                                Section::make('Configuración Avanzada')
                                    ->schema([
                                        Forms\Components\TextInput::make('canonical_url')
                                            ->label('URL Canónica')
                                            ->url()
                                            ->helperText('URL canónica para evitar contenido duplicado'),
                                        
                                        Forms\Components\Textarea::make('schema_markup')
                                            ->label('Schema Markup JSON-LD')
                                            ->helperText('Código JSON-LD para datos estructurados')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        
                        Tab::make('RSS e Importación')
                            ->schema([
                                Section::make('Información RSS')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_imported')
                                            ->label('Artículo Importado')
                                            ->disabled()
                                            ->helperText('Indica si el artículo fue importado desde RSS'),
                                        
                                        Forms\Components\Select::make('rss_feed_id')
                                            ->label('Fuente RSS')
                                            ->relationship('rssFeed', 'name')
                                            ->disabled()
                                            ->visible(fn ($record) => $record?->is_imported),
                                        
                                        Forms\Components\TextInput::make('external_id')
                                            ->label('ID Externo')
                                            ->disabled()
                                            ->visible(fn ($record) => $record?->is_imported)
                                            ->helperText('ID único del artículo en la fuente RSS'),
                                        
                                        Forms\Components\TextInput::make('source_url')
                                            ->label('URL Original')
                                            ->disabled()
                                            ->url()
                                            ->visible(fn ($record) => $record?->is_imported)
                                            ->helperText('URL original del artículo en la fuente RSS'),
                                    ])
                                    ->columns(2)
                                    ->visible(fn ($record) => $record?->is_imported),
                                
                                Section::make('Metadatos de Importación')
                                    ->schema([
                                        Forms\Components\KeyValue::make('import_metadata')
                                            ->label('Metadatos de Importación')
                                            ->disabled()
                                            ->visible(fn ($record) => $record?->is_imported && $record?->import_metadata)
                                            ->helperText('Información adicional sobre la importación'),
                                    ])
                                    ->visible(fn ($record) => $record?->is_imported && $record?->import_metadata),
                            ])
                            ->visible(fn ($record) => $record?->is_imported),
                        
                        Tab::make('Estadísticas')
                            ->schema([
                                Section::make('Métricas del Artículo')
                                    ->schema([
                                        Forms\Components\TextInput::make('views_count')
                                            ->label('Total de Visualizaciones')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->helperText('Número total de vistas del artículo'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->label('Imagen')
                    ->circular()
                    ->size(50)
                    ->defaultImageUrl(function ($record) {
                        return \App\Helpers\ImageHelper::getPlaceholderImage($record->category->name ?? 'General', 100, 100);
                    }),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'secondary' => 'draft',
                        'success' => 'published',
                        'warning' => 'scheduled',
                        'danger' => 'archived',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Borrador',
                        'published' => 'Publicado',
                        'scheduled' => 'Programado',
                        'archived' => 'Archivado',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->color(fn ($record) => 'primary')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_imported')
                    ->label('RSS')
                    ->boolean()
                    ->trueIcon('heroicon-o-rss')
                    ->falseIcon('heroicon-o-pencil')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->tooltip(fn ($record) => $record->is_imported ? 'Importado desde RSS' : 'Creado manualmente')
                    ->alignCenter()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('rssFeed.name')
                    ->label('Fuente RSS')
                    ->badge()
                    ->color('info')
                    ->visible(fn ($record) => $record?->is_imported)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Autor')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Fecha de Publicación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color(fn ($record) => $record->published_at && $record->published_at->isFuture() ? 'warning' : 'primary'),
                
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Vistas')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('reading_time')
                    ->label('Lectura')
                    ->formatStateUsing(fn ($state) => $state . ' min')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'published' => 'Publicado',
                        'scheduled' => 'Programado',
                        'archived' => 'Archivado',
                    ]),
                
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('is_featured')
                    ->label('Solo Destacados')
                    ->query(fn (Builder $query): Builder => $query->where('is_featured', true)),
                
                Tables\Filters\Filter::make('published')
                    ->label('Solo Publicados')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'published')),
                
                Tables\Filters\Filter::make('imported')
                    ->label('Solo RSS Importados')
                    ->query(fn (Builder $query): Builder => $query->where('is_imported', true)),
                
                Tables\Filters\Filter::make('manual')
                    ->label('Solo Manuales')
                    ->query(fn (Builder $query): Builder => $query->where('is_imported', false)),
                
                Tables\Filters\SelectFilter::make('rss_feed')
                    ->label('Fuente RSS')
                    ->relationship('rssFeed', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->url(fn ($record) => route('blog.show', $record->slug))
                    ->openUrlInNewTab(),
                
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
                
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados'),
                    
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publicar seleccionados')
                        ->icon('heroicon-o-eye')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'published']);
                            });
                        })
                        ->requiresConfirmation()
                        ->color('success'),
                    
                    Tables\Actions\BulkAction::make('draft')
                        ->label('Marcar como borrador')
                        ->icon('heroicon-o-pencil')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'draft']);
                            });
                        })
                        ->requiresConfirmation()
                        ->color('warning'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
