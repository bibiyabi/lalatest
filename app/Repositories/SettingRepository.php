<?php

namespace App\Repositories;

use App\Models\Setting;
use Illuminate\Database\Query\Builder;

class SettingRepository
{
    /**
     * @var $setting Builder
     */
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

    public function insertSetting($userId, $data)
    {
        return $this->setting
                    ->create([
                        'user_id'       => $userId,
                        'gateway_id'    => $data['gateway_id'],
                        'user_pk'       => $data['id'],
                        'settings'      => json_encode($data),
                    ]);
    }

    public function updateSetting($id, $data)
    {
        return $this->setting
                    ->where('id', $id)
                    ->update([
                        'gateway_id'    => $data['gateway_id'],
                        'settings'      => json_encode($data),
                    ]);
    }

    public function deleteSetting($id)
    {
        return $this->setting
                    ->where('id', $id)
                    ->delete();
    }

}
