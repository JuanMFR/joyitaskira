<?php

namespace App\Filament\Resources\News\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NewsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('image')
                            ->label('Imagen')
                            ->helperText('Ideal horizontal, 1920 × 800 px aprox.')
                            ->image()
                            ->disk('public')
                            ->directory('news')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(10240)
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth('2400')
                            ->imageResizeTargetHeight('2400')
                            ->imageResizeUpscale(false)
                            ->imageEditor()
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(120)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->required()
                            ->rows(3)
                            ->maxLength(300)
                            ->columnSpanFull(),
                        TextInput::make('link_url')
                            ->label('Link (opcional)')
                            ->helperText('Si lo completás, la noticia se puede clickear. Ej: /catalogo, /producto/aros-luna o https://instagram.com/...')
                            // Solo links de la propia web (/...) o direcciones http(s).
                            ->regex('#^(/|https?://)#i')
                            ->validationMessages(['regex' => 'Tiene que empezar con / (una página de la web) o con https://'])
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('sort_order')
                            ->label('Orden')
                            ->helperText('Menor número = aparece primero.')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Visible en la web')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
