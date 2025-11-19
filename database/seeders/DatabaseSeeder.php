<?php

namespace Database\Seeders;

use Database\Seeders\Finance\AccountCategorySeeder;
use Database\Seeders\Finance\ChartOfAccountSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(TeacherSeeder::class);
        $this->call(StudentSeeder::class);
        $this->call(AccountCategorySeeder::class);
        $this->call(ChartOfAccountSeeder::class);
    }
}
