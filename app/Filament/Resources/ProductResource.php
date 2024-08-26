<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
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
                        'tanks' => 'tanks',
                        'shirts' => 'shirts',
                        'jackets' => 'jackets',
                        'pants' => 'pants',
                        'shorts' => 'shorts',
                        'footwear' => 'footwear',
                        'all' => 'all',
                    ])
                    ->required(),
                Select::make('department')
                    ->options([
                        'accessories' => 'accessories',
                        'man' => 'man',
                        'woman' => 'woman',
                    ])
                    ->required(),
                TextInput::make('brand')
                    ->required()
                    ->maxLength(255),
                TextInput::make('color')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')->rows(5)->autosize()->columnSpan('full'),
                Repeater::make('inventory')
                    ->schema([
                        Select::make('size')
                            ->options([
                                'XS' => 'XS',
                                'S' => 'S',
                                'M' => 'M',
                                'L' => 'L',
                                'XL' => 'XL',
                                'XXL' => 'XXL',
                                'OS' => 'OS',
                                '6' => '6',
                                '6.5' => '6.5',
                                '7' => '7',
                                '7.5' => '7.5',
                                '8' => '8',
                                '8.5' => '8.5',
                                '9' => '9',
                                '9.5' => '9.5',
                                '10' => '10',
                                '10.5' => '10.5',
                                '11' => '11',
                                '11.5' => '11.5',
                                '12' => '12',
                                '12.5' => '12.5',
                                '13' => '13',
                            ])
                            ->required(),
                        TextInput::make('quantity')
                            ->required()
                            ->numeric(),
                    ])
                    ->columnSpan('full'),
                FileUpload::make('image')
                    ->multiple()
                    ->image()
                    ->reorderable()
                    ->disk('s3')
                    ->panelLayout('grid')
                    ->directory('products')
                    ->visibility('public')
                    ->saveUploadedFileUsing(function ($file, $state, $set) {
                        $path = $file->store('products', 's3');
                        Storage::disk('s3')->setVisibility($path, 'public');
                        $url = "https://atalantaimages.s3.amazonaws.com/" . $path;

                        // Append URL to images array
                        $state[] = $url;
                        $set('image', $state);

                        return $url;
                    }),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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

