<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class ExpireSponsorships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sponsorships:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire sponsored products that have passed their end time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredProducts = Product::where('sponsor', true)
            ->where('sponsor_end_time', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredProducts as $product) {
            $product->update(['sponsor' => false]);
            $count++;

            $this->info("Expired sponsorship for product: {$product->title} (ID: {$product->id})");
        }

        $this->info("Expired {$count} sponsored products");

        return Command::SUCCESS;
    }
}
