<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Chelout\RelationshipEvents\Concerns\HasBelongsToManyEvents;
use Cartalyst\Tags\TaggableTrait;
use Cartalyst\Tags\TaggableInterface;

class Publication extends Model implements HasMedia,TaggableInterface
{
    use InteractsWithMedia, HasBelongsToManyEvents, TaggableTrait;

    protected $table = "publications";
    protected $fillable = [
        'markup',
        'sku',
        'name',
        'description',
        'woo_id',
        'tiendanube_id',
        'tiendanube_status',
        'tiendanube_product_url',
        'discount',
        'mla',
        'mla_status',
        'attributes', 'quantity',
        'notes',
        'sale_price',
        'price', 'minimum_price', 'iva', 'other_taxes',
        'status', 'active', 'is_bundle', 'min_quantity', 'ean',
        'winner_vendor_product_id', 'woo_product_url', 'created_by',
        'price_variation','quantity_variation'
    ];

    protected $casts = [
        'attributes' => 'array',
    ];

    public function publicationLogs()
    {
        return $this->hasMany('App\PublicationLog');
    }

    public function vendorproductwinner()
    {
        return $this->belongsTo('App\VendorProduct', 'winner_vendor_product_id');
    }

    public function vendorproducts() // este lo hice yo, revisar
    {
        return $this->belongsToMany('App\VendorProduct');
    }

    public function vendorProductsConnectedPublication()
    {
        return $this->vendorproducts()->count();
    }

    public function enabledVendorProducts()
    {
        return $this->belongsToMany('App\VendorProduct')->where('status', true)->where('active',true)->whereHas('vendor', function ($q) {
            $q->where('status', true);
        });
    }

    public function sales()
    {
        return $this->belongsToMany('App\Sale')->withPivot('price',
            'sale_price', 'iva', 'updated_at', 'created_at', 'other_taxes');
    }

    public function category()
    {
        return $this->belongsTo('App\Category');
    }

    public function categories() // este lo hice yo, revisar
    {
        return $this->belongsToMany('App\Category', 'publication_category');
    }

    public function syncLogs()
    {
        return $this->hasMany('App\SyncLog', 'publication_id');
    }

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
            $this->addMediaCollection('product_images');
            $this->addMediaCollection('avatar')->singleFile();

    }

    public function BundlePublications()
    {
        return $this->hasMany('App\BundlePublication', 'bundle_id', 'id');
    }

    /**
     * Get the premium product associated with the publication.
     */
    public function premiumProduct()
    {
        return $this->hasOne('App\PublicationPremiumProduct');
    }

    public function errorLogs()
    {
        return $this->morphMany('App\ErrorLog', 'errorable');
    }

    protected static function boot()
    {
        parent::boot();

        static::belongsToManyAttached(function ($relation, $parent, $ids) {
            if ($relation=='vendorproducts')
            {
                PublicationVendorProductConnectionLog::create([
                    'vendor_product_id' => $ids[0],
                    'publication_id' => $parent->id,
                    'user_id' => auth()->user()->id,
                    'action' => 'Attached',
                ]);
            }
        });

        static::belongsToManyDetached(function ($relation, $parent, $ids) {
            if ($relation=='vendorproducts')
            {
                PublicationVendorProductConnectionLog::create([
                    'vendor_product_id' => $ids[0],
                    'publication_id' => $parent->id,
                    'user_id' => auth()->user()->id,
                    'action' => 'Detached',
                ]);
            }
        });
    }

    public function user()
    {
        $user= $this->belongsTo(User::class,'created_by')->first();

        if(isset($user))
        {
            return $user['first_name'].' '.$user['last_name'];
        }else{
            return null;
        }
    }

    public function saleItems()
    {
        return $this->hasMany('App\SaleItem', 'publication_id');
    }


}
