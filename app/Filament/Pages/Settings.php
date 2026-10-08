<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Configuración general de la tienda (se guarda en la tabla settings).
 *
 * @property-read Schema $form
 */
class Settings extends Page
{
    protected string $view = 'filament.pages.settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Web';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Configuración';

    protected static ?string $title = 'Configuración';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Setting::allCached());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('WhatsApp')
                    ->description('A este número llegan los pedidos armados desde el carrito.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('whatsapp_number')
                            ->label('Número de WhatsApp')
                            ->helperText('Con código de país y sin espacios ni signos. Ej: 5491122334455')
                            ->tel()
                            ->regex('/^\d{8,15}$/')
                            ->required(),
                        TextInput::make('whatsapp_greeting')
                            ->label('Saludo inicial del mensaje')
                            ->placeholder('¡Hola Kira! Quiero consultar por estos productos:')
                            ->maxLength(255),
                    ]),
                Section::make('Redes y contacto')
                    ->columns(2)
                    ->schema([
                        TextInput::make('instagram_url')
                            ->label('Instagram')
                            ->url()
                            ->placeholder('https://instagram.com/joyitaskira'),
                        TextInput::make('contact_email')
                            ->label('Email de contacto')
                            ->email(),
                    ]),
                Section::make('Textos de la web')
                    ->schema([
                        TextInput::make('announcement')
                            ->label('Aviso superior')
                            ->helperText('Franja fina arriba de todo. Ej: "Envíos a todo el país". Vacío = no se muestra.')
                            ->maxLength(150),
                        Textarea::make('about_text')
                            ->label('Texto "Sobre nosotras"')
                            ->rows(4),
                    ]),
            ]);
    }

    public function save(): void
    {
        Setting::setMany($this->form->getState());

        Notification::make()
            ->success()
            ->title('Configuración guardada')
            ->send();
    }
}
