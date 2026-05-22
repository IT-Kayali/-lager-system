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
            ],
            [
                'name' => 'Silber',
                'slug' => CustomerGroup::SILVER,
                'description' => 'Silber-Kundengruppe',
            ],
            [
                'name' => 'Diamond',
                'slug' => CustomerGroup::DIAMOND,
                'description' => 'Diamond-Kundengruppe',
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
