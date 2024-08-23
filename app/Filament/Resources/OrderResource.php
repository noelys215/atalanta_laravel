<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use App\Notifications\OrderPaidNotification;
use Filament\Forms\Components\TextInput;
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
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

                TextInput::make('short_order_id')
                    ->label('Short Order ID')
                    ->required()
                    ->maxLength(255)->columnSpanFull(),

                TextInput::make('customer_name')
                    ->label('Customer Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('customer_email')
                    ->label('Customer Email')
                    ->required()
                    ->email()
                    ->maxLength(255),


                Repeater::make('order_items')
                    ->schema([
                        TextInput::make('description')
                            ->label('Product Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->required()
                            ->numeric(),
                        TextInput::make('price')
                            ->label('Price')
                            ->required()
                            ->numeric(),
                        TextInput::make('image')
                            ->label('Image URL')
                            ->required()
                            ->url(),
                        TextInput::make('size')
                            ->label('Size')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columnSpan('full')
                    ->label('Order Items'),

                TextInput::make('payment_method')
                    ->label('Payment Method')
                    ->required()
                    ->maxLength(255),
                TextInput::make('items_price')
                    ->label('Items Price')
                    ->required()
                    ->numeric(),
                TextInput::make('tax_price')
                    ->label('Tax Price')
                    ->required()
                    ->numeric(),
                TextInput::make('shipping_price')
                    ->label('Shipping Price')
                    ->required()
                    ->numeric(),
                TextInput::make('total_price')
                    ->label('Total Price')
                    ->required()
                    ->numeric(),
                TextInput::make('payment_result')
                    ->label('Payment Result'),

                DateTimePicker::make('paid_at')
                    ->label('Paid At'),
                DateTimePicker::make('shipped_at')
                    ->label('Shipped At'),

                TextInput::make('shipping_address.address')
                    ->label('Street')
                    ->required(),
                TextInput::make('shipping_address.city')
                    ->label('City')
                    ->required(),
                TextInput::make('shipping_address.state')
                    ->label('State')
                    ->required(),
                TextInput::make('shipping_address.postal_code')
                    ->label('ZIP')
                    ->required(),
                TextInput::make('shipping_address.country')
                    ->label('Country')
                    ->required(),

                Toggle::make('is_paid')
                    ->label('Is Paid')
                    ->required(),
                Toggle::make('is_shipped')
                    ->label('Is Shipped')
                    ->required(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('short_order_id')->label('Short Order ID')->sortable()->searchable(),
                TextColumn::make('id')->sortable(),
                TextColumn::make('customer_name')->label('User')->sortable()->searchable(),
                TextColumn::make('customer_email')->label('Email')->sortable()->searchable(),
                TextColumn::make('items_price')->sortable()->searchable(),
                TextColumn::make('tax_price')->sortable()->searchable(),
                TextColumn::make('shipping_price')->sortable()->searchable(),
                TextColumn::make('total_price')->sortable()->searchable(),
                TextColumn::make('payment_result')->label('Payment Result')->sortable()->searchable(),
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
                try {
                    Notification::route('mail', $order->customer_email)
                        ->notify(new OrderPaidNotification($order));
                    \Log::info('Order paid email sent successfully', ['order_id' => $order->id, 'email' => $order->customer_email]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send order paid email', [
                        'order_id' => $order->id,
                        'email' => $order->customer_email,
                        'error_message' => $e->getMessage(),
                    ]);
                }
            } else {
                throw new ModelNotFoundException('User not found for order ID: ' . $order->id);
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
