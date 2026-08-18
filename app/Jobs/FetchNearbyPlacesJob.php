<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchNearbyPlacesJob implements ShouldQueue
{
    use Queueable;

    public float $lat;
    public float $lon;
    public int $assetId;

    /**
     * Create a new job instance.
     */
    public function __construct(float $lat, float $lon, int $assetId)
    {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->assetId = $assetId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \App\Services\OpenStreetMapService::getNearbyPlaces($this->lat, $this->lon, $this->assetId);
    }
}
