<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataStudentResource\Pages;
use App\Filament\Resources\DataStudentResource\RelationManagers;
use App\Models\DataStudent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DataStudentResource extends Resource
{
    protected static ?string $model = DataStudent::class;

    protected static ?string $navigationGroup = 'Data';

    protected static ?string $navigationLabel = 'Data Mahasiswa';

    protected static ?string $navigationTitle = 'Data Mahasiswa';

    protected static ?int $navigationSort = -6;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                ->label('Nama')
                ->required(),
                TextInput::make('nim')
                ->label('NIM')
                ->required()
                ->numeric(),
                TextInput::make('score')
                ->label('Nilai')
                ->required()
                ->numeric()
                ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                ->label('Nama')
                ->searchable(),
                TextColumn::make('nim')
                ->label('NIM')
                ->searchable(),
                TextColumn::make('score')
                ->label('Nilai')
                ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListDataStudents::route('/'),
            'create' => Pages\CreateDataStudent::route('/create'),
            'edit' => Pages\EditDataStudent::route('/{record}/edit'),
        ];
    }
}
