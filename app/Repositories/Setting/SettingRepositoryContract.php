<?php
namespace App\Repositories\Setting;

interface SettingRepositoryContract
{
    public function getCompanyName();

    public function updateOverall($requestData);

    public function getSetting();
}
