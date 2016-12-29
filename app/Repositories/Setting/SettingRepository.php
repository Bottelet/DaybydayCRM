<?php
namespace App\Repositories\Setting;

use App\Models\Setting;

/**
 * Class SettingRepository
 * @package App\Repositories\Setting
 */
class SettingRepository implements SettingRepositoryContract
{
    /**
     * @return mixed
     */
    public function getCompanyName()
    {
        return Setting::findOrFail(1)->company;
    }

    /**
     * @param $requestData
     */
    public function updateOverall($requestData)
    {
        $setting = Setting::findOrFail(1);

        $setting->fill($requestData->all())->save();
    }

    /**
     * @return mixed
     */
    public function getSetting()
    {
        return Setting::findOrFail(1);
    }
}
