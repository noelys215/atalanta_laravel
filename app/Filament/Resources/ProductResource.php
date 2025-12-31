<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use BackedEnum;
use Closure;
use Filament\Actions;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    /**
     * Apparel sizes (XS-XXL)
     */
    public const APPAREL_SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

    /**
     * Shoe sizes (6-13 with half sizes)
     */
    public const SHOE_SIZES = ['6', '6.5', '7', '7.5', '8', '8.5', '9', '9.5', '10', '10.5', '11', '11.5', '12', '12.5', '13'];

    /**
     * One size
     */
    public const ONE_SIZE = ['OS'];

    /**
     * Get sizes based on product category
     */
    public static function getSizesForCategory(?string $category): array
    {
        return match ($category) {
            'footwear' => self::SHOE_SIZES,
            'tops', 'bottoms' => self::APPAREL_SIZES,
            default => self::ONE_SIZE,
        };
    }

    /**
     * Build inventory array with all sizes for a category (default quantity 3)
     */
    public static function buildInventoryForCategory(?string $category): array
    {
        $sizes = self::getSizesForCategory($category);
        return array_map(fn($size) => ['size' => $size, 'quantity' => 3], $sizes);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->afterStateUpdated(function (callable $set, $state) {
                        $set('slug', Str::slug($state));
                    }),
                TextInput::make('price')
                    ->required()
                    ->numeric(),
                Select::make('category')
                    ->options([
                        'tops' => 'Tops',
                        'bottoms' => 'Bottoms',
                        'footwear' => 'Footwear',
                        'electronics' => 'Electronics',
                        'furniture' => 'Furniture',
                        'all' => 'All',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state, Get $get) {
                        // When category changes, reset inventory to appropriate sizes
                        $set('inventory', self::buildInventoryForCategory($state));
                    }),
                Select::make('department')
                    ->options([
                        'Homme' => 'Homme',
                        'Femme' => 'Femme',
                        'Essentials' => 'Essentials',
                    ])
                    ->required(),
                TextInput::make('brand')
                    ->required()
                    ->maxLength(255),
                TextInput::make('color')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')->rows(5)->autosize()->columnSpan('full'),

                Section::make('Inventory')
                    ->description(fn (Get $get): string => match ($get('category')) {
                        'footwear' => 'Shoe sizes (6-13). Delete rows you don\'t stock.',
                        'tops', 'bottoms' => 'Apparel sizes (XS-XXL). Delete rows you don\'t stock.',
                        default => 'One size (OS)',
                    })
                    ->schema([
                        Repeater::make('inventory')
                            ->label('')
                            ->schema([
                                TextInput::make('size')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(1)
                                    ->default(0)
                                    ->required()
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->addable(false)
                            ->reorderable(false)
                            ->deletable(true)
                            ->grid(fn (Get $get): int => match ($get('../category')) {
                                'footwear' => 3,
                                'tops', 'bottoms' => 3,
                                default => 1,
                            })
                            ->default(fn (Get $get): array => self::buildInventoryForCategory($get('../category')))
                            ->columnSpan('full'),
                    ])
                    ->columnSpan('full'),

                SpatieMediaLibraryFileUpload::make('images')
                    ->collection('product_images')
                    ->multiple()
                    ->reorderable()
                    ->image()
                    ->panelLayout('grid'),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('images')
                    ->collection('product_images')
                    ->limit(1)
                    ->circular()
                    ->visibility('private')
                    ->checkFileExistence(false),
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('price')->sortable()->searchable(),
                TextColumn::make('category')->sortable()->searchable(),
                TextColumn::make('department')->sortable()->searchable(),
                TextColumn::make('brand')->sortable()->searchable(),
                TextColumn::make('color')->sortable()->searchable(),
                BooleanColumn::make('in_stock')
                    ->getStateUsing(function (Product $record) {
                        return collect($record->inventory)->sum('quantity') > 0;
                    })
                    ->label('In Stock'),
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('department')
                    ->options([
                        'Homme' => 'Homme',
                        'Femme' => 'Femme',
                        'Essentials' => 'Essentials',
                    ])
                    ->searchable(),
                SelectFilter::make('category')
                    ->options([
                        'tops' => 'tops',
                        'bottoms' => 'bottoms',
                        'footwear' => 'footwear',
                        'electronics' => 'electronics',
                        'furniture' => 'furniture',
                        'all' => 'all',
                    ])
                    ->searchable(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = Str::slug($data['name']);
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = Str::slug($data['name']);
        return $data;
    }
}
