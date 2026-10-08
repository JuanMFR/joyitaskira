<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Datos del producto')
                    ->columnSpan(['default' => 3, 'lg' => 2])
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('code')
                            ->label('Código interno')
                            ->helperText('Opcional. Solo se ve en el panel.')
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        TextInput::make('price')
                            ->label('Precio')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step('0.01')
                            ->prefix('$'),
                        Select::make('category_id')
                            ->label('Categoría')
                            ->relationship('category', 'name', fn ($query) => $query->ordered())
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                            ])
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Estado')
                    ->columnSpan(['default' => 3, 'lg' => 1])
                    ->schema([
                        ToggleButtons::make('status')
                            ->label('Estado')
                            ->options(ProductStatus::class)
                            ->default(ProductStatus::Available)
                            ->required(),
                        Toggle::make('is_featured')
                            ->label('Destacado')
                            ->helperText('Aparece en la sección de destacados del inicio.'),
                    ]),

                Section::make('Fotos')
                    ->description('La foto marcada como principal es la que se ve en el catálogo. Arrastrá para cambiar el orden.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('images')
                            ->hiddenLabel()
                            ->relationship('images')
                            ->orderColumn('sort_order')
                            ->reorderable()
                            ->grid(['default' => 1, 'sm' => 2, 'lg' => 4])
                            ->addActionLabel('Agregar foto')
                            ->defaultItems(0)
                            ->schema([
                                FileUpload::make('path')
                                    ->hiddenLabel()
                                    ->image()
                                    ->disk('public')
                                    ->directory('products')
                                    ->visibility('public')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(10240)
                                    // Se achica en el navegador antes de subir, para que la web cargue rápido.
                                    ->imageResizeMode('contain')
                                    ->imageResizeTargetWidth('1600')
                                    ->imageResizeTargetHeight('1600')
                                    ->imageResizeUpscale(false)
                                    ->imageEditor()
                                    ->required(),
                                Toggle::make('is_main')
                                    ->label('Principal')
                                    ->live()
                                    ->afterStateUpdated(fn (bool $state, Toggle $component, Get $get, Set $set) => self::keepSingleMain($state, $component, $get, $set)),
                            ]),
                    ]),
            ]);
    }

    /** Al marcar una foto como principal, desmarca las demás. */
    private static function keepSingleMain(bool $state, Toggle $component, Get $get, Set $set): void
    {
        if (! $state) {
            return;
        }

        // Ruta del estado: "data.images.{clave}.is_main" → nos quedamos con la clave del ítem actual.
        $currentKey = Str::of($component->getStatePath())->beforeLast('.')->afterLast('.')->toString();

        foreach (array_keys($get('../../images') ?? []) as $key) {
            if ((string) $key !== $currentKey) {
                $set("../../images.{$key}.is_main", false);
            }
        }
    }
}
