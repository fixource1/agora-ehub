<?php

namespace App\Filament\Resources\Resources\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('uploader_id')
                    ->relationship('uploader', 'name')
                    ->required(),
                Select::make('resource_type_id')
                    ->relationship('resourceType', 'name')
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name'),
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('subtitle'),
                Textarea::make('description')
                    ->columnSpanFull(),
                FileUpload::make('cover_image')
                    ->image(),
                TextInput::make('language')
                    ->required()
                    ->default('en'),
                TextInput::make('audience_level')
                    ->required()
                    ->default('general'),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                TextInput::make('view_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('download_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('published_at'),
            ]);
    }
}
