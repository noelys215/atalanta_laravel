<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
                TextInput::make('telephone')
                    ->required()
                    ->maxLength(255),
                TextInput::make('country')
                    ->required()
                    ->maxLength(255),
                TextInput::make('address')
                    ->required()
                    ->maxLength(255),
                TextInput::make('address_cont')
                    ->maxLength(255),
                TextInput::make('state')
                    ->required()
                    ->maxLength(255),
                TextInput::make('city')
                    ->required()
                    ->maxLength(255),
                TextInput::make('postal_code')
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_admin')
                    ->required()->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')->sortable()->searchable(),
                TextColumn::make('last_name')->sortable()->searchable(),
                TextColumn::make('email')->sortable()->searchable(),
                TextColumn::make('telephone')->sortable()->searchable(),
                TextColumn::make('country')->sortable()->searchable(),
                TextColumn::make('address')->sortable()->searchable(),
                TextColumn::make('address_cont')->sortable()->searchable(),
                TextColumn::make('state')->sortable()->searchable(),
                TextColumn::make('city')->sortable()->searchable(),
                TextColumn::make('postal_code')->sortable()->searchable(),
                ToggleColumn::make('is_admin')->sortable()->searchable(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
