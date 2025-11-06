<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashierPrivilege extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'cashier_id',
        'managed_by',
        'can_view_older_sales',
        'can_process_refunds',
        'can_apply_discounts',
        'can_void_transactions',
        'can_modify_prices',
        'can_press_end_of_day',
        'can_view_daily_reports',
        'can_view_cash_drawer',
        'can_add_products',
        'can_edit_products',
        'can_manage_inventory',
        'can_access_settings',
        'can_backup_data'
    ];

    protected $casts = [
        'can_view_older_sales' => 'boolean',
        'can_process_refunds' => 'boolean',
        'can_apply_discounts' => 'boolean',
        'can_void_transactions' => 'boolean',
        'can_modify_prices' => 'boolean',
        'can_press_end_of_day' => 'boolean',
        'can_view_daily_reports' => 'boolean',
        'can_view_cash_drawer' => 'boolean',
        'can_add_products' => 'boolean',
        'can_edit_products' => 'boolean',
        'can_manage_inventory' => 'boolean',
        'can_access_settings' => 'boolean',
        'can_backup_data' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'managed_by');
    }

    /**
     * Get default privileges for a new cashier
     */
    public static function getDefaultPrivileges()
    {
        return [
            'can_view_older_sales' => false, // Default: cannot see older sales
            'can_process_refunds' => false,
            'can_apply_discounts' => false,
            'can_void_transactions' => false,
            'can_modify_prices' => false,
            'can_press_end_of_day' => false, // Default: cannot press end of day
            'can_view_daily_reports' => false,
            'can_view_cash_drawer' => true,
            'can_add_products' => false,
            'can_edit_products' => false,
            'can_manage_inventory' => false,
            'can_access_settings' => false,
            'can_backup_data' => false,
        ];
    }
}
