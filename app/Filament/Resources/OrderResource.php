<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use App\Notifications\OrderPaidNotification;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

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
                TextInput::make('payment_result'),
                DateTimePicker::make('shipped_at'),
                DateTimePicker::make('paid_at'),

                TextInput::make('shipping_address.street')
                    ->label('Street')
                    ->required(),

                TextInput::make('shipping_address.city')
                    ->label('City')
                    ->required(),
                TextInput::make('shipping_address.state')
                    ->label('State')
                    ->required(),
                TextInput::make('shipping_address.postalCode')
                    ->label('ZIP')
                    ->required(),
                TextInput::make('shipping_address.country')
                    ->label('Country')->columnSpan(1),

                Toggle::make('is_paid')->columnSpan(2)
                    ->required(),
                Toggle::make('is_shipped')->columnSpan(2)
                    ->required(),
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

    public static function handleOrderPaid(Order $order)
    {
        if ($order->is_paid) {
            Log::info('Order marked as paid: ' . $order->id);

            if ($order->user) {
                $orderItems = is_string($order->order_items) ? json_decode($order->order_items, true) : $order->order_items;
                foreach ($orderItems as $item) {
                    $product = Product::where('name', $item['name'])->first();
                    if ($product) {
                        $inventory = is_string($product->inventory) ? json_decode($product->inventory, true) : $product->inventory;
                        foreach ($inventory as &$invItem) {
                            if ($invItem['size'] == $item['selectedSize']) {
                                Log::info('Adjusting inventory for product: ' . $product->name . ', size: ' . $item['selectedSize'] . ', quantity before: ' . $invItem['quantity']);
                                $invItem['quantity'] -= $item['quantity'];
                                Log::info('Quantity after adjustment: ' . $invItem['quantity']);
                            }
                        }
                        $product->inventory = $inventory;
                        $product->save();
                    }
                }

                // Send email to the user
                Notification::send($order->user, new OrderPaidNotification($order));
            } else {
                Log::error('User not found for order ID: ' . $order->id);
            }
        }
    }

    public static function handleOrderCancelled(Order $order)
    {
        if ($order->is_paid) {
            Log::info('Order marked as cancelled: ' . $order->id);

            if ($order->user) {
                $orderItems = is_string($order->order_items) ? json_decode($order->order_items, true) : $order->order_items;
                foreach ($orderItems as $item) {
                    $product = Product::where('name', $item['name'])->first();
                    if ($product) {
                        $inventory = is_string($product->inventory) ? json_decode($product->inventory, true) : $product->inventory;
                        foreach ($inventory as &$invItem) {
                            if ($invItem['size'] == $item['selectedSize']) {
                                Log::info('Restoring inventory for product: ' . $product->name . ', size: ' . $item['selectedSize'] . ', quantity before: ' . $invItem['quantity']);
                                $invItem['quantity'] += $item['quantity'];
                                Log::info('Quantity after restoration: ' . $invItem['quantity']);
                            }
                        }
                        $product->inventory = $inventory;
                        $product->save();
                    }
                }
            } else {
                Log::error('User not found for order ID: ' . $order->id);
            }
        }
    }
}
