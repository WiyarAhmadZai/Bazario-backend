<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FixProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-product-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix product images by copying assets to storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing product images...');

        // Get all products
        $products = Product::all();

        foreach ($products as $product) {
            if ($product->images) {
                // Decode the JSON string to get the array of image paths
                $images = json_decode($product->images, true);

                // If decoding failed, skip this product
                if (!is_array($images)) {
                    $this->warn("Skipping product {$product->id}: Invalid image data");
                    continue;
                }

                $fixedImages = [];

                foreach ($images as $imagePath) {
                    // Check if the image path is a fake frontend asset path
                    if (strpos($imagePath, 'src/assets/') !== false) {
                        // Extract the actual image filename
                        $imageName = basename($imagePath);

                        // Copy image from frontend assets to storage
                        $newImagePath = $this->copyImageToStorage($imageName);
                        if ($newImagePath) {
                            $fixedImages[] = $newImagePath;
                            $this->info("Fixed image for product {$product->id}: {$imageName} -> {$newImagePath}");
                        } else {
                            $this->warn("Failed to copy image for product {$product->id}: {$imageName}");
                        }
                    } else {
                        // Keep existing valid paths
                        $fixedImages[] = $imagePath;
                    }
                }

                // Update the product with fixed image paths
                $product->images = json_encode($fixedImages);
                $product->save();
            }
        }

        $this->info('Product images fixed successfully!');
    }

    /**
     * Copy an image from frontend assets to storage and return the storage path
     */
    private function copyImageToStorage($imageName)
    {
        // Use the correct path to the frontend assets
        $sourcePath = dirname(base_path()) . '/react-frontend/src/assets/' . $imageName;

        // If the file doesn't exist with the exact name, try with different case for extension
        if (!file_exists($sourcePath)) {
            $pathInfo = pathinfo($imageName);
            $altImageName = $pathInfo['filename'] . '.' . strtolower($pathInfo['extension']);
            $sourcePath = dirname(base_path()) . '/react-frontend/src/assets/' . $altImageName;

            // If still not found, try with uppercase extension
            if (!file_exists($sourcePath)) {
                $altImageName = $pathInfo['filename'] . '.' . strtoupper($pathInfo['extension']);
                $sourcePath = dirname(base_path()) . '/react-frontend/src/assets/' . $altImageName;

                if (!file_exists($sourcePath)) {
                    return null;
                }
            }
        }

        // Generate a unique filename
        $extension = pathinfo($imageName, PATHINFO_EXTENSION);
        $uniqueName = Str::random(40) . '.' . $extension;
        $destinationPath = 'products/' . $uniqueName;

        // Copy the file to storage
        if (Storage::disk('public')->put($destinationPath, file_get_contents($sourcePath))) {
            return $destinationPath;
        }

        return null;
    }
}
