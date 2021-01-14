<?php

namespace App\Repositories;

use App\Models\Setting;

class SettingRepository
{
    private $setting;

    public function __construct()
    {
        $this->setting = Setting::query();
    }

    public function first()
    {
        return $this->setting->first();
    }

    public function get()
    {
        return $this->setting->get();
    }

    public function filterId($id)
    {
        return $this->setting->where('id', '=', $id);
    }

    public function filterByUserPk($id)
    {
        $this->setting->where('user_pk', '=', $id);
        return $this;
    }

    public function filterByUserId($id)
    {
        $this->setting->where('user_id', '=', $id);
        return $this;
    }

    public function filterCombinePk($userId, $userPk) {
        $this->filterByUserPk($userPk);
        $this->filterByUserId($userId);
        return $this;
    }
}
