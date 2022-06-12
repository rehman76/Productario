<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 10/02/2021
 * Time: 6:40 PM
 */

namespace App\Repositories;

use App\Publication;

class PublicationRepository
{
    public function __construct(Publication $product)
    {
        $this->product = $product;
    }

    public function all()
    {
       return $this->product->with(['vendorproductwinner', 'categories'])->get();
    }

    public function allPaginated($filters)
    {
        $query =  $this->product->with(['vendorproductwinner', 'categories','media']);

        $query = isset($filters['productIds']) && $filters['productIds'] ?
                            $query->whereIn('id',  $filters['productIds']) : $query;

        if (isset($filters['status']))
        {
            $query = $filters['status']=='active' ? $query->where('status', 1) : $query->where('status', 0);
        }

        if (isset($filters['stock']))
        {
            $query = $filters['stock']=='active' ? $query->where('quantity', '>',  0) :  $query->where('quantity',  0);
        }

        return $query->paginate();

    }

    public static function getByEAN($ean)
    {
        return Publication::where('ean', $ean)->first();
    }

    public function first($columnName, $value)
    {
        return $this->product->where($columnName, $value)->first();
    }

    public function store($data)
    {
        return $this->product->create($data);
    }

    public function update($column, $data)
    {
        $this->product->where($column['name'], $column['value'])->update($data);
    }

}
