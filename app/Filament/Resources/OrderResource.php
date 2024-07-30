<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Actions\CreateAction;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    // Change the icon to one that is available
    protected static ?string $navigationIcon = 'heroicon-o-document';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                Repeater::make('order_items')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('quantity')
                            ->required()
                            ->numeric(),
                        TextInput::make('price')
                            ->required()
                            ->numeric(),
                        TextInput::make('image')
                            ->required()
                            ->url(),
                        TextInput::make('selectedSize')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columnSpan('full'),
                TextInput::make('payment_method')
                    ->required()
                    ->maxLength(255),
                TextInput::make('items_price')
                    ->required()
                    ->numeric(),
                TextInput::make('tax_price')
                    ->required()
                    ->numeric(),
                TextInput::make('shipping_price')
                    ->required()
                    ->numeric(),
                TextInput::make('total_price')
                    ->required()
                    ->numeric(),
                Toggle::make('is_paid')
                    ->required(),
                DateTimePicker::make('paid_at'),
                Toggle::make('is_shipped')
                    ->required(),
                DateTimePicker::make('shipped_at'),
                Textarea::make('shipping_address')
                    ->required(),
                Textarea::make('payment_result'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('user.name')->label('User')->sortable()->searchable(),
                TextColumn::make('items_price')->sortable()->searchable(),
                TextColumn::make('tax_price')->sortable()->searchable(),
                TextColumn::make('shipping_price')->sortable()->searchable(),
                TextColumn::make('total_price')->sortable()->searchable(),
                ToggleColumn::make('is_paid')->sortable()->searchable(),
                TextColumn::make('paid_at')->dateTime()->sortable(),
                ToggleColumn::make('is_shipped')->sortable()->searchable(),
                TextColumn::make('shipped_at')->dateTime()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->headerActions([
                CreateAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
