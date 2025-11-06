<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'color',
        'icon',
        'visible_sections',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'visible_sections' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function privileges()
    {
        return $this->hasOne(RolePrivilege::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'company_role_id');
    }

    /**
     * Get default privileges for a new role
     */
    public static function getDefaultPrivileges()
    {
        return [
            'can_access_pos' => true,
            'can_logout_from_pos' => true,
            'can_view_sales' => true,
            'can_view_older_sales' => false,
            'can_create_sales' => true,
            'can_process_refunds' => false,
            'can_apply_discounts' => false,
            'can_void_transactions' => false,
            'can_modify_prices' => false,
            'can_press_end_of_day' => false,
            'can_view_daily_reports' => false,
            'can_view_cash_drawer' => true,
            'can_open_cash_drawer' => false,
            'can_view_products' => true,
            'can_add_products' => false,
            'can_edit_products' => false,
            'can_delete_products' => false,
            'can_manage_inventory' => false,
            'can_view_users' => false,
            'can_add_users' => false,
            'can_edit_users' => false,
            'can_delete_users' => false,
            'can_view_reports' => false,
            'can_export_reports' => false,
            'can_view_analytics' => false,
            'can_access_settings' => false,
            'can_backup_data' => false,
            'can_manage_branches' => false,
            'can_manage_categories' => false,
            'can_manage_pos_layouts' => false,
            'can_manage_screen_setup' => false,
        ];
    }
}
