<?php


namespace App\Models\CRM\Leads;


use Illuminate\Database\Eloquent\Model;

class LeadAssign extends Model
{
    /**
     * Assigned By Types for Lead Assign
     * 
     * @var array
     */
    const ASSIGNED_BY_TYPES = ['autoassign', 'hotpotato', 'dealer', 'salesperson'];

    /**
     * Statuses for Lead Assign
     * 
     * @var array
     */
    const ASSIGNED_STATUS = ['assigning', 'assigned', 'mailed', 'skipped', 'error'];

    /**
     * Specific Statuses for Lead Assign
     * 
     * @var string
     */
    const STATUS_ASSIGNING = 'assigning';
    const STATUS_ASSIGNED  = 'assigned';
    const STATUS_MAILED    = 'mailed';
    const STATUS_SKIPPED   = 'skipped';
    const STATUS_ERROR     = 'error';

    /**
     * @var string
     */
    const TABLE_NAME = 'crm_lead_assign';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'lead_id',
        'dealer_location_id',
        'salesperson_type',
        'found_salesperson_id',
        'chosen_salesperson_id',
        'assigned_by',
        'assigned_by_id',
        'status',
        'explanation'
    ];
}