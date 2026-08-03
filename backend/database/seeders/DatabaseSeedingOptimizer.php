<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class DatabaseSeedingOptimizer extends Seeder
{
    public function run(): void
    {
        $rolePemohon = Role::firstOrCreate(['name' => 'pemohon', 'guard_name' => 'web']);
        $rolePenilai = Role::firstOrCreate(['name' => 'penilai', 'guard_name' => 'web']);

        $now = Carbon::now();
        $password = Hash::make('password');

        $this->command->info('Seeding Users...');
        
        $pemohonIds = [];
        for ($i = 0; $i < 2; $i++) {
            $usersData = [];
            for ($j = 0; $j < 500; $j++) {
                $idx = $i * 500 + $j + 1;
                $usersData[] = [
                    'name' => 'Pemohon ' . $idx,
                    'email' => 'pemohon' . $idx . '@example.com',
                    'password' => $password,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::transaction(function () use ($usersData) {
                DB::table('users')->insert($usersData);
            });
            
            $insertedUsers = DB::table('users')->orderBy('id', 'desc')->take(500)->get();
            $rolesData = [];
            foreach ($insertedUsers as $user) {
                $pemohonIds[] = $user->id;
                $rolesData[] = [
                    'role_id' => $rolePemohon->id,
                    'model_type' => 'App\Models\User',
                    'model_id' => $user->id,
                ];
            }
            DB::table('model_has_roles')->insertOrIgnore($rolesData);
        }

        for ($i = 0; $i < 2; $i++) {
            $usersData = [];
            for ($j = 0; $j < 500; $j++) {
                $idx = $i * 500 + $j + 1;
                $usersData[] = [
                    'name' => 'Penilai ' . $idx,
                    'email' => 'penilai' . $idx . '@example.com',
                    'password' => $password,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::transaction(function () use ($usersData) {
                DB::table('users')->insert($usersData);
            });
            
            $insertedUsers = DB::table('users')->orderBy('id', 'desc')->take(500)->get();
            $rolesData = [];
            foreach ($insertedUsers as $user) {
                $rolesData[] = [
                    'role_id' => $rolePenilai->id,
                    'model_type' => 'App\Models\User',
                    'model_id' => $user->id,
                ];
            }
            DB::table('model_has_roles')->insertOrIgnore($rolesData);
        }

        $this->command->info('Seeding Permohonan...');
        for ($i = 0; $i < 10; $i++) {
            $permohonanData = [];
            for ($j = 0; $j < 1000; $j++) {
                $idx = $i * 1000 + $j + 1;
                $permohonanData[] = [
                    'nomor_permohonan' => 'REQ-' . str_pad($idx, 6, '0', STR_PAD_LEFT) . '-' . Str::random(4),
                    'pemohon_id' => $pemohonIds[array_rand($pemohonIds)],
                    'judul_project' => 'Project ' . $idx,
                    'deskripsi' => 'Deskripsi untuk project ' . $idx,
                    'status' => 'draft',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::transaction(function () use ($permohonanData) {
                DB::table('permohonan')->insert($permohonanData);
            });
        }
    }
}
