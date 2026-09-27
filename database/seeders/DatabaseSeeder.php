<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Product::query()->delete();
        ProductCategory::query()->delete();

        User::query()->firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'name'     => 'Test User',
            'password' => 'password',
            'role'     => 'customer',
        ]);

        User::query()->firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name'     => 'Admin',
            'password' => 'password',
            'role'     => 'admin',
        ]);

        $categoryMap = collect([
            [
                'name' => 'Hommes',
                'slug' => 'hommes',
                'description' => 'Modeles intemporels pour un usage quotidien.',
            ],
            [
                'name' => 'Femmes',
                'slug' => 'femmes',
                'description' => 'Coupes modernes et looks urbains.',
            ],
            [
                'name' => 'Garcons',
                'slug' => 'garcons',
                'description' => 'Textiles legers adaptes aux activites physiques.',
            ],
            [
                'name' => 'Filles',
                'slug' => 'filles',
                'description' => 'Finitions haut de gamme et editions limitees.',
            ],
        ])->map(function ($category) {
            return ProductCategory::create($category);
        });

        collect([
            ['name' => 'Classic White Tee', 'slug' => 'classic-white-tee', 'description' => 'T-shirt blanc essentiel en coton doux, coupe droite et finition propre pour un usage quotidien.', 'price_cents' => 1990, 'currency' => 'EUR', 'is_featured' => true, 'image_path' => 'products/classic-white-tee.jpg', 'product_category_id' => $categoryMap[0]->id],
            ['name' => 'Classic Black Tee', 'slug' => 'classic-black-tee', 'description' => 'Version noire du modele classique avec silhouette simple et matiere confortable.', 'price_cents' => 1990, 'currency' => 'EUR', 'is_featured' => false, 'image_path' => 'products/classic-black-tee.jpg', 'product_category_id' => $categoryMap[0]->id],
            ['name' => 'Classic Navy Tee', 'slug' => 'classic-navy-tee', 'description' => 'Base bleu marine facile a porter, pensee pour une garde-robe sobre et durable.', 'price_cents' => 2090, 'currency' => 'EUR', 'is_featured' => false, 'image_path' => 'products/classic-navy-tee.jpg', 'product_category_id' => $categoryMap[0]->id],
            ['name' => 'Classic Sand Tee', 'slug' => 'classic-sand-tee', 'description' => 'T-shirt sable au tombant net, adapte aux looks neutres et minimalistes.', 'price_cents' => 2150, 'currency' => 'EUR', 'is_featured' => false, 'image_path' => 'products/classic-sand-tee.jpg', 'product_category_id' => $categoryMap[0]->id],
            ['name' => 'Urban Oversize Graphite', 'slug' => 'urban-oversize-graphite', 'description' => 'Modele oversize graphite avec volume streetwear et jersey epais.', 'price_cents' => 2990, 'currency' => 'EUR', 'is_featured' => true, 'image_path' => 'products/urban-oversize-graphite.jpg', 'product_category_id' => $categoryMap[1]->id],
            ['name' => 'Urban Oversize Cream', 'slug' => 'urban-oversize-cream', 'description' => 'Declinaison creme du volume oversize, port ample et allure urbaine.', 'price_cents' => 2990, 'currency' => 'EUR', 'is_featured' => false, 'image_path' => 'products/urban-oversize-cream.jpg', 'product_category_id' => $categoryMap[1]->id],
            ['name' => 'Graffiti Drop Shoulder', 'slug' => 'graffiti-drop-shoulder', 'description' => 'T-shirt a epaules tombees inspire des codes streetwear et des graphismes de rue.', 'price_cents' => 3190, 'currency' => 'EUR', 'is_featured' => false, 'image_path' => 'products/graffiti-drop-shoulder.jpg', 'product_category_id' => $categoryMap[1]->id],
            ['name' => 'Neon Statement Tee', 'slug' => 'neon-statement-tee', 'description' => 'Piece visuelle a accents neon, pensee pour un vestiaire expressif.', 'price_cents' => 2790, 'currency' => 'EUR', 'is_featured' => false, 'image_path' => 'products/neon-statement-tee.jpg', 'product_category_id' => $categoryMap[1]->id],
            ['name' => 'RunDry Performance Tee', 'slug' => 'rundry-performance-tee', 'description' => 'T-shirt sport leger favorisant l evacuation de l humidite pendant l effort.', 'price_cents' => 2490, 'currency' => 'EUR', 'is_featured' => true, 'image_path' => 'products/rundry-performance-tee.jpg', 'product_category_id' => $categoryMap[2]->id],
            ['name' => 'TrainFlex Active Tee', 'slug' => 'trainflex-active-tee', 'description' => 'Modele d entrainement souple concu pour accompagner les mouvements repetes.', 'price_cents' => 2690, 'currency' => 'EUR', 'is_featured' => false, 'image_path' => 'products/trainflex-active-tee.jpg', 'product_category_id' => $categoryMap[2]->id],
            ['name' => 'BreathLite Sport Tee', 'slug' => 'breathlite-sport-tee', 'description' => 'Textile respirant et leger pour les seances intenses ou les sorties rapides.', 'price_cents' => 2390, 'currency' => 'EUR', 'is_featured' => false, 'image_path' => 'products/breathlite-sport-tee.jpg', 'product_category_id' => $categoryMap[2]->id],
            ['name' => 'Motion Core Tee', 'slug' => 'motion-core-tee', 'description' => 'Base sport polyvalente, pensee pour l echauffement comme pour le quotidien.', 'price_cents' => 2550, 'currency' => 'EUR', 'is_featured' => false, 'image_path' => 'products/motion-core-tee.jpg', 'product_category_id' => $categoryMap[2]->id],
            ['name' => 'Heritage Cotton Luxe', 'slug' => 'heritage-cotton-luxe', 'description' => 'T-shirt premium en coton dense avec finitions soignees et toucher haut de gamme.', 'price_cents' => 4490, 'currency' => 'EUR', 'is_featured' => true, 'image_path' => 'products/heritage-cotton-luxe.jpg', 'product_category_id' => $categoryMap[3]->id],
            ['name' => 'Signature Embroidered Tee', 'slug' => 'signature-embroidered-tee', 'description' => 'Piece premium relevee par une broderie discrete et une coupe precise.', 'price_cents' => 4990, 'currency' => 'EUR', 'is_featured' => false, 'image_path' => 'products/signature-embroidered-tee.jpg', 'product_category_id' => $categoryMap[3]->id],
            ['name' => 'Minimal Gold Label Tee', 'slug' => 'minimal-gold-label-tee', 'description' => 'Modele epure avec detail signature, destine a une ligne premium minimaliste.', 'price_cents' => 4690, 'currency' => 'EUR', 'is_featured' => false, 'image_path' => 'products/minimal-gold-label-tee.jpg', 'product_category_id' => $categoryMap[3]->id],
            ['name' => 'SilkTouch Elite Tee', 'slug' => 'silktouch-elite-tee', 'description' => 'T-shirt premium au toucher soyeux, coupe nette et finition plus habillee.', 'price_cents' => 5290, 'currency' => 'EUR', 'is_featured' => false, 'image_path' => 'products/silktouch-elite-tee.jpg', 'product_category_id' => $categoryMap[3]->id],
            ['name' => 'Vintage Indigo Tee', 'slug' => 'vintage-indigo-tee', 'description' => 'Classique teinte indigo avec rendu legerement delave et esprit heritage.', 'price_cents' => 2290, 'currency' => 'EUR', 'is_featured' => false, 'image_path' => 'products/vintage-indigo-tee.jpg', 'product_category_id' => $categoryMap[0]->id],
            ['name' => 'Monochrome Boxy Tee', 'slug' => 'monochrome-boxy-tee', 'description' => 'Coupe boxy monochrome, construite pour un look streetwear net et contemporain.', 'price_cents' => 3050, 'currency' => 'EUR', 'is_featured' => false, 'image_path' => 'products/monochrome-boxy-tee.jpg', 'product_category_id' => $categoryMap[1]->id],
            ['name' => 'Endurance Mesh Tee', 'slug' => 'endurance-mesh-tee', 'description' => 'Modele sport a structure aeree, adapte aux usages cardio et endurance.', 'price_cents' => 2750, 'currency' => 'EUR', 'is_featured' => false, 'image_path' => 'products/endurance-mesh-tee.jpg', 'product_category_id' => $categoryMap[2]->id],
            ['name' => 'Atelier Limited Tee', 'slug' => 'atelier-limited-tee', 'description' => 'Edition limitee premium, coupes soignee et production volontairement restreinte.', 'price_cents' => 5490, 'currency' => 'EUR', 'is_featured' => true, 'image_path' => 'products/atelier-limited-tee.jpg', 'product_category_id' => $categoryMap[3]->id],
        ])->each(function (array $productData): void {
            Product::create($productData);
        });
    }
}
