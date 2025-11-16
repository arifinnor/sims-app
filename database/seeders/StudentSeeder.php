<?php

namespace Database\Seeders;

use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a pool of guardians first
        // Create more guardians than students to allow for variety
        $guardians = Guardian::factory()->count(40)->create();

        // Create students and attach guardians
        $students = Student::factory()->count(20)->create();

        $relationshipTypes = ['Mother', 'Father', 'Legal Guardian', 'Grandmother', 'Grandfather', 'Uncle', 'Aunt', 'Other'];

        foreach ($students as $student) {
            // Each student gets 1-3 guardians
            $numGuardians = fake()->numberBetween(1, 3);

            // Select random guardians from the pool
            $selectedGuardians = $guardians->random(min($numGuardians, $guardians->count()));

            // Randomly select which guardian should be primary (first one by default)
            $primaryGuardianIndex = fake()->numberBetween(0, $selectedGuardians->count() - 1);
            $primaryGuardianId = $selectedGuardians->values()->get($primaryGuardianIndex)->id;

            foreach ($selectedGuardians as $index => $guardian) {
                $isPrimary = $guardian->id === $primaryGuardianId;

                // Use relationship from guardian model or generate one for pivot
                $relationshipType = $guardian->relationship ?? fake()->randomElement($relationshipTypes);

                $student->guardians()->attach($guardian->id, [
                    'relationship_type' => $relationshipType,
                    'is_primary' => $isPrimary,
                ]);
            }
        }
    }
}
