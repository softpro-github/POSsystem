<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // Assets
            ['code' => '1000', 'name' => 'Cash', 'type' => 'asset'],
            ['code' => '1010', 'name' => 'Bank', 'type' => 'asset'],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => 'asset'],
            ['code' => '1200', 'name' => 'Inventory', 'type' => 'asset'],

            // Liabilities
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability'],
            ['code' => '2100', 'name' => 'Tax Payable', 'type' => 'liability'],
            ['code' => '2200', 'name' => 'Loans Payable', 'type' => 'liability'],

            // Equity
            ['code' => '3000', 'name' => "Owner's Capital", 'type' => 'equity'],
            ['code' => '3100', 'name' => 'Retained Earnings', 'type' => 'equity'],

            // Income
            ['code' => '4000', 'name' => 'Sales Revenue', 'type' => 'income'],
            ['code' => '4100', 'name' => 'Sales Returns & Allowances', 'type' => 'income'],

            // Expenses
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'expense'],
            ['code' => '5100', 'name' => 'Rent', 'type' => 'expense'],
            ['code' => '5200', 'name' => 'Salaries', 'type' => 'expense'],
            ['code' => '5300', 'name' => 'Utilities', 'type' => 'expense'],
            ['code' => '5400', 'name' => 'Cash Short/Over', 'type' => 'expense'],
        ];

        foreach ($accounts as $account) {
            Account::firstOrCreate(['code' => $account['code']], $account);
        }
    }
}
