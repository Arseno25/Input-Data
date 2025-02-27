<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Assessment;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
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

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $records = Assessment::with(['student'])->get();

        $pdf = Pdf::loadView('pdfs.data', ['records' => $records])
            ->setPaper('a4', 'landscape');

        $filename = 'exports/assessment-data-' . Carbon::now()->format('Y-m-d_H-i-s') . '.pdf';

        Storage::disk('public')->put($filename, $pdf->output());

        $basename = basename($filename);

        if ($records->isEmpty()) {
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

        $this->user->notify(
            Notification::make()
                ->title('PDF Report Ready')
                ->body('The PDF report has been successfully generated. Click the button below to download it.')
                ->actions([
                    \Filament\Notifications\Actions\Action::make('Download PDF')
                        ->label('download .pdf')
                        ->color('success')
                        ->markAsRead()
                        ->url(route('download.pdf', ['filename' => $basename])),
                ])
                ->icon('heroicon-o-paper-clip')
                ->success()
                ->toDatabase()
        );
    }
}
