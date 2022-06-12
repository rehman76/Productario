<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Actions\Actionable;


/**
 * Vendor model.
 */
class Vendor extends Model {

    use Actionable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vendors';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','phone', 'email', 'address', 'user_id', 'status', 'dollar_rate','mark_up', 'other_taxes', 'image', 'currency',
        'import_frequency', 'account_status' , 'last_imported_at','updated_at', 'created_at'
    ];

    // Aca no falta poner un BelongsTo User? O sea relacionarlo con algun user para ese vendor


    protected $casts = [
        'last_imported_at' => 'datetime'
    ];
    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Eloquent relations to other models.
    |
    */


    /**
     * Relationship to VendorProduct model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendorproducts() {
        return $this->hasMany('App\VendorProduct');
    }

    public function saleItems()
    {
        return $this->hasMany('App\SaleItem', 'vendor_id');
    }
}
