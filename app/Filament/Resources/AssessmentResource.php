<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssessmentResource\Pages;
use App\Filament\Resources\AssessmentResource\RelationManagers;
use App\Models\Assessment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class AssessmentResource extends Resource
{
    protected static ?string $tenantOwnershipRelationshipName = 'room';

    protected static ?string $model = Assessment::class;

    protected static ?string $navigationGroup = 'Data Management';

    protected static ?string $navigationLabel = 'Assessment';

    protected static ?string $pluralLabel = 'Assessments';

    protected static ?string $label = 'Assessment';

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
                Forms\Components\Section::make('Student Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('Student')
                            ->searchable()
                            ->preload()
                            ->relationship('student', 'name', function (Builder $query) {
                                $query->where('room_id', Filament::getTenant()->getKey());
                            })
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $student = \App\Models\Student::find($state);
                                if ($student) {
                                    $set('room_id', $student->room_id);
                                }
                            }),
                        Forms\Components\Select::make('room_id')
                            ->label('Class')
                            ->relationship('room', 'name')
                            ->placeholder(' ')
                            ->disabled()
                            ->required(),
                        Forms\Components\Hidden::make('lecturer_id')
                            ->default(auth()->user()->getKey()),
                    ]),
                Forms\Components\Section::make('Assessment Information')
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        Forms\Components\TextInput::make('assessment_name')
                            ->label('Assessment Name')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('score_1')
                            ->label('Score 1')
                            ->numeric(),
                        Forms\Components\TextInput::make('score_2')
                            ->label('Score 2')
                            ->numeric(),
                        Forms\Components\TextInput::make('score_3')
                            ->label('Score 3')
                            ->numeric(),
                        Forms\Components\TextInput::make('score_4')
                            ->label('Score 4')
                            ->numeric(),
                        Forms\Components\TextInput::make('score_5')
                            ->label('Score 5')
                            ->numeric(),
                        Forms\Components\TextInput::make('score_6')
                            ->label('Score 6')
                            ->numeric(),
                        Forms\Components\TextInput::make('score_7')
                            ->label('Score 7')
                            ->numeric(),
                        Forms\Components\TextInput::make('score_8')
                            ->label('Score 8')
                            ->numeric(),
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
                Tables\Columns\TextColumn::make('room.name')
                    ->label('Class')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assessment_name')
                    ->label('Assessment')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('score_1')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('score_2')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('score_3')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('score_4')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('score_5')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('score_6')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('score_7')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('score_8')
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
                ExportAction::make('Export Excel')
                    ->label('Export Excel')
                    ->exports([
                        ExcelExport::make()->withColumns([
                            Column::make('student.name')->heading('Nama Mahasiswa'),
                            Column::make('student.nim')->heading('NIM'),
                            Column::make('assessment_name')->heading('Penilaian'),
                            Column::make('score_1')->heading(''),
                            Column::make('score_2')->heading(''),
                            Column::make('score_3')->heading(''),
                            Column::make('score_4')->heading(''),
                            Column::make('score_5')->heading(''),
                            Column::make('score_6')->heading(''),
                            Column::make('score_7')->heading(''),
                            Column::make('score_8')->heading(''),
                        ])
                            ->withFilename('assessment-' . Carbon::now()->format('Y-m-d'))
                    ]),
                Tables\Actions\Action::make('Export PDF')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('danger')
                    ->action(function () {
                        $records = Assessment::with(['student', 'room'])->get();
                        $pdf = Pdf::loadView('pdfs.data', ['records' => $records]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                            Notification::make()
                                ->title('Export PDF')
                                ->body('Berhasil mengexport ' . 'data-' . Carbon::now()->format('Y-m-d H:m:s') . '.pdf')
                                ->success()
                                ->icon('heroicon-o-check-circle')
                                ->send();
                        }, 'data-' . Carbon::now()->format('Y-m-d') . '.pdf');
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
}
