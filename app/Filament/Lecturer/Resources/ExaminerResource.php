<?php

namespace App\Filament\Lecturer\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Assessment;
use Filament\Tables\Table;
use App\Jobs\GeneratePdfJob;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\AssessmentExporter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Lecturer\Resources\ExaminerResource\Pages;
use App\Filament\Lecturer\Resources\ExaminerResource\RelationManagers;
use Filament\Forms\Components\Hidden;

class ExaminerResource extends Resource
{
    protected static ?string $model = Assessment::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Assessment Management';

    protected static ?string $navigationLabel = 'Examiner Lecturer';

    protected static ?string $label = 'Assessment';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('student');

        if (auth()->user()->hasRole('super_admin')) {
            return $query;
        }

        $role = static::$navigationLabel === 'Supervisor Lecturer' ? 'supervisor' : 'examiner';

        return $query
            ->where('lecturer_id', auth()->id())
            ->where('type', $role);
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
                        Forms\Components\Select::make('group_id')
                            ->label(function () {
                                return app()->getLocale() == 'id' ? 'Kelompok' : 'Group';
                            })
                            ->relationship('group', 'name')
                            ->searchable()
                            ->preload()
                            ->live(),

                        Forms\Components\Select::make('student_id')
                            ->label(function () {
                                return app()->getLocale() == 'id' ? 'Mahasiswa' : 'Student';
                            })
                            ->options(function (Forms\Get $get) {
                                $groupId = $get('group_id');
                                if (!$groupId) return [];

                                return \App\Models\Student::query()
                                    ->where('group_id', $groupId)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                if ($student = \App\Models\Student::find($state)) {
                                    $set('student.nim', $student->nim);
                                    $set('student.title_of_the_final_project_proposal', $student->title_of_the_final_project_proposal);
                                    $set('student.design_theme', $student->design_theme);
                                }
                            })
                            ->loadStateFromRelationshipsUsing(function (Forms\Get $get, Forms\Set $set, $state) {
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
                            ->default([
                                'ZONING' => 0,
                                'TATA MASA/BLOK PLAN' => 0,
                                'INFRASTRUKTUR TAPAK' => 0,
                                'LANDSCAPE/RUANG LUAR' => 0,
                                'ASPEK STANDAR/TEKNIS/PERATURAN' => 0,
                                'TEMA RANCANGAN' => 0,
                                'KUALITAS DAN KELENGKAPAN' => 0,
                                'TEKNIK PRESENTASI DAN KOMUNIKASI' => 0,
                            ])
                            ->disableDeletingRows()
                            ->disableAddingRows()
                            ->disableEditingKeys()
                            ->disabledOn('edit')
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
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('type')
                            ->default('examiner'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Lecturer')
                    ->searchable()
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('student.group.name')
                    ->label('Group')
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
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Lecturer')
                    ->hidden()
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('Export Excel')
                    ->hidden(fn() => !auth()->user()->hasRole('super_admin'))
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('success')
                    ->exporter(AssessmentExporter::class),
                Tables\Actions\Action::make('Export PDF')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('danger')
                    ->hidden(fn() => !auth()->user()->hasRole('super_admin'))
                    ->action(function () {
                        $user = auth()->user();

                        try {
                            \Log::info('Dispatching PDF job', ['user_id' => $user->id]);
                            $job = new GeneratePdfJob($user);
                            dispatch($job);

                            Notification::make()
                                ->title('Export Process in Progress')
                                ->body('The PDF is being processed. You will receive a notification when it is finished.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Log::error('PDF job error', ['error' => $e->getMessage()]);
                            throw $e;
                        }
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
            'index' => Pages\ListExaminers::route('/'),
            'create' => Pages\CreateExaminer::route('/create'),
            'edit' => Pages\EditExaminer::route('/{record}/edit'),
        ];
    }
}
