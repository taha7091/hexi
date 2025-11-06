<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'company_id',
        'branch_id',
        'role',
        'company_role_id',
        'name',
        'email',
        'password',
        'pin',
        'theme_preference',
        'notifications_enabled',
        'email_notifications',
        'timezone',
        'date_format',
        'time_format',
        'language_preference',
    ];

    protected $hidden = ['password', 'remember_token'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function companyRole()
    {
        return $this->belongsTo(CompanyRole::class);
    }

    /**
     * Get brands through company relationship
     */
    public function brands()
    {
        if ($this->isMasterAdmin()) {
            return Brand::query();
        }

        return $this->company ? $this->company->brands() : Brand::whereRaw('1 = 0');
    }

    /**
     * Check if user is master admin (ID = 1)
     */
    public function isMasterAdmin()
    {
        return $this->id === 1;
    }

    /**
     * Check if user has admin role
     */
    public function isAdmin()
    {
        return in_array($this->role, ['admin', 'master_admin']);
    }

    /**
     * Check if user can manage companies (only master admin)
     */
    public function canManageCompanies()
    {
        return $this->isMasterAdmin();
    }

    /**
     * Check if user can manage brands for their company
     */
    public function canManageBrands()
    {
        return $this->isMasterAdmin() || $this->isAdmin();
    }

    /**
     * Check if user can manage branches for their company
     */
    public function canManageBranches()
    {
        return $this->isMasterAdmin() || $this->isAdmin();
    }

    /**
     * Check if user can manage products (admin, manager)
     */
    public function canManageProducts()
    {
        return in_array($this->role, ['admin', 'manager', 'master_admin']);
    }

    /**
     * Check if user can create/edit products (admin, manager)
     */
    public function canEditProducts()
    {
        return in_array($this->role, ['admin', 'manager', 'master_admin']);
    }

    /**
     * Check if user can view products (all roles)
     */
    public function canViewProducts()
    {
        return true; // All authenticated users can view products
    }

    /**
     * Check if user can manage POS layouts (admin, manager)
     */
    public function canManagePosLayouts()
    {
        return in_array($this->role, ['admin', 'manager', 'master_admin']);
    }

    /**
     * Check if user can access POS terminal (pos_user, admin, manager)
     */
    public function canAccessPOS()
    {
        return in_array($this->role, ['pos_user', 'admin', 'manager', 'master_admin']);
    }

    /**
     * Check if user is a company manager
     */
    public function isManager()
    {
        return $this->role === 'manager';
    }

    /**
     * Check if user is a regular employee
     */
    public function isEmployee()
    {
        return in_array($this->role, ['user', 'pos_user']);
    }

    /**
     * Get user's accessible companies (master admin sees all, others see only their own)
     */
    public function getAccessibleCompanies()
    {
        if ($this->isMasterAdmin()) {
            return Company::all();
        }

        return Company::where('id', $this->company_id)->get();
    }

    /**
     * Get user's accessible brands
     */
    public function getAccessibleBrands()
    {
        if ($this->isMasterAdmin()) {
            return Brand::all();
        }

        return Brand::where('company_id', $this->company_id)->get();
    }

    /**
     * Get user's accessible branches
     */
    public function getAccessibleBranches()
    {
        if ($this->isMasterAdmin()) {
            return Branch::all();
        }

        return Branch::where('company_id', $this->company_id)->get();
    }

    /**
     * Cashier privileges (when user is a cashier)
     */
    public function cashierPrivileges()
    {
        return $this->hasOne(CashierPrivilege::class, 'cashier_id');
    }

    /**
     * Managed cashiers (when user is a manager)
     */
    public function managedCashiers()
    {
        return $this->hasMany(CashierPrivilege::class, 'managed_by');
    }

    /**
     * Check if user has a specific privilege (for cashiers)
     */
    public function hasPrivilege($privilege)
    {
        // Master admin and admin have all privileges
        if ($this->isMasterAdmin() || $this->isAdmin()) {
            return true;
        }

        // Check company role privileges first
        if ($this->companyRole && $this->companyRole->privileges) {
            return $this->companyRole->privileges->hasPrivilege($privilege);
        }

        // Fallback to cashier privileges for backward compatibility
        if ($this->role === 'cashier') {
            $privileges = $this->cashierPrivileges;
            if (!$privileges) {
                return false; // No privileges set, deny access
            }
            return $privileges->$privilege ?? false;
        }

        // Default: deny access if no privileges defined
        return false;
    }

}