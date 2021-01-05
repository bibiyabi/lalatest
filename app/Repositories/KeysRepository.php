<?php

namespace App\Repositories;

use App\Models\key;

class KeysRepository
{


    public function __construct()
    {


    }

    public function get()
    {
        return key::get();
    }

    public function getByUserPk($id)
    {
        return key::where('user_pk', '=', $id)->get();
    }

    public function getKeysByUserPk($id)
    {

        $keys =  key::where('user_pk', '=', $id)->value('keys');

        return collect(json_decode($keys, true));
    }
}
