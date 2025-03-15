<?php

namespace App\Filament\Pages\Settings;

use App\Settings\WebsiteSettings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\SettingsPage;

class ManageWebsite extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'Settings';
    protected static ?string $navigationGroup = 'Website Settings';

    protected static string $settings = WebsiteSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('website_title')
                    ->label('Name Website')
                    ->required(),
                TextInput::make('website_description')
                    ->label('Deskripsi Website')
                    ->required(),
                Toggle::make('use_logo')
                    ->label('Website Logo')
                    ->default(false)
                    ->columnSpanFull()
                    ->live(),
                FileUpload::make('website_logo')
                    ->label('Logo Website')
                    ->maxWidth('200px')
                    ->visible(fn(Get $get) => $get('use_logo'))
                    ->reactive()
                    ->directory('/assets/weblogo'),
                FileUpload::make('website_favicon')
                    ->label('Favicon Website')
                    ->visible(fn(Get $get) => $get('use_logo'))
                    ->reactive()
                    ->directory('/settings'),
            ]);
    }
}
