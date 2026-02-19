<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Models\ShiftAssignment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AssignCommonEmployeeShifts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:shifts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This use for auto assign common employee shifts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automatic shift assignment...');

        // Fetch schedules for common employees
        $schedules = Schedule::whereHas('user.position', function ($query) {
            $query->where('group', 'umum');
        })->get();

        // Generate assignments for the next 7 days
        foreach ($schedules as $schedule) {
            $dates = collect(range(0, 6))->map(fn($days) => now()->startOfWeek()->addDays($days));
            
            foreach ($dates as $date) {
                if (ucfirst($date->format('l')) === $schedule->day_of_week) {
                    ShiftAssignment::updateOrCreate(
                        [
                            'user_id' => $schedule->user_id,
                            'shift_id' => $schedule->shift_id,
                            'date' => $date->toDateString(),
                        ],
                        []
                    );
                    Log::info("Processing schedule for user_id: {$schedule->user_id} on {$date->toDateString()}");
                }
            }
        }
        
        $this->info('Shift assignments completed successfully.');
    }
}
