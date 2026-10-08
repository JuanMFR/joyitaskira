<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatus;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['category', 'mainImage']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('mainImage.path')
                    ->label('')
                    ->disk('public')
                    ->square()
                    ->imageSize(56),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(['name', 'code'])
                    ->sortable()
                    ->description(fn (Product $record) => $record->code),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('price')
                    ->label('Precio')
                    ->money('ARS', locale: 'es_AR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(ProductStatus::class),
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name'),
                TernaryFilter::make('is_featured')
                    ->label('Destacado'),
            ])
            ->recordActions([
                ActionGroup::make(self::statusActions())
                    ->label('Estado')
                    ->icon('heroicon-m-arrow-path')
                    ->button()
                    ->outlined()
                    ->size('sm'),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('changeStatus')
                        ->label('Cambiar estado')
                        ->icon('heroicon-m-arrow-path')
                        ->schema([
                            Select::make('status')
                                ->label('Nuevo estado')
                                ->options(ProductStatus::class)
                                ->required(),
                        ])
                        ->action(fn (Collection $records, array $data) => $records->each->update(['status' => $data['status']]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /** Acciones rápidas para pasar un producto a cada estado desde el listado. */
    private static function statusActions(): array
    {
        return array_map(
            fn (ProductStatus $status) => Action::make("status_{$status->value}")
                ->label($status->getLabel())
                ->color($status->getColor())
                ->hidden(fn (Product $record) => $record->status === $status)
                ->action(fn (Product $record) => $record->update(['status' => $status])),
            ProductStatus::cases(),
        );
    }
}
