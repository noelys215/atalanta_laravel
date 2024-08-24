<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroCardResource\Pages;
use App\Models\HeroCard;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HeroCardResource extends Resource
{
    protected static ?string $model = HeroCard::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->afterStateUpdated(function (callable $set, $state) {
                        $set('slug', Str::slug($state));
                    }),
                TextInput::make('subtitle')
                    ->required()
                    ->maxLength(255),
                TextInput::make('video_src')
                    ->url()
                    ->nullable(),
                FileUpload::make('image_src')
                    ->label('Upload Image or Video')
                    ->image()
                    ->disk('s3')
                    ->directory('hero-cards')
                    ->visibility('public')
                    ->saveUploadedFileUsing(function ($file, $state, $set) {
                        $path = $file->store('hero-cards', 's3');
                        Storage::disk('s3')->setVisibility($path, 'public');
                        $url = "https://atalantaimages.s3.amazonaws.com/" . $path;
                        $set('image_src', $url);
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
                TextColumn::make('id')->sortable(),
                TextColumn::make('title'),
                TextColumn::make('slug'),
                TextColumn::make('subtitle'),
                TextColumn::make('video_src'),
                TextColumn::make('image_src'),
            ])
            ->filters([
                //
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
            'index' => Pages\ListHeroCards::route('/'),
            'create' => Pages\CreateHeroCard::route('/create'),
            'edit' => Pages\EditHeroCard::route('/{record}/edit'),
        ];
    }
}
