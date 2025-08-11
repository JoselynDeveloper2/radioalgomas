<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles basados en el sistema actual
        $roles = [
            'super_admin' => 'Super Admin',
            'admin' => 'Administrador', 
            'editor' => 'Editor',
            'author' => 'Autor',
            'subscriber' => 'Suscriptor'
        ];

        foreach ($roles as $name => $displayName) {
            Role::firstOrCreate(['name' => $name], ['guard_name' => 'web']);
        }

        // Asignar permisos por roles
        $this->assignPermissionsByRole();
        
        // Migrar usuarios existentes al sistema de roles
        $this->migrateExistingUsers();
    }

    private function assignPermissionsByRole(): void
    {
        $superAdmin = Role::where('name', 'super_admin')->first();
        $admin = Role::where('name', 'admin')->first();
        $editor = Role::where('name', 'editor')->first();  
        $author = Role::where('name', 'author')->first();
        $subscriber = Role::where('name', 'subscriber')->first();

        // Super Admin - todos los permisos
        $superAdmin->syncPermissions(Permission::all());

        // Admin - casi todos los permisos excepto super admin
        $adminPermissions = Permission::where('name', 'not like', 'shield_%')->get();
        $admin->syncPermissions($adminPermissions);

        // Editor - gestión de artículos, categorías y tags
        $editorPermissions = Permission::where('name', 'like', '%article%')
            ->orWhere('name', 'like', '%category%')
            ->orWhere('name', 'like', '%tag%')
            ->get();
        $editor->syncPermissions($editorPermissions);

        // Author - solo crear y editar sus propios artículos
        $authorPermissions = Permission::whereIn('name', [
            'view_article',
            'create_article', 
            'update_article',
            'view_any_article'
        ])->get();
        $author->syncPermissions($authorPermissions);

        // Subscriber - solo ver
        $subscriberPermissions = Permission::where('name', 'like', 'view_%')->get();
        $subscriber->syncPermissions($subscriberPermissions);
    }

    private function migrateExistingUsers(): void
    {
        // Mapeo de roles antiguos a nuevos
        $roleMapping = [
            User::ROLE_ADMIN => 'admin',
            User::ROLE_EDITOR => 'editor', 
            User::ROLE_AUTHOR => 'author',
            User::ROLE_SUBSCRIBER => 'subscriber'
        ];

        foreach (User::all() as $user) {
            if (isset($roleMapping[$user->role])) {
                $user->assignRole($roleMapping[$user->role]);
            }
        }

        // Asignar super admin al usuario admin principal
        $adminUser = User::where('email', 'admin@tucanaltv.tv')->first();
        if ($adminUser) {
            $adminUser->syncRoles(['super_admin']);
        }
    }
}