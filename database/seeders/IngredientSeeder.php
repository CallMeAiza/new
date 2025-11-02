<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;


class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
            $ingredients = [
                'CHICKEN LOAF' => [
                    'price' => 37.00,
                    'category' => 'Meat',
                    'unit' => 'pack/s',
                    'quantity' => 46,    
                ],
                'ENERGEN' => [
                    'price' => 100.00,
                    'category' => 'Dairy',
                    'unit' => 'box/es',
                    'quantity' => 29,
                ],
                'SARDINES' => [
                    'price' => 23.00,
                    'category' => 'Fish',
                    'unit' => 'can/s',
                    'quantity' => 44,
                ],
                'HOTDOG' => [
                    'price' => 135.00,
                    'category' => 'Meat',
                    'unit' => 'pack/s',
                    'quantity' => 12,
                ],
                'HAM' => [
                    'price' => 60.00,
                    'category' => 'Meat',
                    'unit' => 'pack/s',
                    'quantity' => 30,
                ],
                'TOMATOES' => [
                    'price' => 80.00,
                    'category' => 'Vegetables and Legumes',
                    'unit' => 'kg',
                    'quantity' => 12,
                ],
                'SIBUYAS DAHONAN' => [
                    'price' => 150.00,
                    'category' => 'Vegetables and Legumes',
                    'unit' => 'kg',
                    'quantity' => 1,
                ],
                'GINISA MIX' => [
                    'price' => 65.00,
                    'category' => 'Seasonings and Condiments',
                    'unit' => 'pack/s',
                    'quantity' => 3,
                ],
                'SUGAR' => [
                    'price' => 80.00,
                    'category' => 'Pantry Staples',
                    'unit' => 'kg',
                    'quantity' => 3,
                ],
                'SALT' => [
                    'price' => 20.00,
                    'category' => 'Pantry Staples',
                    'unit' => 'kg',
                    'quantity' => 3,
                ],
                'ONION' => [
                    'price' => 130.00,
                    'category' => 'Vegetables and Legumes',
                    'unit' => 'kg',
                    'quantity' => 3,
                ],
                'GARLIC' => [
                    'price' => 130.00,
                    'category' => 'Vegetables and Legumes',
                    'unit' => 'kg',
                    'quantity' => 3,
                ],
                'CHILI PEPPER' => [
                    'price' => 140.00,
                    'category' => 'Vegetables and Legumes',
                    'unit' => 'kg',
                    'quantity' => 1,
                ],
                'GINGER' => [
                    'price' => 170.00,
                    'category' => 'Vegetables and Legumes',
                    'unit' => 'kg',
                    'quantity' => 1,
                ],
                'PATIS' => [
                    'price' => 220.00,
                    'category' => 'Seasonings and Condiments',
                    'unit' => 'gallon/s',
                    'quantity' => 2,
                ],
                'VINEGAR' => [
                    'price' => 180.00,
                    'category' => 'Seasonings and Condiments',
                    'unit' => 'gallon/s',
                    'quantity' => 1,
                ],
                'MATCHES' => [
                    'price' => 25.00,
                    'category' => 'Pantry Staples',
                    'unit' => 'box/es',
                    'quantity' => 1,
                ],
                'OYSTER SAUCE' => [
                    'price' => 210.00,
                    'category' => 'Seasonings and Condiments',
                    'unit' => 'bottle/s',
                    'quantity' => 2,
                ],
                'OIL' => [
                    'price' => 170.00,
                    'category' => 'Pantry Staples',
                    'unit' => 'container/s',
                    'quantity' => 2,
                ],
                'BANANA FOR DESSERT' => [
                    'price' => 30.00,
                    'category' => 'Fruits',
                    'unit' => 'kg',
                    'quantity' => 35,
                ],
                'APPLE' => [
                    'price' => 12.00,
                    'category' => 'Fruits',
                    'unit' => 'pack/s',
                    'quantity' => 145,
                ],
                'LEMONSITO' => [
                    'price' => 80.00,
                    'category' => 'Fruits',
                    'unit' => 'kg',
                    'quantity' => 6,
                ],
                'FLOUR' => [
                    'price' => 65.00,
                    'category' => 'Dry Goods',
                    'unit' => 'kg',
                    'quantity' => 3,
                ],
                'CORNSTARCH' => [
                    'price' => 60.00,
                    'category' => 'Dry Goods',
                    'unit' => 'kg',
                    'quantity' => 3,
                ],
                'KNORR CUBES' => [
                    'price' => 85.00,
                    'category' => 'Seasonings and Condiments',
                    'unit' => 'pack/s',
                    'quantity' => 2,
                ],
                'BLACK PEPPER' => [
                    'price' => 600.00,
                    'category' => 'Seasonings and Condiments',
                    'unit' => 'kg',
                    'quantity' => 0.25,
                ],
                'BANANA FOR SNACK' => [
                    'price' => 40.00,
                    'category' => 'Fruits',
                    'unit' => 'kg',
                    'quantity' => 30,
                ],
                'MELON' => [
                    'price' => 40.00,
                    'category' => 'Fruits',
                    'unit' => 'kg',
                    'quantity' => 30,
                ],
                'CONDENSED MILK' => [
                    'price' => 120.00,
                    'category' => 'Dairy',
                    'unit' => 'can/s',
                    'quantity' => 2,
                ],
                'TABLIA' => [
                    'price' => 40.00,
                    'category' => 'Dry Goods',
                    'unit' => 'pack/s',
                    'quantity' => 4,
                ],
                'PINEAPPLE' => [
                    'price' => 50.00,
                    'category' => 'Fruits',
                    'unit' => 'kg',
                    'quantity' => 20,
                ],
                    'ORANGE' => [
                        'price' => 10.00,
                        'category' => 'Fruits',
                        'unit' => 'pcs',
                        'quantity' => 145,
                    ],
                    'CARROTS' => [
                        'price' => 80.00,
                        'category' => 'Vegetables and Legumes',
                        'unit' => 'kg',
                        'quantity' => 8,
                    ],
                    'POTATO' => [
                        'price' => 70.00,
                        'category' => 'Vegetables and Legumes',
                        'unit' => 'kg',
                        'quantity' => 8,
                    ],
                    'CHOPSOUEY' => [
                        'price' => 100.00,
                        'category' => 'Vegetables and Legumes',
                        'unit' => 'kg',
                        'quantity' => 14,
                    ],
                    'SQUASH' => [
                        'price' => 20.00,
                        'category' => 'Vegetables and Legumes',
                        'unit' => 'kg',
                        'quantity' => 22,
                    ],
                    'BATONG' => [
                        'price' => 80.00,
                        'category' => 'Vegetables and Legumes',
                        'unit' => 'kg',
                        'quantity' => 12,
                    ],
                    'TALONG' => [
                        'price' => 80.00,
                        'category' => 'Vegetables and Legumes',
                        'unit' => 'kg',
                        'quantity' => 12,
                    ],
                    'MONGOES' => [
                        'price' => 80.00,
                        'category' => 'Vegetables and Legumes',
                        'unit' => 'kg',
                        'quantity' => 4,
                    ],
                    'BAGIUO BEANS' => [
                        'price' => 80.00,
                        'category' => 'Vegetables and Legumes',
                        'unit' => 'kg',
                        'quantity' => 14,
                    ],
                    'ALUGBATI' => [
                        'price' => 60.00,
                        'category' => 'Vegetables and Legumes',
                        'unit' => 'kg',
                        'quantity' => 4,
                    ],
                    'BUWAD' => [
                        'price' => 240.00,
                        'category' => 'Fish',
                        'unit' => 'kg',
                        'quantity' => 6,
                    ],
                    'KAMUNGGAY' => [
                        'price' => 100.00,
                        'category' => 'Vegetables and Legumes',
                        'unit' => 'kg',
                        'quantity' => 1,
                    ],
                    'PITCHAY' => [
                        'price' => 50.00,
                        'category' => 'Vegetables and Legumes',
                        'unit' => 'kg',
                        'quantity' => 2,
                    ],
                    'CABBAGE' => [
                        'price' => 40.00,
                        'category' => 'Vegetables and Legumes',
                        'unit' => 'kg',
                        'quantity' => 2,
                    ],
                    'GROUNDPORK' => [
                        'price' => 308.00,
                        'category' => 'Meat',
                        'unit' => 'kg',
                        'quantity' => 8,
                    ],
                    'CHICKEN' => [
                        'price' => 210.00,
                        'category' => 'Meat',
                        'unit' => 'kg',
                        'quantity' => 60,
                    ],
                    'PORKCHOP' => [
                        'price' => 368.00,
                        'category' => 'Meat',
                        'unit' => 'kg',
                        'quantity' => 13,
                    ],
                    'FISH' => [
                        'price' => 220.00,
                        'category' => 'Fish',
                        'unit' => 'kg',
                        'quantity' => 35,
                    ],
                    'PORK MENUDO' => [
                        'price' => 360.00,
                        'category' => 'Meat',
                        'unit' => 'kg',
                        'quantity' => 8,
                    ],
                    'GATA' => [
                        'price' => 180.00,
                        'category' => 'Dairy',
                        'unit' => 'can/s',
                        'quantity' => 3,
                    ],
                    'PAKBIT' => [
                        'price' => 210.00,
                        'category' => 'Vegetables and Legumes',
                        'unit' => 'kg',
                        'quantity' => 12,
                    ],
                    'KETCHUP' => [
                        'price' => 100.00,
                        'category' => 'Seasonings and Condiments',
                        'unit' => 'gallon/s',
                        'quantity' => 1,
                    ],
                    'SOTANGHON' => [
                        'price' => 380.00,
                        'category' => 'Dry Goods',
                        'unit' => 'kg',
                        'quantity' => 4,
                    ],
                    'KAMOTE' => [
                        'price' => 45.00,
                        'category' => 'Vegetables and Legumes',
                        'unit' => 'kg',
                        'quantity' => 5,
                    ],
                    'COCO MAMA GATA' => [
                        'price' => 65.00,
                        'category' => 'Dairy',
                        'unit' => 'pack/s',
                        'quantity' => 6,
                    ],
                    'CHORIZO' => [
                        'price' => 210.00,
                        'category' => 'Meat',
                        'unit' => 'kg',
                        'quantity' => 18,

                    ],
                    'LANDANG' => [
                        'price' => 150.00,
                        'category' => 'Dry Goods',
                        'unit' => 'pack/s',
                        'quantity' => 1,
                    ],
                    'SAGO' => [
                        'price' => 100.00,
                        'category' => 'Dry Goods',
                        'unit' => 'pack/s',
                        'quantity' => 1,
                    ],
                    'UBE' => [
                        'price' => 250.00,
                        'category' => 'Fruits',
                        'unit' => 'kg',
                        'quantity' => 2,
                    ],
                    'EGGS' => [
                        'price' => 250,
                        'category' => 'Dairy',
                        'unit' => 'tray/s',
                        'quantity' => 17,
                    ],
                    'AMPALAYA' => [
                        'price' => 120,
                        'category' => 'Vegetables and Legumes',
                        'unit' => 'tray/s',
                        'quantity' => 17,
                    ], 
                    'ODONG' => [
                        'price' => 30,
                        'category' => 'Dry Goods',
                        'unit' => 'pack/s',
                        'quantity' => 12,
                    ],
                    'SARI-SARI' => [
                        'price' => 80,
                        'category' => 'Vegetables and Legumes',
                        'unit' => 'kg',
                        'quantity' => 12,
                    ],
                    'SAYOTE' => [
                        'price' => 20,
                        'category' => 'Vegetables and Legumes',
                        'unit' => 'pcs',
                        'quantity' => 4,
                    ],
                    'TOMATO PASTE' => [
                        'price' => 40,
                        'category' => 'Seasonings and Condiments',
                        'unit' => 'pack/s',
                        'quantity' => 1,
                    ],
                    'MUSHROOM' => [
                        'price' => 520,
                        'category' => 'Vegetables and Legumes',
                        'unit' => 'can/s',
                        'quantity' => 1,
                    ]
                

            
        ];

        foreach ($ingredients as $ingredientName => $ingredientData) {
            Ingredient::updateOrCreate(
                ['name' => $ingredientName],
                [
                    'category' => $ingredientData['category'],
                    'unit' => $ingredientData['unit'],
                    'price' => $ingredientData['price'],
                    'quantity' => $ingredientData['quantity']
                ]
            );
        }
    }

}

