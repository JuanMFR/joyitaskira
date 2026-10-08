<?php

namespace App\Filament\Resources\News\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class NewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('public')
                    ->imageHeight(56),
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->placeholder('—')
                    ->description(fn ($record) => str($record->description)->limit(80)),
                TextColumn::make('link_url')
                    ->label('Link')
                    ->placeholder('Sin link')
                    ->limit(40),
                ToggleColumn::make('is_active')
                    ->label('Visible'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
