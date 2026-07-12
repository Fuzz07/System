<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OfficersRosterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password'); // Default password for new officers
        $sscParty = 'ABANTE PARTY';

        // 1. Executive Board
        $executiveOfficers = [
            ['position' => 'President', 'name' => 'Villacarlos, Jireh Joy A.', 'email' => 'president@mcclawis.edu.ph'],
            ['position' => 'Vice President', 'name' => 'Licardo, Juvy Irish C.', 'email' => 'vp@mcclawis.edu.ph'],
            ['position' => 'Secretary', 'name' => 'Carabio, Margarette B.', 'email' => 'secretary@mcclawis.edu.ph'],
            ['position' => 'Treasurer', 'name' => 'Maru, Florane D.', 'email' => 'treasurer.ssc@mcclawis.edu.ph'],
            ['position' => 'Auditor', 'name' => 'Salvaña, Althea Mae D.', 'email' => 'auditor@mcclawis.edu.ph'],
            ['position' => 'PIO', 'name' => 'Manos, Shanei M.', 'email' => 'pio1@mcclawis.edu.ph'],
            ['position' => 'PIO', 'name' => 'Escala, Marlon', 'email' => 'pio2@mcclawis.edu.ph'],
        ];

        foreach ($executiveOfficers as $officer) {
            $nameParts = explode(',', $officer['name'], 2);
            $lastName = trim($nameParts[0]);
            $firstName = isset($nameParts[1]) ? trim($nameParts[1]) : '';
            $fullname = trim($firstName . ' ' . $lastName);

            User::updateOrCreate(
                ['email' => $officer['email']],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'fullname' => $fullname,
                    'password' => $password,
                    'role' => strtolower($officer['position']) === 'treasurer' ? 'treasurer' : 'officer',
                    'position' => $officer['position'],
                    'party' => $sscParty,
                    'department' => 'SSC General',
                    'status' => 'active'
                ]
            );
        }

        // 2. Department Representatives
        $representativeGroups = [
            'BSBA Representative' => [
                ['name' => 'Tidoso, Hanieson Y.', 'email' => 'rep.bsba1@mcclawis.edu.ph'],
                ['name' => 'Gervacio, Ma. Jeestar D.', 'email' => 'rep.bsba2@mcclawis.edu.ph'],
            ],
            'BSIT Representative' => [
                ['name' => 'Espina, Caramela I.', 'email' => 'rep.bsit1@mcclawis.edu.ph'],
                ['name' => 'Verana, Julie Ann', 'email' => 'rep.bsit2@mcclawis.edu.ph'],
            ],
            'BSHM Representative' => [
                ['name' => 'Mahinay, Kim Marie R.', 'email' => 'rep.bshm1@mcclawis.edu.ph'],
                ['name' => 'Chavez, Juniel M.', 'email' => 'rep.bshm2@mcclawis.edu.ph'],
            ],
            'BSED Representative' => [
                ['name' => 'Cernal, Claire M.', 'email' => 'rep.bsed1@mcclawis.edu.ph'],
                ['name' => 'Aropo, Ferdinand Jr. S.', 'email' => 'rep.bsed2@mcclawis.edu.ph'],
            ],
            'BEED Representative' => [
                ['name' => 'Kaquilala, Janlee V.', 'email' => 'rep.beed1@mcclawis.edu.ph'],
                ['name' => 'Desucatan, Rishelle V.', 'email' => 'rep.beed2@mcclawis.edu.ph'],
            ],
        ];

        foreach ($representativeGroups as $positionName => $representatives) {
            foreach ($representatives as $rep) {
                $nameParts = explode(',', $rep['name'], 2);
                $lastName = trim($nameParts[0]);
                $firstName = isset($nameParts[1]) ? trim($nameParts[1]) : '';
                $fullname = trim($firstName . ' ' . $lastName);
                $deptCode = explode(' ', $positionName)[0]; // e.g. BSIT

                User::updateOrCreate(
                    ['email' => $rep['email']],
                    [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'fullname' => $fullname,
                        'password' => $password,
                        'role' => 'officer',
                        'position' => $positionName,
                        'party' => $sscParty,
                        'department' => $deptCode,
                        'status' => 'active'
                    ]
                );
            }
        }
    }
}
