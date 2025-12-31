<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use App\Notifications\OrderPaidNotification;
use BackedEnum;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([

                TextInput::make('short_order_id')
                    ->label('Short Order ID')
                    ->required()
                    ->maxLength(255)->columnSpanFull(),
                TextInput::make('tracking_number')
                    ->label('Tracking Number')
                    ->maxLength(32),

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
                        Placeholder::make('image_preview')
                            ->label('Image')
                            ->content(function (callable $get) {
                                $imageUrl = $get('image');

                                if (! is_string($imageUrl) || $imageUrl === '') {
                                    return '-';
                                }

                                $escapedUrl = e($imageUrl);

                                return new HtmlString(
                                    "<img src=\"{$escapedUrl}\" alt=\"Order item image\" style=\"max-width: 120px; height: auto; border-radius: 6px;\" />"
                                );
                            }),
                        TextInput::make('image')
                            ->label('Image URL')
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
                DateTimePicker::make('delivered_at')
                    ->label('Delivered At'),

                TextInput::make('shipping_address.name')
                    ->label('Recipient Name'),
                TextInput::make('shipping_address.address.line1')
                    ->label('Street')
                    ->maxLength(255),
                TextInput::make('shipping_address.address.line2')
                    ->label('Street 2')
                    ->maxLength(255),
                TextInput::make('shipping_address.address.city')
                    ->label('City')
                    ->maxLength(255),
                TextInput::make('shipping_address.address.state')
                    ->label('State')
                    ->maxLength(255),
                TextInput::make('shipping_address.address.postal_code')
                    ->label('ZIP')
                    ->maxLength(255),
                TextInput::make('shipping_address.address.country')
                    ->label('Country')
                    ->maxLength(255),

                Toggle::make('is_paid')
                    ->label('Is Paid')
                    ->required(),
                Toggle::make('is_shipped')
                    ->label('Is Shipped')
                    ->required(),
                Toggle::make('is_delivered')
                    ->label('Is Delivered')
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
                ToggleColumn::make('is_delivered')->sortable()->searchable(),
                TextColumn::make('shipped_at')->dateTime()->sortable(),
                TextColumn::make('delivered_at')->dateTime()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ])
            ->headerActions([
                Actions\CreateAction::make(),
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

    public static function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['is_delivered'])) {
            $data['is_shipped'] = true;
            $data['delivered_at'] = $data['delivered_at'] ?? now();
            $data['shipped_at'] = $data['shipped_at'] ?? now();
        }

        if (! empty($data['is_shipped'])) {
            $data['shipped_at'] = $data['shipped_at'] ?? now();
        }

        return $data;
    }

    public static function handleOrderPaid(Order $order)
    {
        if ($order->is_paid) {
            $shouldAdjustInventory = $order->inventory_adjusted_at === null;
            $orderItems = is_string($order->order_items) ? json_decode($order->order_items, true) : $order->order_items;

            if ($shouldAdjustInventory) {
                foreach ($orderItems as $item) {
                    $productName = $item['description'] ?? $item['name'] ?? null;
                    $selectedSize = $item['size'] ?? $item['selectedSize'] ?? null;
                    $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 0;

                    if (! $productName || ! $selectedSize || $quantity < 1) {
                        continue;
                    }

                    $product = Product::where('name', $productName)->first();
                    if (! $product) {
                        continue;
                    }

                    $inventory = is_string($product->inventory) ? json_decode($product->inventory, true) : $product->inventory;
                    foreach ($inventory as &$invItem) {
                        if (($invItem['size'] ?? null) === $selectedSize) {
                            Log::info('Adjusting inventory for product: ' . $product->name . ', size: ' . $selectedSize . ', quantity before: ' . $invItem['quantity']);
                            $invItem['quantity'] = max(0, (int) $invItem['quantity'] - $quantity);
                            Log::info('Quantity after adjustment: ' . $invItem['quantity']);
                        }
                    }
                    $product->inventory = $inventory;
                    $product->save();
                }

                $order->forceFill(['inventory_adjusted_at' => now()])->save();
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
        }
    }

    public static function handleOrderCancelled(Order $order)
    {
        if ($order->is_paid && $order->inventory_adjusted_at) {
            Log::info('Order marked as cancelled: ' . $order->id);

            $orderItems = is_string($order->order_items) ? json_decode($order->order_items, true) : $order->order_items;
            foreach ($orderItems as $item) {
                $productName = $item['description'] ?? $item['name'] ?? null;
                $selectedSize = $item['size'] ?? $item['selectedSize'] ?? null;
                $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 0;

                if (! $productName || ! $selectedSize || $quantity < 1) {
                    continue;
                }

                $product = Product::where('name', $productName)->first();
                if (! $product) {
                    continue;
                }

                $inventory = is_string($product->inventory) ? json_decode($product->inventory, true) : $product->inventory;
                foreach ($inventory as &$invItem) {
                    if (($invItem['size'] ?? null) === $selectedSize) {
                        Log::info('Restoring inventory for product: ' . $product->name . ', size: ' . $selectedSize . ', quantity before: ' . $invItem['quantity']);
                        $invItem['quantity'] = (int) $invItem['quantity'] + $quantity;
                        Log::info('Quantity after restoration: ' . $invItem['quantity']);
                    }
                }
                $product->inventory = $inventory;
                $product->save();
            }

            $order->forceFill(['inventory_adjusted_at' => null])->save();
        }
    }
}
