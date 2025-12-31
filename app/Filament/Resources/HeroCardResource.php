<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroCardResource\Pages;
use App\Models\HeroCard;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class HeroCardResource extends Resource
{
    protected static ?string $model = HeroCard::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
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
                FileUpload::make('media_upload')
                    ->label('Upload Image or Video')
                    ->disk('s3')
                    ->directory('hero-cards')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/*', 'video/mp4', 'video/webm', 'video/quicktime'])
                    ->maxSize(102400) // 100MB max for videos
                    ->dehydrated(false)
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        if (empty($state)) {
                            return;
                        }
                        
                        // Handle array of files or single file
                        $file = is_array($state) ? reset($state) : $state;
                        
                        if (!$file instanceof TemporaryUploadedFile) {
                            return;
                        }
                        
                        // Store file to S3
                        $path = $file->store('hero-cards', 's3');
                        Storage::disk('s3')->setVisibility($path, 'public');
                        $url = "https://atalantaimages.s3.amazonaws.com/" . $path;
                        
                        // Check file extension to determine type
                        $extension = strtolower($file->getClientOriginalExtension());
                        $videoExtensions = ['mp4', 'webm', 'mov', 'avi', 'mkv'];
                        
                        if (in_array($extension, $videoExtensions)) {
                            // It's a video
                            $set('video_src', $url);
                            $set('image_src', null);
                        } else {
                            // It's an image
                            $set('image_src', $url);
                            $set('video_src', null);
                        }
                    }),
                TextInput::make('video_src')
                    ->label('Video URL')
                    ->url()
                    ->nullable()
                    ->hint('Auto-populated when you upload a video'),
                TextInput::make('image_src')
                    ->label('Image URL')
                    ->url()
                    ->nullable()
                    ->hint('Auto-populated when you upload an image'),
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
