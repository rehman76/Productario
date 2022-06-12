<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 10/02/2021
 * Time: 6:40 PM
 */

namespace App\Repositories;
use App\Media;

class MediaRepository
{
    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    public function all()
    {
       return $this->media->get();
    }

    public function first($condition)
    {
        return $this->media->where($condition)->first();
    }

    public function store($data)
    {
        return $this->media->create($data);
    }

    public function update($column, $data)
    {
        $this->media->where($column['name'], $column['value'])->update($data);
    }

    public function deleteAll($columnName)
    {
        return $this->media->where($columnName)->delete();
    }
}
