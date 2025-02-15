<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassRoomResource\Pages;
use App\Filament\Resources\ClassRoomResource\RelationManagers;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelReader;

class ClassRoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationGroup = 'Data Management';

    protected static ?string $navigationLabel = 'Class Room';

    protected static ?string $pluralLabel = 'Class Rooms';

    protected static ?string $label = 'Class Room';

    protected static ?int $navigationSort = -12;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required(),
                Forms\Components\Select::make('lecturer_id')
                    ->label('Lecturer')
                    ->relationship('users', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('users.name')
                    ->label('Lecturer')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('upload file')
                    ->label('Upload Class')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('success')
                    ->modal()
                    ->modalWidth(MaxWidth::Medium)
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->label('File')
                            ->disk('local')
                            ->preserveFilenames()
                            ->uploadingMessage('Uploading attachment...')
                            ->placeholder('Choose a file to upload')
                            ->acceptedFileTypes([
                                'text/csv',
                                'csv',
                                'application/csv',
                                'text/comma-separated-values',
                                'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel.sheet.macroEnabled.12',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
                                'application/vnd.ms-excel.template.macroEnabled.12',
                                'application/vnd.ms-excel',
                                'application/vnd.ms-excel.addin.macroEnabled.12',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data, Action $action) {
                        $file = $data['file'];

                        $path = Storage::disk('local')->path($file);

                        SimpleExcelReader::create($path)
                            ->useHeaders(['Name', 'Lecturer ID'])
                            ->getRows()
                            ->each(function(array $rowProperties) {
                                Room::updateOrCreate(
                                    ['name' => $rowProperties['Name']],
                                    [
                                        'lecturer_id' => $rowProperties['Lecturer ID'] !== '' ? $rowProperties['Lecturer ID'] : null,
                                    ]
                                );
                            });
                    }),
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
            'index' => Pages\ListClassRooms::route('/'),
            'create' => Pages\CreateClassRoom::route('/create'),
            'edit' => Pages\EditClassRoom::route('/{record}/edit'),
        ];
    }
}
