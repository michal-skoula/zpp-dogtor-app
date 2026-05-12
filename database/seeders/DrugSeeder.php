<?php

namespace Database\Seeders;

use App\Models\Drug;
use Illuminate\Database\Seeder;

class DrugSeeder extends Seeder
{
    public function run(): void
    {
        $drugs = [
            [
                'name' => 'Ibalgin 400',
                'form' => 'tablet',
                'strength' => '400mg',
                'description' => 'Ibuprofen — nesteroidní protizánětlivý lék na bolest a horečku.',
            ],
            [
                'name' => 'Paralen 500',
                'form' => 'tablet',
                'strength' => '500mg',
                'description' => 'Paracetamol — analgetikum a antipyretikum bez rizika žaludečního dráždění.',
            ],
            [
                'name' => 'Aspirin',
                'form' => 'tablet',
                'strength' => '500mg',
                'description' => 'Kyselina acetylsalicylová — bolest, horečka, zánět.',
            ],
            [
                'name' => 'Nurofen',
                'form' => 'tablet',
                'strength' => '200mg',
                'description' => 'Ibuprofen v nižší dávce, vhodný pro mírné bolesti.',
            ],
            [
                'name' => 'No-Spa',
                'form' => 'tablet',
                'strength' => '40mg',
                'description' => 'Drotaverin — spasmolytikum na křeče hladké svaloviny.',
            ],
            [
                'name' => 'ACC Long',
                'form' => 'šumivá tableta',
                'strength' => '600mg',
                'description' => 'Acetylcystein — mukolytikum pro uvolnění hlenu z dýchacích cest.',
            ],
            [
                'name' => 'Smecta',
                'form' => 'prášek',
                'strength' => '3g/sáček',
                'description' => 'Diosmektit — ochrana sliznice při průjmu a podráždění trávicího traktu.',
            ],
            [
                'name' => 'Strepsils',
                'form' => 'pastilka',
                'strength' => '1.2mg/0.6mg',
                'description' => 'Amylmetakresol + dichlorobenzylalkohol — antiseptikum na bolavý krk.',
            ],
            [
                'name' => 'Wobenzym',
                'form' => 'enterosolventní tableta',
                'strength' => '100mg',
                'description' => 'Enzymatická směs — protizánětlivý a imunomodulační účinek.',
            ],
            [
                'name' => 'Coldrex',
                'form' => 'tablet',
                'strength' => '500mg/5mg/20mg',
                'description' => 'Paracetamol + terpin + vitamín C — kombinace na příznaky nachlazení.',
            ],
            [
                'name' => 'Septolete',
                'form' => 'pastilka',
                'strength' => '1mg/1mg',
                'description' => 'Cetylpyridinium + benzokain — lokální antibakteriální účinek v ústní dutině.',
            ],
            [
                'name' => 'Modafen',
                'form' => 'tablet',
                'strength' => '200mg/30mg',
                'description' => 'Ibuprofen + pseudoefedrin — rýma a ucpaný nos při nachlazení.',
                'is_active' => false,
            ],
        ];

        foreach ($drugs as $drug) {
            Drug::firstOrCreate(
                ['name' => $drug['name']],
                array_merge(['is_active' => true], $drug),
            );
        }
    }
}
