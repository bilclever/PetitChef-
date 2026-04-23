<?php

namespace Database\Seeders;

use App\Models\Dish;
use App\Models\User;
use Illuminate\Database\Seeder;

class DishSeeder extends Seeder
{
    public function run(): void
    {
        $cooks = User::where('role', 'cook')
            ->where('approval_status', 'approved')
            ->pluck('id', 'name');

        if ($cooks->isEmpty()) {
            $this->command->warn('Aucun cuisinier approuvé trouvé. Lance d\'abord DatabaseSeeder.');
            return;
        }

        $cookIds = $cooks->values()->toArray();
        // Répartir les plats entre les cuisiniers disponibles
        $c0 = $cookIds[0];
        $c1 = $cookIds[1] ?? $cookIds[0];
        $c2 = $cookIds[2] ?? $cookIds[0];

        $dishes = [
            // ── Cuisinier 1 ──────────────────────────────────────────────────
            [
                'cook_id'     => $c0,
                'name'        => 'Riz gras au poulet',
                'description' => 'Riz cuit dans une sauce tomate épicée avec morceaux de poulet grillé, oignons et épices locales.',
                'price'       => 2500,
                'quantity'    => 15,
                'emoji'       => '🍚',
                'is_of_day'   => true,
                'is_active'   => true,
            ],
            [
                'cook_id'     => $c0,
                'name'        => 'Fufu de manioc + sauce gombo',
                'description' => 'Fufu de manioc pilé servi avec une sauce gombo aux crevettes et poisson fumé.',
                'price'       => 2000,
                'quantity'    => 10,
                'emoji'       => '🫕',
                'is_of_day'   => false,
                'is_active'   => true,
            ],
            [
                'cook_id'     => $c0,
                'name'        => 'Pâte de maïs + sauce arachide',
                'description' => 'Pâte blanche de maïs accompagnée d\'une sauce arachide onctueuse avec viande de bœuf.',
                'price'       => 1800,
                'quantity'    => 12,
                'emoji'       => '🥣',
                'is_of_day'   => false,
                'is_active'   => true,
            ],
            [
                'cook_id'     => $c0,
                'name'        => 'Brochettes de bœuf',
                'description' => 'Brochettes de bœuf marinées aux épices, grillées au charbon. Servies avec oignons et piment.',
                'price'       => 1500,
                'quantity'    => 20,
                'emoji'       => '🍢',
                'is_of_day'   => false,
                'is_active'   => true,
            ],
            [
                'cook_id'     => $c0,
                'name'        => 'Soupe de poisson',
                'description' => 'Soupe légère à base de poisson frais, tomates, oignons et épices. Servie avec du pain.',
                'price'       => 2200,
                'quantity'    => 8,
                'emoji'       => '🍲',
                'is_of_day'   => false,
                'is_active'   => true,
            ],

            // ── Cuisinier 2 ──────────────────────────────────────────────────
            [
                'cook_id'     => $c1,
                'name'        => 'Alloco + poisson frit',
                'description' => 'Bananes plantains frites dorées accompagnées de poisson frit croustillant et sauce pimentée.',
                'price'       => 1500,
                'quantity'    => 18,
                'emoji'       => '🍌',
                'is_of_day'   => true,
                'is_active'   => true,
            ],
            [
                'cook_id'     => $c1,
                'name'        => 'Jollof rice au poulet',
                'description' => 'Riz jollof parfumé cuit avec tomates, poivrons et épices. Accompagné de poulet rôti.',
                'price'       => 3000,
                'quantity'    => 10,
                'emoji'       => '🍛',
                'is_of_day'   => false,
                'is_active'   => true,
            ],
            [
                'cook_id'     => $c1,
                'name'        => 'Haricots rouges + gari',
                'description' => 'Haricots rouges mijotés avec huile de palme et épices, servis avec gari et poisson fumé.',
                'price'       => 1200,
                'quantity'    => 25,
                'emoji'       => '🫘',
                'is_of_day'   => false,
                'is_active'   => true,
            ],
            [
                'cook_id'     => $c1,
                'name'        => 'Poulet DG',
                'description' => 'Poulet sauté avec plantains mûrs, légumes et épices. Plat festif camerounais très apprécié.',
                'price'       => 3500,
                'quantity'    => 6,
                'emoji'       => '🍗',
                'is_of_day'   => false,
                'is_active'   => true,
            ],
            [
                'cook_id'     => $c1,
                'name'        => 'Akara (beignets de haricots)',
                'description' => 'Beignets croustillants de haricots blancs frits, assaisonnés d\'oignons et piment. Idéal en snack.',
                'price'       => 500,
                'quantity'    => 30,
                'emoji'       => '🧆',
                'is_of_day'   => false,
                'is_active'   => true,
            ],

            // ── Cuisinier 3 ──────────────────────────────────────────────────
            [
                'cook_id'     => $c2,
                'name'        => 'Thiéboudienne',
                'description' => 'Plat national sénégalais : riz au poisson avec légumes variés, sauce tomate et épices.',
                'price'       => 3000,
                'quantity'    => 8,
                'emoji'       => '🐟',
                'is_of_day'   => true,
                'is_active'   => true,
            ],
            [
                'cook_id'     => $c2,
                'name'        => 'Mafé de bœuf',
                'description' => 'Ragoût de bœuf en sauce arachide épaisse avec légumes. Servi avec riz blanc.',
                'price'       => 2800,
                'quantity'    => 10,
                'emoji'       => '🥩',
                'is_of_day'   => false,
                'is_active'   => true,
            ],
            [
                'cook_id'     => $c2,
                'name'        => 'Yassa poulet',
                'description' => 'Poulet mariné au citron et oignons caramélisés, mijoté lentement. Servi avec riz.',
                'price'       => 2500,
                'quantity'    => 12,
                'emoji'       => '🍋',
                'is_of_day'   => false,
                'is_active'   => true,
            ],
            [
                'cook_id'     => $c2,
                'name'        => 'Egusi soup + eba',
                'description' => 'Soupe de graines de courge avec légumes-feuilles et viande, servie avec eba (gari cuit).',
                'price'       => 2000,
                'quantity'    => 14,
                'emoji'       => '🥬',
                'is_of_day'   => false,
                'is_active'   => true,
            ],
            [
                'cook_id'     => $c2,
                'name'        => 'Bouillie de mil sucrée',
                'description' => 'Bouillie onctueuse de mil avec lait, sucre et vanille. Petit-déjeuner traditionnel.',
                'price'       => 800,
                'quantity'    => 20,
                'emoji'       => '🥛',
                'is_of_day'   => false,
                'is_active'   => true,
            ],
        ];

        foreach ($dishes as $dish) {
            Dish::create($dish);
        }

        $this->command->info('✅ ' . count($dishes) . ' plats créés avec succès !');
    }
}
