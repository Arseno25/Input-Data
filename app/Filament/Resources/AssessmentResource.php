<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Assessment;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Jobs\GeneratePdfJob;
use Filament\Facades\Filament;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;
use pxlrbt\FilamentExcel\Columns\Column;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\AssessmentExporter;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Filament\Actions\Exports\Enums\ExportFormat;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AssessmentResource\Pages;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use App\Filament\Resources\AssessmentResource\RelationManagers;

class AssessmentResource extends Resource
{
    protected static ?string $tenantOwnershipRelationshipName = 'room';

    protected static ?string $model = Assessment::class;

    protected static ?string $navigationGroup = 'Data Management';

    protected static ?int $navigationSort = -4;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('super_admin')) {
            return $query;
        }

        return $query
            ->where('lecturer_id', auth()->id())
            ->whereHas('student', function (Builder $query) {
                $query->where('room_id', Filament::getTenant()->getKey());
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(function () {
                    $locale = app()->getLocale();

                    if ($locale == 'id') {
                        return 'Informasi Mahasiswa';
                    }
                    return 'Student Information';
                })
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label(function () {
                                $locale = app()->getLocale();
                                return $locale == 'id' ? 'Mahasiswa' : 'Student';
                            })
                            ->searchable()
                            ->relationship('student', 'name')
                            ->preload()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateHydrated(function (callable $set, $state) {
                                if ($state) {
                                    $student = \App\Models\Student::find($state);
                                    if ($student) {
                                        $set('student.nim', $student->nim);
                                        $set('student.title_of_the_final_project_proposal', $student->title_of_the_final_project_proposal);
                                        $set('student.design_theme', $student->design_theme);
                                    }
                                }
                            })
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($student = \App\Models\Student::find($state)) {
                                    $set('student.nim', $student->nim);
                                    $set('student.title_of_the_final_project_proposal', $student->title_of_the_final_project_proposal);
                                    $set('student.design_theme', $student->design_theme);
                                }
                            }),
                        Forms\Components\TextInput::make('student.nim')
                            ->label('NIM')
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('student.title_of_the_final_project_proposal')
                            ->label('Title of the Final Project Proposal')
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('student.design_theme')
                            ->label('Design Theme')
                            ->required()
                            ->disabled(),
                        Forms\Components\Hidden::make('lecturer_id')
                            ->default(auth()->user()->getKey()),
                    ]),
                Forms\Components\Section::make(function () {
                    $locale = app()->getLocale();

                    if ($locale == 'id') {
                        return 'Informasi Penilaian';
                    }
                    return 'Assessment Information';
                })
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        Forms\Components\Select::make('assessment_stage')
                            ->label(function () {
                                $locale = app()->getLocale();
                                return $locale = 'id' ? 'Tahap Penilaian' : 'Assessment Stage';
                            })
                            ->columnSpanFull()
                            ->options([
                                'Penilaian Tahap 1' => 'Stage 1 Assessment (Penilaian Tahap 1)',
                                'Penilaian Tahap 2' => 'Stage 1 Assessment (Penilaian Tahap 2)',
                                'Penilaian Tahap 3' => 'Stage 1 Assessment (Penilaian Tahap 3)',
                                'Penilaian Tahap 4' => 'Stage 1 Assessment (Penilaian Tahap 4)',
                            ])
                            ->required(),
                        Forms\Components\KeyValue::make('assessment')
                            ->label(function () {
                                $locale = app()->getLocale();
                                return $locale == 'id' ? 'Penilaian' : 'Assessment';
                            })
                            ->required()
                            ->helperText(function () {
                                $locale = app()->getLocale();
                                if ($locale == 'id') {
                                    return 'Label Nilai adalah untuk label nilai (misalnya, Nilai 1), dan Nilai adalah untuk nilai itu sendiri (misalnya, 100).';
                                }
                                return 'Score Label is for the label of the score (e.g., Score 1), and Value is for the score itself (e.g., 100).';
                            })
                            ->keyLabel(function () {
                                $locale = app()->getLocale();

                                if ($locale == 'id') {
                                    return 'Label Nilai';
                                }

                                return 'Score Label';
                            })
                            ->valueLabel(function () {
                                $locale = app()->getLocale();

                                if ($locale == 'id') {
                                    return 'Nilai';
                                }

                                return 'Score';
                            })
                            ->columnSpanFull()
                            ->addActionLabel(function () {
                                $locale = app()->getLocale();

                                if ($locale == 'id') {
                                    return 'Tambah Nilai';
                                }

                                return 'Add Score';
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.title_of_the_final_project_proposal')
                    ->label('Title of the Final Project Proposal')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.design_theme')
                    ->label('Design Theme')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assessment_stage')
                    ->label('Assessment Stage')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assessment')
                    ->label('Assessment')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Lecturer')
                    ->hidden(!auth()->user()->hasRole('super_admin'))
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
                Tables\Actions\ExportAction::make()
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('success')
                    ->exporter(AssessmentExporter::class),
                Tables\Actions\Action::make('Export PDF')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('danger')
                    ->action(function () {
                        $user = auth()->user();
                        GeneratePdfJob::dispatch($user);

                        Notification::make()
                            ->title('Proses Export Berjalan')
                            ->body('PDF sedang diproses. Anda akan menerima notifikasi setelah selesai.')
                            ->success()
                            ->send();
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
            'index' => Pages\ListAssessments::route('/'),
            'create' => Pages\CreateAssessment::route('/create'),
            'edit' => Pages\EditAssessment::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        $locale = app()->getLocale();

        if ($locale == 'id') {
            return 'Penilaian';
        }

        return 'Assesments';
    }
}
