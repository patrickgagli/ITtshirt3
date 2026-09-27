<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_catalogue_page_lists_available_products(): void
    {
        Product::factory()->create([
            'name' => 'Atelier Indigo',
            'slug' => 'atelier-indigo',
            'description' => 'T-shirt en coton lourd avec encolure epaisse.',
            'price_cents' => 3200,
            'currency' => 'EUR',
            'is_featured' => false,
        ]);

        Product::factory()->featured()->create([
            'name' => 'Studio Sable',
            'slug' => 'studio-sable',
            'description' => 'Coupe droite et teinte sable pour la vitrine.',
            'price_cents' => 3400,
            'currency' => 'EUR',
        ]);

        $response = $this->get('/catalogue');

        $response
            ->assertOk()
            ->assertViewIs('products.index')
            ->assertSeeInOrder(['Studio Sable', 'Atelier Indigo'])
            ->assertSee('34,00 EUR');
    }

    public function test_the_catalogue_page_handles_an_empty_product_list(): void
    {
        $response = $this->get('/catalogue');

        $response
            ->assertOk()
            ->assertSee('Aucun produit n\'est disponible pour le moment.', false);
    }

    public function test_the_catalogue_page_shows_the_category_name(): void
    {
        $category = ProductCategory::factory()->create([
            'name' => 'Signatures',
            'slug' => 'signatures',
        ]);

        Product::factory()->create([
            'name' => 'Studio Sable',
            'slug' => 'studio-sable',
            'description' => 'Coupe droite et teinte sable pour la vitrine.',
            'price_cents' => 3400,
            'currency' => 'EUR',
            'is_featured' => true,
            'product_category_id' => $category->id,
        ]);

        $response = $this->get('/catalogue');

        $response
            ->assertOk()
            ->assertSee('Signatures');
    }

    public function test_the_catalogue_page_shows_image_placeholder_when_no_image(): void
    {
        Product::factory()->create([
            'name' => 'Ligne Craie',
            'slug' => 'ligne-craie',
            'description' => 'Base claire a porter seule.',
            'price_cents' => 2900,
            'currency' => 'EUR',
            'image_path' => null,
        ]);

        $response = $this->get('/catalogue');

        $response
            ->assertOk()
            ->assertSee('product-placeholder-art', false);
    }

    public function test_the_product_detail_page_shows_a_single_product_by_slug(): void
    {
        Product::factory()->create([
            'name' => 'Atelier Indigo',
            'slug' => 'atelier-indigo',
            'description' => 'T-shirt en coton lourd avec encolure epaisse.',
            'price_cents' => 3200,
            'currency' => 'EUR',
            'is_featured' => false,
        ]);

        $response = $this->get('/catalogue/atelier-indigo');

        $response
            ->assertOk()
            ->assertViewIs('products.show')
            ->assertSee('Atelier Indigo')
            ->assertSee('T-shirt en coton lourd avec encolure epaisse.')
            ->assertSee('32,00 EUR');
    }

    public function test_the_product_detail_page_includes_category_information(): void
    {
        $category = ProductCategory::factory()->create([
            'name' => 'Signatures',
            'slug' => 'signatures',
        ]);

        Product::factory()->create([
            'name' => 'Studio Sable',
            'slug' => 'studio-sable',
            'description' => 'Coupe droite et teinte sable pour la vitrine.',
            'price_cents' => 3400,
            'currency' => 'EUR',
            'is_featured' => true,
            'product_category_id' => $category->id,
        ]);

        $response = $this->get('/catalogue/studio-sable');

        $response
            ->assertOk()
            ->assertSee('Signatures')
            ->assertSee('Mis en avant', false);
    }

    public function test_the_product_detail_page_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->get('/catalogue/nonexistent-product');

        $response->assertNotFound();
    }

    // ── Category filter ───────────────────────────────────────────────────────

    public function test_catalogue_shows_category_filter_bar_when_categories_exist(): void
    {
        $category = ProductCategory::factory()->create(['name' => 'Signatures', 'slug' => 'signatures']);
        Product::factory()->create(['product_category_id' => $category->id]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Signatures')
            ->assertSee('Toutes les catégories', false);
    }

    public function test_catalogue_filters_products_by_category_slug(): void
    {
        $cat1 = ProductCategory::factory()->create(['name' => 'Essentiels', 'slug' => 'essentiels']);
        $cat2 = ProductCategory::factory()->create(['name' => 'Graphiques', 'slug' => 'graphiques']);

        Product::factory()->create(['name' => 'Tee Essentiel', 'slug' => 'tee-essentiel', 'product_category_id' => $cat1->id]);
        Product::factory()->create(['name' => 'Tee Graphique', 'slug' => 'tee-graphique', 'product_category_id' => $cat2->id]);

        $response = $this->get(route('products.index', ['category' => 'essentiels']));

        $response
            ->assertOk()
            ->assertSee('Tee Essentiel')
            ->assertDontSee('Tee Graphique');
    }

    public function test_catalogue_filter_hides_products_outside_the_active_category(): void
    {
        $cat = ProductCategory::factory()->create(['slug' => 'signatures']);
        Product::factory()->create(['name' => 'Prod In', 'slug' => 'prod-in', 'product_category_id' => $cat->id]);
        Product::factory()->create(['name' => 'Prod Out', 'slug' => 'prod-out', 'product_category_id' => null]);

        $this->get(route('products.index', ['category' => 'signatures']))
            ->assertOk()
            ->assertSee('Prod In')
            ->assertDontSee('Prod Out');
    }

    public function test_catalogue_with_active_filter_highlights_active_category_pill(): void
    {
        $cat = ProductCategory::factory()->create(['name' => 'Signatures', 'slug' => 'signatures']);

        $this->get(route('products.index', ['category' => 'signatures']))
            ->assertOk()
            ->assertSee('Signatures');
    }

    public function test_catalogue_without_filter_shows_all_products(): void
    {
        $cat1 = ProductCategory::factory()->create(['slug' => 'cat-a']);
        $cat2 = ProductCategory::factory()->create(['slug' => 'cat-b']);

        Product::factory()->create(['name' => 'Alpha', 'slug' => 'alpha', 'product_category_id' => $cat1->id]);
        Product::factory()->create(['name' => 'Beta',  'slug' => 'beta',  'product_category_id' => $cat2->id]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Alpha')
            ->assertSee('Beta');
    }

    public function test_catalogue_with_active_filter_shows_filtered_count_in_hero(): void
    {
        $cat = ProductCategory::factory()->create(['name' => 'Signatures', 'slug' => 'signatures']);
        Product::factory()->count(3)->create(['product_category_id' => $cat->id]);

        $this->get(route('products.index', ['category' => 'signatures']))
            ->assertOk()
            ->assertSee('Signatures');
    }

    public function test_catalogue_returns_404_for_unknown_category_slug(): void
    {
        $this->get(route('products.index', ['category' => 'nonexistent-category']))
            ->assertNotFound();
    }

    public function test_catalogue_empty_state_with_filter_shows_category_name_and_clear_link(): void
    {
        $cat = ProductCategory::factory()->create(['name' => 'Signatures', 'slug' => 'signatures']);

        $this->get(route('products.index', ['category' => 'signatures']))
            ->assertOk()
            ->assertSee('Signatures')
            ->assertSee('Voir tous les produits', false);
    }

    // ── Stock display (cards + availability pills) ────────────────────────────

    public function test_catalogue_shows_rupture_badge_for_out_of_stock_product(): void
    {
        Product::factory()->outOfStock()->create(['name' => 'Produit Épuisé', 'slug' => 'produit-epuise']);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('is-out-of-stock', false)   // CSS class on image wrapper
            ->assertSee('Rupture', false);            // overlay badge text
    }

    public function test_catalogue_shows_stock_limite_badge_for_low_stock_product(): void
    {
        Product::factory()->lowStock(2)->create(['name' => 'Produit Limité', 'slug' => 'produit-limite']);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('is-low-stock', false)        // CSS class on badge
            ->assertSee('2 restant(s)', false);
    }

    public function test_catalogue_shows_no_stock_badge_for_untracked_product(): void
    {
        Product::factory()->create(['name' => 'Produit Normal', 'slug' => 'produit-normal']);

        $response = $this->get(route('products.index'));
        $response->assertOk()
                 ->assertDontSee('is-out-of-stock', false)
                 ->assertDontSee('is-low-stock', false);
    }

    public function test_catalogue_out_of_stock_card_shows_indisponible_cta(): void
    {
        Product::factory()->outOfStock()->create(['name' => 'CTA Test', 'slug' => 'cta-test']);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Indisponible', false);
    }

    public function test_catalogue_in_stock_card_shows_voir_cta(): void
    {
        Product::factory()->inStock(10)->create(['name' => 'Voir Test', 'slug' => 'voir-test']);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Voir', false);
    }

    public function test_catalogue_passes_stock_counts_to_view(): void
    {
        Product::factory()->inStock(20)->count(3)->create();
        Product::factory()->lowStock(2)->count(2)->create();
        Product::factory()->outOfStock()->count(1)->create();
        Product::factory()->create(); // untracked — counts as in_stock

        $this->get(route('products.index'))
            ->assertOk()
            ->assertViewHas('stockCounts', function (array $counts) {
                return $counts['all']          === 7
                    && $counts['in_stock']     === 4  // 3 inStock + 1 untracked
                    && $counts['low_stock']    === 2
                    && $counts['out_of_stock'] === 1;
            });
    }

    public function test_catalogue_stock_counts_respect_active_category(): void
    {
        $cat = ProductCategory::factory()->create(['slug' => 'cat-counts']);
        Product::factory()->inStock(10)->count(2)->create(['product_category_id' => $cat->id]);
        Product::factory()->outOfStock()->create(['product_category_id' => $cat->id]);
        // products outside category — must not appear in counts
        Product::factory()->inStock(10)->count(5)->create();

        $this->get(route('products.index', ['category' => 'cat-counts']))
            ->assertOk()
            ->assertViewHas('stockCounts', function (array $counts) {
                return $counts['all']          === 3
                    && $counts['in_stock']     === 2
                    && $counts['out_of_stock'] === 1;
            });
    }

    public function test_product_detail_shows_disponible_for_untracked_product(): void
    {
        Product::factory()->create(['slug' => 'untracked-show', 'track_stock' => false]);

        $this->get(route('products.show', 'untracked-show'))
            ->assertOk()
            ->assertSee('Disponible', false);
    }

    public function test_product_detail_shows_rupture_for_out_of_stock_product(): void
    {
        Product::factory()->outOfStock()->create(['slug' => 'out-of-stock-show']);

        $this->get(route('products.show', 'out-of-stock-show'))
            ->assertOk()
            ->assertSee('Rupture de stock', false);
    }

    public function test_product_detail_shows_stock_limite_for_low_stock_product(): void
    {
        Product::factory()->lowStock(2)->create(['slug' => 'low-stock-show']);

        $this->get(route('products.show', 'low-stock-show'))
            ->assertOk()
            ->assertSee('Stock limité (2)', false);
    }

    public function test_product_detail_shows_en_stock_for_tracked_in_stock_product(): void
    {
        Product::factory()->inStock(50)->create(['slug' => 'in-stock-show']);

        $this->get(route('products.show', 'in-stock-show'))
            ->assertOk()
            ->assertSee('En stock (50)', false);
    }

    public function test_product_detail_hides_add_to_cart_button_when_out_of_stock(): void
    {
        Product::factory()->outOfStock()->create(['slug' => 'no-cart-show']);

        $this->get(route('products.show', 'no-cart-show'))
            ->assertOk()
            ->assertSee('Rupture de stock', false)
            ->assertDontSee('data-vue="add-to-cart"', false);
    }

    public function test_product_detail_out_of_stock_shows_image_overlay_class(): void
    {
        Product::factory()->outOfStock()->create(['slug' => 'overlay-oos']);

        $this->get(route('products.show', 'overlay-oos'))
            ->assertOk()
            ->assertSee('product-image-box is-out-of-stock', false);
    }

    public function test_product_detail_out_of_stock_shows_rupture_overlay_badge(): void
    {
        Product::factory()->outOfStock()->create(['slug' => 'badge-oos']);

        $this->get(route('products.show', 'badge-oos'))
            ->assertOk()
            ->assertSee('is-out-of-stock', false)
            ->assertSee('Rupture', false);
    }

    public function test_product_detail_low_stock_shows_low_stock_overlay_badge(): void
    {
        Product::factory()->lowStock(3)->create(['slug' => 'badge-low']);

        $this->get(route('products.show', 'badge-low'))
            ->assertOk()
            ->assertSee('is-low-stock', false)
            ->assertSee('3 restant(s)', false);
    }

    public function test_product_detail_low_stock_shows_urgency_banner(): void
    {
        Product::factory()->lowStock(4)->create(['slug' => 'urgency-low']);

        $this->get(route('products.show', 'urgency-low'))
            ->assertOk()
            ->assertSee('commandez vite', false)
            ->assertSee('4', false);
    }

    public function test_product_detail_in_stock_shows_no_overlay_and_no_urgency(): void
    {
        Product::factory()->inStock(20)->create(['slug' => 'clean-in-stock']);

        $this->get(route('products.show', 'clean-in-stock'))
            ->assertOk()
            ->assertDontSee('is-out-of-stock', false)
            ->assertDontSee('commandez vite', false);
    }

    // ── Search ────────────────────────────────────────────────────────────────

    public function test_catalogue_search_returns_matching_product_by_name(): void
    {
        Product::factory()->create(['name' => 'Chemise Bleue', 'slug' => 'chemise-bleue']);
        Product::factory()->create(['name' => 'Pantalon Noir', 'slug' => 'pantalon-noir']);

        $this->get(route('products.index', ['q' => 'Bleue']))
            ->assertOk()
            ->assertSee('Chemise Bleue')
            ->assertDontSee('Pantalon Noir');
    }

    public function test_catalogue_search_returns_matching_product_by_description(): void
    {
        Product::factory()->create([
            'name'        => 'Tee Secret',
            'slug'        => 'tee-secret',
            'description' => 'Coton biologique fin.',
        ]);
        Product::factory()->create([
            'name'        => 'Tee Autre',
            'slug'        => 'tee-autre',
            'description' => 'Description ordinaire.',
        ]);

        $this->get(route('products.index', ['q' => 'biologique']))
            ->assertOk()
            ->assertSee('Tee Secret')
            ->assertDontSee('Tee Autre');
    }

    public function test_catalogue_search_with_no_results_shows_empty_state(): void
    {
        Product::factory()->create(['name' => 'Produit Existant', 'slug' => 'produit-existant']);

        $this->get(route('products.index', ['q' => 'xxxxxxnotfound']))
            ->assertOk()
            ->assertSee('xxxxxxnotfound', false)
            ->assertSee('Aucun résultat', false);
    }

    public function test_catalogue_search_preserves_category_filter(): void
    {
        $cat = ProductCategory::factory()->create(['slug' => 'special']);
        Product::factory()->create(['name' => 'Tee Spécial', 'slug' => 'tee-special', 'product_category_id' => $cat->id]);
        Product::factory()->create(['name' => 'Tee Autre',   'slug' => 'tee-autre-cat', 'product_category_id' => null]);

        $this->get(route('products.index', ['category' => 'special', 'q' => 'Tee']))
            ->assertOk()
            ->assertSee('Tee Spécial')
            ->assertDontSee('Tee Autre');
    }

    // ── Sort ──────────────────────────────────────────────────────────────────

    public function test_catalogue_sort_by_name_asc(): void
    {
        Product::factory()->create(['name' => 'Zèbre', 'slug' => 'zebre']);
        Product::factory()->create(['name' => 'Abricot', 'slug' => 'abricot']);

        $response = $this->get(route('products.index', ['sort' => 'name_asc']));
        $response->assertOk()->assertSeeInOrder(['Abricot', 'Zèbre']);
    }

    public function test_catalogue_sort_by_name_desc(): void
    {
        Product::factory()->create(['name' => 'Zèbre', 'slug' => 'zebre-desc']);
        Product::factory()->create(['name' => 'Abricot', 'slug' => 'abricot-desc']);

        $response = $this->get(route('products.index', ['sort' => 'name_desc']));
        $response->assertOk()->assertSeeInOrder(['Zèbre', 'Abricot']);
    }

    public function test_catalogue_sort_by_price_asc(): void
    {
        Product::factory()->create(['name' => 'Cher',       'slug' => 'cher',       'price_cents' => 9900]);
        Product::factory()->create(['name' => 'Bon Marché', 'slug' => 'bon-marche', 'price_cents' =>  990]);

        $response = $this->get(route('products.index', ['sort' => 'price_asc']));
        $response->assertOk()->assertSeeInOrder(['Bon Marché', 'Cher']);
    }

    public function test_catalogue_sort_by_price_desc(): void
    {
        Product::factory()->create(['name' => 'Cher',       'slug' => 'cher-desc',       'price_cents' => 9900]);
        Product::factory()->create(['name' => 'Bon Marché', 'slug' => 'bon-marche-desc', 'price_cents' =>  990]);

        $response = $this->get(route('products.index', ['sort' => 'price_desc']));
        $response->assertOk()->assertSeeInOrder(['Cher', 'Bon Marché']);
    }

    public function test_catalogue_sort_by_stock_shows_available_before_out_of_stock(): void
    {
        Product::factory()->outOfStock()->create(['name' => 'Rupture',    'slug' => 'rupture-sort']);
        Product::factory()->inStock(20)->create(['name' => 'En stock',   'slug' => 'en-stock-sort']);
        Product::factory()->lowStock(2)->create(['name' => 'Stock limité', 'slug' => 'low-sort']);

        $response = $this->get(route('products.index', ['sort' => 'stock']));
        $response->assertOk()->assertSeeInOrder(['En stock', 'Stock limité', 'Rupture']);
    }

    public function test_catalogue_rejects_invalid_sort_and_defaults_to_featured(): void
    {
        Product::factory()->featured()->create(['name' => 'Vedette', 'slug' => 'vedette-invalid']);
        Product::factory()->create(['name' => 'Normal', 'slug' => 'normal-invalid']);

        // featured-first then name: "Normal" then "Vedette" alphabetically... wait, actually:
        // featured first = "Vedette" first, then "Normal"
        $response = $this->get(route('products.index', ['sort' => '__bad__']));
        $response->assertOk()->assertSeeInOrder(['Vedette', 'Normal']);
    }

    // ── Availability filter ───────────────────────────────────────────────────

    public function test_catalogue_availability_in_stock_excludes_out_of_stock_products(): void
    {
        Product::factory()->inStock(10)->create(['name' => 'Dispo',   'slug' => 'dispo-avail']);
        Product::factory()->outOfStock()->create(['name' => 'Rupture', 'slug' => 'rupture-avail']);

        $this->get(route('products.index', ['availability' => 'in_stock']))
            ->assertOk()
            ->assertSee('Dispo')
            ->assertDontSee('Rupture');
    }

    public function test_catalogue_availability_out_of_stock_shows_only_rupture_products(): void
    {
        Product::factory()->inStock(10)->create(['name' => 'En stock',  'slug' => 'stock-oos']);
        Product::factory()->outOfStock()->create(['name' => 'Rupture OOS', 'slug' => 'oos-only']);

        $this->get(route('products.index', ['availability' => 'out_of_stock']))
            ->assertOk()
            ->assertSee('Rupture OOS')
            ->assertDontSee('En stock');
    }

    public function test_catalogue_availability_low_stock_shows_only_low_stock_products(): void
    {
        Product::factory()->inStock(20)->create(['name' => 'Normal Stock', 'slug' => 'normal-ls']);
        Product::factory()->lowStock(2)->create(['name' => 'Faible Stock', 'slug' => 'faible-ls']);
        Product::factory()->outOfStock()->create(['name' => 'Rupture LS',  'slug' => 'rupture-ls']);

        $this->get(route('products.index', ['availability' => 'low_stock']))
            ->assertOk()
            ->assertSee('Faible Stock')
            ->assertDontSee('Normal Stock')
            ->assertDontSee('Rupture LS');
    }

    public function test_catalogue_availability_all_shows_every_product(): void
    {
        Product::factory()->inStock(10)->create(['name'  => 'Dispo All',   'slug' => 'dispo-all']);
        Product::factory()->outOfStock()->create(['name' => 'Rupture All', 'slug' => 'rupture-all']);

        $this->get(route('products.index', ['availability' => 'all']))
            ->assertOk()
            ->assertSee('Dispo All')
            ->assertSee('Rupture All');
    }

    public function test_catalogue_availability_in_stock_excludes_low_stock_products(): void
    {
        Product::factory()->inStock(20)->create(['name' => 'Bien Appro',   'slug' => 'bien-appro']);
        Product::factory()->lowStock(3)->create(['name' => 'Faible Appro', 'slug' => 'faible-appro']);

        $this->get(route('products.index', ['availability' => 'in_stock']))
            ->assertOk()
            ->assertSee('Bien Appro')
            ->assertDontSee('Faible Appro');
    }

    public function test_catalogue_rejects_invalid_availability_and_shows_all(): void
    {
        Product::factory()->inStock(10)->create(['name' => 'Tout Affiché', 'slug' => 'tout-affiche']);

        $this->get(route('products.index', ['availability' => '__bad__']))
            ->assertOk()
            ->assertSee('Tout Affiché');
    }

    // ── Pagination ────────────────────────────────────────────────────────────

    public function test_catalogue_paginates_after_12_products(): void
    {
        Product::factory()->count(13)->sequence(fn ($s) => [
            'name' => 'Produit ' . str_pad($s->index + 1, 2, '0', STR_PAD_LEFT),
            'slug' => 'prod-pag-' . ($s->index + 1),
        ])->create();

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Produit 01')
            ->assertDontSee('Produit 13'); // 13th is on page 2
    }

    public function test_catalogue_page_2_shows_overflow_products(): void
    {
        Product::factory()->count(13)->sequence(fn ($s) => [
            'name' => 'Produit ' . str_pad($s->index + 1, 2, '0', STR_PAD_LEFT),
            'slug' => 'prod-p2-' . ($s->index + 1),
        ])->create();

        $this->get(route('products.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Produit 13');
    }

    public function test_catalogue_search_combined_with_availability_and_sort(): void
    {
        Product::factory()->inStock(20)->create(['name' => 'Alpha Dispo', 'slug' => 'alpha-combined']);
        Product::factory()->outOfStock()->create(['name' => 'Alpha Rupture', 'slug' => 'alpha-oos-combined']);
        Product::factory()->inStock(5)->create(['name' => 'Beta Dispo', 'slug' => 'beta-combined']);

        $this->get(route('products.index', ['q' => 'Alpha', 'availability' => 'in_stock', 'sort' => 'name_asc']))
            ->assertOk()
            ->assertSee('Alpha Dispo')
            ->assertDontSee('Alpha Rupture')
            ->assertDontSee('Beta Dispo');
    }
}
