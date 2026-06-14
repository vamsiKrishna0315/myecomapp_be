<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class CustomRolesSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Only create roles if they don't already exist
        $this->command->info('Creating roles that match your user form...');
        
        // Define roles exactly as you have them in your user form
        $roles = [
            'admin' => [
                'description' => 'Admin - Full system access',
                'permissions' => [] // Synced to all generated permissions below
            ],
            
            'store_admin' => [
                'description' => 'Store Admin - Store management',
                'permissions' => [
                    // Product management
                    'ViewAny:Product',
                    'View:Product',
                    'Create:Product',
                    'Update:Product',
                    'Delete:Product',
                    
                    // Category management
                    'ViewAny:Category',
                    'View:Category',
                    'Create:Category',
                    'Update:Category',
                    
                    // Order management
                    'ViewAny:Orders',
                    'View:Orders',
                    'Update:Orders',
                    'ViewAny:OrderItems',
                    'View:OrderItems',
                    
                    // Customer management
                    'ViewAny:Customer',
                    'View:Customer',
                    'Update:Customer',
                    
                    // Store management
                    'ViewAny:Store',
                    'View:Store',
                    'Update:Store',
                    
                    // Analytics widgets
                    'View:CustomerStatsOverviewWidget',
                    'View:OrdersStatsOverviewWidget',
                    'View:TotalOrdersWidget',
                    'View:TopSellingProductsWidget',
                    'View:TopCustomersWidget',
                ]
            ],
            
            'store_driver' => [
                'description' => 'Store Driver - Delivery management',
                'permissions' => [
                    // Order delivery management
                    'ViewAny:Orders',
                    'View:Orders',
                    'Update:Orders',
                    'ViewAny:OrderStatusTracking',
                    'View:OrderStatusTracking',
                    'Create:OrderStatusTracking',
                    'Update:OrderStatusTracking',
                    
                    // Customer info for delivery
                    'View:Customer',
                    
                    // Driver specific
                    'View:Driver',
                    'Update:Driver',
                    
                    // Feedback viewing (NEW)
                    'ViewAny:Feedback',
                    'View:Feedback',
                    
                    // Driver widgets
                    'View:DriverStatsOverviewWidget',
                    'View:DriverAnalyticsWidget',
                ]
            ],
            
            'store_vendor' => [
                'description' => 'Store Vendor - Product and store management',
                'permissions' => [
                    // Product management for their store
                    'ViewAny:Product',
                    'View:Product',
                    'Create:Product',
                    'Update:Product',
                    
                    // Their orders only
                    'ViewAny:Orders',
                    'View:Orders',
                    'Update:Orders',
                    
                    // Store info
                    'View:Store',
                    'Update:Store',
                    
                    // Basic widgets
                    'View:OrdersStatsOverviewWidget',
                    'View:TotalOrdersWidget',
                    'View:TopSellingProductsWidget',
                ]
            ],
            
            'user' => [
                'description' => 'Regular User - Limited access',
                'permissions' => [
                    // Basic user permissions - very limited
                    'View:Customer', // Can view their own profile
                ]
            ]
        ];

        foreach ($roles as $roleName => $roleData) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $permissionsToSync = $roleName === 'admin'
                ? Permission::pluck('name')->all()
                : Permission::whereIn('name', $roleData['permissions'])->pluck('name')->all();

            if (! empty($permissionsToSync)) {
                $role->syncPermissions($permissionsToSync);
            }

            $this->command->info("Synced role: {$roleName} with " . count($permissionsToSync) . ' permissions');
        }
    }
}
