<?php

namespace Database\Seeders;

use App\Models\CashMismatchReason;
use App\Models\ReturnReason;
use Illuminate\Database\Seeder;

class ReasonCodeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Defective / faulty item',
            'Wrong item delivered',
            'Customer changed mind',
            'Damaged in transit',
        ] as $name) {
            ReturnReason::firstOrCreate(['name' => $name]);
        }

        foreach ([
            'Miscounted drawer',
            'Till float error',
            'Unrecorded pay-out',
            'Theft/loss suspected',
        ] as $name) {
            CashMismatchReason::firstOrCreate(['name' => $name]);
        }
    }
}
