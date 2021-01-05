<?php

namespace App\Repositories;

use App\Models\key;

class KeysRepository
{
    protected $key;

    public function __construct(key $key)
    {
        $this->key = $key;
    }

    public function get()
    {
        return $this->key
            ->get();
    }

    public function getByUserPk($id)
    {
        return $this->key->where('user_pk', '=', $id)->get();
    }

    public function getKeysByUserPk($id)
    {
        $keys =  $this->key->where('user_pk', '=', $id)->value('keys');
        return collect(json_decode($keys, true));
    }
}
