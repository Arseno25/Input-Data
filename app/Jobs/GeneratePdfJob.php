<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Assessment;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(): void
    {
        Log::info('Starting PDF generation');

        try {
            $records = Assessment::with(['student'])->get();
            Log::info('Records found:', ['count' => $records->count()]);

            if ($records->isEmpty()) {
                Log::info('No records found');
                $this->user->notify(
                    Notification::make()
                        ->title('No Data Available')
                        ->body('There is no data available to export at this time.')
                        ->icon('heroicon-o-x-circle')
                        ->danger()
                        ->toDatabase()
                );
                return;
            }

            $pdf = Pdf::loadView('pdfs.data', ['records' => $records])
                ->setPaper('a4', 'landscape');

            $filename = 'exports/assessment-data-' . Carbon::now()->format('Y-m-d_H-i-s') . '.pdf';
            Log::info('Saving PDF', ['filename' => $filename]);

            Storage::disk('public')->put($filename, $pdf->output());
            $basename = basename($filename);

            Log::info('PDF generated successfully');

            $this->user->notify(
                Notification::make()
                    ->title('PDF Report Ready')
                    ->body('The PDF report has been successfully generated. Click the button below to download it.')
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('Download PDF')
                            ->label('Download .pdf')
                            ->color('success')
                            ->markAsRead()
                            ->url(route('download.pdf', ['filename' => $basename])),
                    ])
                    ->icon('heroicon-o-paper-clip')
                    ->success()
                    ->toDatabase()
            );
        } catch (\Exception $e) {
            Log::error('PDF generation failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
