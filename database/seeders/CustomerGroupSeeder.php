<?php

namespace Database\Seeders;

use App\Models\CustomerGroup;
use Illuminate\Database\Seeder;

class CustomerGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'name' => 'Gold',
                'slug' => CustomerGroup::GOLD,
                'description' => 'Gold-Kundengruppe',
                'color' => CustomerGroup::DEFAULT_COLORS[CustomerGroup::GOLD],
            ],
            [
                'name' => 'Silber',
                'slug' => CustomerGroup::SILVER,
                'description' => 'Silber-Kundengruppe',
                'color' => CustomerGroup::DEFAULT_COLORS[CustomerGroup::SILVER],
            ],
            [
                'name' => 'Diamond',
                'slug' => CustomerGroup::DIAMOND,
                'description' => 'Diamond-Kundengruppe',
                'color' => CustomerGroup::DEFAULT_COLORS[CustomerGroup::DIAMOND],
            ],
        ];

        foreach ($groups as $group) {
            CustomerGroup::updateOrCreate(
                ['slug' => $group['slug']],
                $group
            );
        }
    }
}
