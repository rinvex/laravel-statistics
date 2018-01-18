<?php

declare(strict_types=1);

namespace Rinvex\Statistics\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CleanStatisticsRequests implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ! config('rinvex.statistics.lifetime') || app('rinvex.statistics.request')->where('created_at', '<=', now()->subDays(config('rinvex.statistics.lifetime')))->delete();
    }
}
