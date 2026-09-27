<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SeedProductImages extends Command
{
    protected $signature = 'app:seed-product-images {--force : Re-download images that already exist}';

    protected $description = 'Download Unsplash images for seeded products and update image_path in the database';

    /** Map product slug → Unsplash photo ID */
    private const SLUG_PHOTO_MAP = [
        'classic-white-tee'          => '1521572163474-6864f9cf17ab',
        'classic-black-tee'          => '1576566588028-4147f3842f27',
        'classic-navy-tee'           => '1503341504253-dff4815485f1',
        'classic-sand-tee'           => '1562157873-818bc0726f68',
        'urban-oversize-graphite'    => '1618517351616-38fb9c5210c6',
        'urban-oversize-cream'       => '1585386959984-a4155224a1ad',
        'graffiti-drop-shoulder'     => '1529374255404-311a2a4f1fd9',
        'neon-statement-tee'         => '1560750588-73207b1ef5b8',
        'rundry-performance-tee'     => '1571731956672-f2b94d7dd0cb',
        'trainflex-active-tee'       => '1517836357463-d25dfeac3438',
        'breathlite-sport-tee'       => '1538805060514-97d9cc17730c',
        'motion-core-tee'            => '1604671368394-2240d0b1bb6c',
        'heritage-cotton-luxe'       => '1620799140408-edc6dcb6d633',
        'signature-embroidered-tee'  => '1551232864-3f0890e580d9',
        'minimal-gold-label-tee'     => '1542291026-7eec264c27ff',
        'silktouch-elite-tee'        => '1549298916-b41d501d3772',
        'vintage-indigo-tee'         => '1558769132-cb1aea458c5e',
        'monochrome-boxy-tee'        => '1624378439575-d8705ad7ae80',
        'endurance-mesh-tee'         => '1476480862126-209bfaa8edc8',
        'atelier-limited-tee'        => '1556742049-0cfed4f6a45d',
    ];

    public function handle(): int
    {
        $force = $this->option('force');

        $this->info('Seeding product images from Unsplash…');

        Storage::disk('public')->makeDirectory('products');

        $bar = $this->output->createProgressBar(count(self::SLUG_PHOTO_MAP));
        $bar->start();

        $updated = 0;
        $skipped = 0;
        $failed  = 0;

        foreach (self::SLUG_PHOTO_MAP as $slug => $photoId) {
            $filename    = "products/{$slug}.jpg";
            $storagePath = Storage::disk('public')->path($filename);

            $alreadyExists = Storage::disk('public')->exists($filename);

            if ($alreadyExists && ! $force) {
                $skipped++;
                $this->updateDb($slug, $filename);
                $bar->advance();
                continue;
            }

            $url = "https://images.unsplash.com/photo-{$photoId}?w=800&q=85&auto=format&fit=crop";

            try {
                $response = Http::timeout(30)->get($url);

                if ($response->successful()) {
                    Storage::disk('public')->put($filename, $response->body());
                    $this->updateDb($slug, $filename);
                    $updated++;
                } else {
                    $this->warn("\n  ✗ HTTP {$response->status()} for {$slug}");
                    $failed++;
                }
            } catch (\Throwable $e) {
                $this->warn("\n  ✗ Failed {$slug}: {$e->getMessage()}");
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Done — {$updated} downloaded, {$skipped} skipped, {$failed} failed.");

        return self::SUCCESS;
    }

    private function updateDb(string $slug, string $imagePath): void
    {
        Product::where('slug', $slug)->update(['image_path' => $imagePath]);
    }
}
