<?php

namespace App\Filament\Pages;

use App\Models\RadioSetting;
use DateTimeZone;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class RadioSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationLabel = 'Configuración del Player';
    protected static ?string $navigationGroup = 'Streaming';
    protected static ?int $navigationSort = 0;
    protected static ?string $title = 'Configuración del Player de Radio';

    protected static string $view = 'filament.pages.radio-settings-page';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(RadioSetting::current()->only([
            'station_name',
            'stream_url',
            'autoplay',
            'timezone',
            'fallback_show_name',
            'fallback_show_host',
        ]));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Emisora')
                    ->schema([
                        Forms\Components\TextInput::make('station_name')
                            ->label('Nombre de la emisora')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('timezone')
                            ->label('Zona horaria')
                            ->options(collect(DateTimeZone::listIdentifiers(DateTimeZone::AMERICA))
                                ->mapWithKeys(fn (string $tz) => [$tz => $tz])
                                ->prepend('UTC', 'UTC'))
                            ->searchable()
                            ->required()
                            ->helperText('Se usa para calcular qué programa está "Ahora" en la parrilla.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Transmisión en vivo')
                    ->schema([
                        Forms\Components\TextInput::make('stream_url')
                            ->label('URL del stream')
                            ->required()
                            ->url()
                            ->maxLength(2048)
                            ->helperText('El servidor de streaming debe permitir CORS para que funcione el visualizador de audio.')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('autoplay')
                            ->label('Intentar reproducir automáticamente al entrar al sitio')
                            ->helperText('El navegador decide: suele permitirlo a oyentes recurrentes y bloquearlo a visitantes nuevos.'),
                    ]),

                Forms\Components\Section::make('Programa por defecto')
                    ->description('Se muestra cuando ningún programa de la parrilla cubre la hora actual.')
                    ->schema([
                        Forms\Components\TextInput::make('fallback_show_name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('fallback_show_host')
                            ->label('Conductor')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        RadioSetting::persist($this->form->getState());

        Notification::make()
            ->title('Configuración guardada')
            ->success()
            ->send();
    }
}
