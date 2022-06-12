<?php

namespace App;

use App\Scopes\SoftDeleteScope;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


/**
 * Vendor Publication model.
 */
class VendorProduct extends Model implements HasMedia
{

    use InteractsWithMedia;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vendor_products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'vendor_id',
      'sku',
      'name',
      'status',
      'description',
      'ean',
      'currency',
      'link',
      'quantity',
       'notes',
       'vendor_price',
      'min_quantity',
      'price',
      'calculated_retail_price',
      'sale_price',
      'discount',
      'iva',
      'other_taxes',
      'weight', 'price_variation',
    'quantity_variation',
        'active'
    ];


    protected $attributes = [
        'status' => 1
    ];

    protected static function booted()
    {
        static::addGlobalScope(new SoftDeleteScope);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Eloquent relations to other models.
    |
    */

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200);
    }

    /**
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('vendor_product_images');

    }
    /**
     * Relationship to Vendor model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendor() {
        return $this->belongsTo('App\Vendor', 'vendor_id');
    }

    /**
     * Relationship to Publication model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function winner() {
        return $this->belongsTo('App\Publication', 'vendor_product_winner_id');
    }


    public function publications()
    {
        return $this->belongsToMany('App\Publication', 'publication_vendor_product');
    }

    public function publication()
    {
        return $this->publications()->first();

    }

    /**** Accessors ****/
    public function getCalculatedRetailPrice()
    {
        return '$'. $this->calculated_retail_price;
    }

    public function vendorProductLogs()
    {
        return $this->hasMany('App\VendorProductLog','vendor_product_id','id');
    }

    public function customSoftDelete()
    {
        try {
            $this->deleted_at = now();
            $this->save();

            return true;
        } catch(\Throwable $th) {
            return false;
        }
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'publication_vendor_product_logs', 'vendor_product_id', 'user_id')
            ->withPivot('created_at');
    }

    public function latestUser()
    {
        $latestUser= $this->users()->latest('publication_vendor_product_logs.created_at')->first();

        if(isset($latestUser))
        {
            return $latestUser;
        }else{
            return null;
        }
    }

    public function sale()
    {
        return $this->hasOne(SaleItem::class,'vendor_product_id');
    }

    /*** Vendor Product Connections logs with Publication ***/
    public function vendorConnectionLogs()
    {
        return $this->hasMany(PublicationVendorProductConnectionLog::class,'vendor_product_id','id');
    }
}
