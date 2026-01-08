<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar ou atualizar Super Administrador
        $data = [
            'name' => 'Super Administrador',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ];

        // Adiciona status se a coluna existir
        if (Schema::hasColumn('users', 'status')) {
            $data['status'] = 'active';
        }

        // Adiciona is_super_admin se a coluna existir
        if (Schema::hasColumn('users', 'is_super_admin')) {
            $data['is_super_admin'] = true;
        }

        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            $data
        );

        // Força a atualização do is_super_admin (caso o updateOrCreate não tenha funcionado)
        if (Schema::hasColumn('users', 'is_super_admin')) {
            $superAdmin->is_super_admin = true;
            if (Schema::hasColumn('users', 'status')) {
                $superAdmin->status = 'active';
            }
            $superAdmin->save();
        }

        // Garante que o super admin não esteja vinculado a nenhuma empresa
        if (Schema::hasTable('user_company')) {
            $superAdmin->companies()->detach();
        }

        // Recarrega o modelo para garantir que os dados estão atualizados
        $superAdmin->refresh();

        $this->command->info('Super Administrador criado/atualizado com sucesso!');
        $this->command->info('E-mail: admin@admin.com');
        $this->command->info('Senha: password');
        $this->command->info('is_super_admin: ' . ($superAdmin->is_super_admin ?? 'null'));
    }
}
