<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["ViewAny:GamificationBadgeRule","View:GamificationBadgeRule","Create:GamificationBadgeRule","Update:GamificationBadgeRule","Delete:GamificationBadgeRule","Restore:GamificationBadgeRule","ForceDelete:GamificationBadgeRule","ForceDeleteAny:GamificationBadgeRule","RestoreAny:GamificationBadgeRule","Replicate:GamificationBadgeRule","Reorder:GamificationBadgeRule","ViewAny:Banner","View:Banner","Create:Banner","Update:Banner","Delete:Banner","Restore:Banner","ForceDelete:Banner","ForceDeleteAny:Banner","RestoreAny:Banner","Replicate:Banner","Reorder:Banner","ViewAny:BillingType","View:BillingType","Create:BillingType","Update:BillingType","Delete:BillingType","Restore:BillingType","ForceDelete:BillingType","ForceDeleteAny:BillingType","RestoreAny:BillingType","Replicate:BillingType","Reorder:BillingType","ViewAny:Category","View:Category","Create:Category","Update:Category","Delete:Category","Restore:Category","ForceDelete:Category","ForceDeleteAny:Category","RestoreAny:Category","Replicate:Category","Reorder:Category","ViewAny:Coupon","View:Coupon","Create:Coupon","Update:Coupon","Delete:Coupon","Restore:Coupon","ForceDelete:Coupon","ForceDeleteAny:Coupon","RestoreAny:Coupon","Replicate:Coupon","Reorder:Coupon","ViewAny:Customer","View:Customer","Create:Customer","Update:Customer","Delete:Customer","Restore:Customer","ForceDelete:Customer","ForceDeleteAny:Customer","RestoreAny:Customer","Replicate:Customer","Reorder:Customer","ViewAny:CutType","View:CutType","Create:CutType","Update:CutType","Delete:CutType","Restore:CutType","ForceDelete:CutType","ForceDeleteAny:CutType","RestoreAny:CutType","Replicate:CutType","Reorder:CutType","ViewAny:Driver","View:Driver","Create:Driver","Update:Driver","Delete:Driver","Restore:Driver","ForceDelete:Driver","ForceDeleteAny:Driver","RestoreAny:Driver","Replicate:Driver","Reorder:Driver","ViewAny:Feedback","View:Feedback","Create:Feedback","Update:Feedback","Delete:Feedback","Restore:Feedback","ForceDelete:Feedback","ForceDeleteAny:Feedback","RestoreAny:Feedback","Replicate:Feedback","Reorder:Feedback","ViewAny:OrderBilling","View:OrderBilling","Create:OrderBilling","Update:OrderBilling","Delete:OrderBilling","Restore:OrderBilling","ForceDelete:OrderBilling","ForceDeleteAny:OrderBilling","RestoreAny:OrderBilling","Replicate:OrderBilling","Reorder:OrderBilling","ViewAny:OrderCancellation","View:OrderCancellation","Create:OrderCancellation","Update:OrderCancellation","Delete:OrderCancellation","Restore:OrderCancellation","ForceDelete:OrderCancellation","ForceDeleteAny:OrderCancellation","RestoreAny:OrderCancellation","Replicate:OrderCancellation","Reorder:OrderCancellation","ViewAny:OrderItems","View:OrderItems","Create:OrderItems","Update:OrderItems","Delete:OrderItems","Restore:OrderItems","ForceDelete:OrderItems","ForceDeleteAny:OrderItems","RestoreAny:OrderItems","Replicate:OrderItems","Reorder:OrderItems","ViewAny:OrderReview","View:OrderReview","Create:OrderReview","Update:OrderReview","Delete:OrderReview","Restore:OrderReview","ForceDelete:OrderReview","ForceDeleteAny:OrderReview","RestoreAny:OrderReview","Replicate:OrderReview","Reorder:OrderReview","ViewAny:OrderStatusTracking","View:OrderStatusTracking","Create:OrderStatusTracking","Update:OrderStatusTracking","Delete:OrderStatusTracking","Restore:OrderStatusTracking","ForceDelete:OrderStatusTracking","ForceDeleteAny:OrderStatusTracking","RestoreAny:OrderStatusTracking","Replicate:OrderStatusTracking","Reorder:OrderStatusTracking","ViewAny:OrderStatuses","View:OrderStatuses","Create:OrderStatuses","Update:OrderStatuses","Delete:OrderStatuses","Restore:OrderStatuses","ForceDelete:OrderStatuses","ForceDeleteAny:OrderStatuses","RestoreAny:OrderStatuses","Replicate:OrderStatuses","Reorder:OrderStatuses","ViewAny:Orders","View:Orders","Create:Orders","Update:Orders","Delete:Orders","Restore:Orders","ForceDelete:Orders","ForceDeleteAny:Orders","RestoreAny:Orders","Replicate:Orders","Reorder:Orders","ViewAny:GamificationPointRule","View:GamificationPointRule","Create:GamificationPointRule","Update:GamificationPointRule","Delete:GamificationPointRule","Restore:GamificationPointRule","ForceDelete:GamificationPointRule","ForceDeleteAny:GamificationPointRule","RestoreAny:GamificationPointRule","Replicate:GamificationPointRule","Reorder:GamificationPointRule","ViewAny:ProductCut","View:ProductCut","Create:ProductCut","Update:ProductCut","Delete:ProductCut","Restore:ProductCut","ForceDelete:ProductCut","ForceDeleteAny:ProductCut","RestoreAny:ProductCut","Replicate:ProductCut","Reorder:ProductCut","ViewAny:ProductGrade","View:ProductGrade","Create:ProductGrade","Update:ProductGrade","Delete:ProductGrade","Restore:ProductGrade","ForceDelete:ProductGrade","ForceDeleteAny:ProductGrade","RestoreAny:ProductGrade","Replicate:ProductGrade","Reorder:ProductGrade","ViewAny:Product","View:Product","Create:Product","Update:Product","Delete:Product","Restore:Product","ForceDelete:Product","ForceDeleteAny:Product","RestoreAny:Product","Replicate:Product","Reorder:Product","ViewAny:Store","View:Store","Create:Store","Update:Store","Delete:Store","Restore:Store","ForceDelete:Store","ForceDeleteAny:Store","RestoreAny:Store","Replicate:Store","Reorder:Store","ViewAny:User","View:User","Create:User","Update:User","Delete:User","Restore:User","ForceDelete:User","ForceDeleteAny:User","RestoreAny:User","Replicate:User","Reorder:User","ViewAny:WhyUs","View:WhyUs","Create:WhyUs","Update:WhyUs","Delete:WhyUs","Restore:WhyUs","ForceDelete:WhyUs","ForceDeleteAny:WhyUs","RestoreAny:WhyUs","Replicate:WhyUs","Reorder:WhyUs","ViewAny:Role","View:Role","Create:Role","Update:Role","Delete:Role","Restore:Role","ForceDelete:Role","ForceDeleteAny:Role","RestoreAny:Role","Replicate:Role","Reorder:Role","View:OrderTrackingPage","View:OrdersStatsOverviewWidget","View:TotalOrdersWidget","View:CustomerStatsOverviewWidget","View:TopCustomersWidget","View:NewCustomersWidget","View:CustomerGrowthChartWidget","View:TopStoreVendorsWidget","View:DriverAnalyticsWidget","View:DriverStatsOverviewWidget","View:TopSellingProductsWidget"]}]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
