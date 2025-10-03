<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
class ImportProductsJob implements ShouldQueue
{
    use Queueable;
    public string $path;
    public int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct($path, $userId)
    {
        $this->path = $path;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Import job started for: {$this->path}");
        $failures = [];
        $csv = \League\Csv\Reader::createFromPath(storage_path("app/private/{$this->path}"), 'r');
        $csv->setHeaderOffset(0); // first row as header
        $records = $csv->getRecords();

        foreach ($records as $index => $row) {
            try {
                // Simple validation example
                if (empty($row['name']) || !is_numeric($row['price'])) {
                    throw new \Exception('Invalid name or price');
                }

                Product::updateOrCreate(
                    ['sku' => $row['sku']],
                    ['name' => $row['name'],
                    'price' => $row['price'],
                    'stock_on_hand' => $row['stock_on_hand'],
                    'reorder_threshold' => $row['reorder_threshold'],
                    'status' => $row['status'],
                    'tags' => explode(',', $row['tags']) ?? null
                    ]
                );
            } catch (\Exception $e) {
                $failures[] = array_merge($row, ['error' => $e->getMessage()]);
            }
        }

        if (!empty($failures)) {
            $failFile = 'imports/failures_' . time() . '.csv';
            $csvWriter = \League\Csv\Writer::createFromPath(storage_path("app/{$failFile}"), 'w+');
            $csvWriter->insertOne(array_keys($failures[0]));
            $csvWriter->insertAll($failures);
        }
    }
}
