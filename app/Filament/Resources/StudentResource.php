<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Student;
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

class StudentResource extends Resource
{
    protected static ?string $tenantOwnershipRelationshipName = 'room';

    protected static ?string $model = Student::class;

    protected static ?string $navigationGroup = 'Data Management';

    protected static ?int $navigationSort = -5;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required(),
                Forms\Components\TextInput::make('nim')
                    ->label('NIM')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('title_of_the_final_project_proposal')
                    ->label('Title of the Final Project Proposal')
                    ->required(),
                Forms\Components\TextInput::make('design_theme')
                    ->label('Design Theme')
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
                Tables\Columns\TextColumn::make('nim')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title_of_the_final_project_proposal')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('design_theme')
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
                    ->label('Upload Student')
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
                            ->useHeaders(['NAMA', 'NIM', 'JUDUL PROPOSAL TUGAS AKHIR', 'TEMA RANCANGAN'])
                            ->getRows()
                            ->each(function (array $rowProperties) {
                                Student::updateOrCreate(
                                    ['nim' => $rowProperties['NIM']],
                                    [
                                        'name' => $rowProperties['NAMA'],
                                        'title_of_the_final_project_proposal' => $rowProperties['JUDUL PROPOSAL TUGAS AKHIR'],
                                        'design_theme' => $rowProperties['TEMA RANCANGAN'],
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        $locale = app()->getLocale();

        if ($locale == 'id') {
            return 'Mahasiswa';
        }

        return 'Students';
    }
}
