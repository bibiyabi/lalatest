<?php

namespace App\Repositories;

use App\Models\Key;

class KeyRepository
{
    private $key;

    public function __construct()
    {
        $this->key = Key::query();
    }

    public function first()
    {
        return $this->key->first();
    }

    public function get()
    {
        return $this->key->get();
    }

    public function filterId($id)
    {
        return $this->key->where('id', '=', $id);
    }

    public function filterByUserPk($id)
    {
        $this->key->where('user_pk', '=', $id);
        return $this;
    }

    public function filterByUserId($id)
    {
        $this->key->where('user_id', '=', $id);
        return $this;
    }

    public function filterCombinePk($userId, $userPk) {
        $this->filterByUserPk($userPk);
        $this->filterByUserId($userId);
        return $this;
    }
}
