<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePrivilege extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_role_id',
        'can_access_pos',
        'can_logout_from_pos',
        'can_view_sales',
        'can_view_older_sales',
        'can_create_sales',
        'can_process_refunds',
        'can_apply_discounts',
        'can_void_transactions',
        'can_modify_prices',
        'can_press_end_of_day',
        'can_view_daily_reports',
        'can_view_cash_drawer',
        'can_open_cash_drawer',
        'can_view_products',
        'can_add_products',
        'can_edit_products',
        'can_delete_products',
        'can_manage_inventory',
        'can_view_users',
        'can_add_users',
        'can_edit_users',
        'can_delete_users',
        'can_view_reports',
        'can_export_reports',
        'can_view_analytics',
        'can_access_settings',
        'can_backup_data',
        'can_manage_branches',
        'can_manage_categories',
        'can_manage_pos_layouts',
        'can_manage_screen_setup',
    ];

    protected $casts = [
        'can_access_pos' => 'boolean',
        'can_logout_from_pos' => 'boolean',
        'can_view_sales' => 'boolean',
        'can_view_older_sales' => 'boolean',
        'can_create_sales' => 'boolean',
        'can_process_refunds' => 'boolean',
        'can_apply_discounts' => 'boolean',
        'can_void_transactions' => 'boolean',
        'can_modify_prices' => 'boolean',
        'can_press_end_of_day' => 'boolean',
        'can_view_daily_reports' => 'boolean',
        'can_view_cash_drawer' => 'boolean',
        'can_open_cash_drawer' => 'boolean',
        'can_view_products' => 'boolean',
        'can_add_products' => 'boolean',
        'can_edit_products' => 'boolean',
        'can_delete_products' => 'boolean',
        'can_manage_inventory' => 'boolean',
        'can_view_users' => 'boolean',
        'can_add_users' => 'boolean',
        'can_edit_users' => 'boolean',
        'can_delete_users' => 'boolean',
        'can_view_reports' => 'boolean',
        'can_export_reports' => 'boolean',
        'can_view_analytics' => 'boolean',
        'can_access_settings' => 'boolean',
        'can_backup_data' => 'boolean',
        'can_manage_branches' => 'boolean',
        'can_manage_categories' => 'boolean',
        'can_manage_pos_layouts' => 'boolean',
        'can_manage_screen_setup' => 'boolean',
    ];

    public function companyRole()
    {
        return $this->belongsTo(CompanyRole::class);
    }

    /**
     * Check if role has a specific privilege
     */
    public function hasPrivilege($privilege)
    {
        return $this->$privilege ?? false;
    }
}
