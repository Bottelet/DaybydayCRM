<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    protected $fillable = ['name', 'client_id', 'client_secret', 'api_key', 'org_id', 'api_type'];

    /**
     * @param $type
     * @return mixed
     * @throws \Exception
     */
    public static function getApi($type)
    {
        $integration = Integration::where([
            'api_type' => $type
        ])->first();
        if ($integration) {
            $className = ucfirst($integration->name);

            call_user_func_array(['App\\' . $className, 'initialize'], [$integration]);
            $apiInstance = call_user_func_array(['App\\' . $className, 'getInstance'], []);

            return $apiInstance;
        }
        return false;
    }

    public static function initBillingIntegration()
    {
        $integration = Integration::whereApiType('billing')->first();
        if (!$integration) {
            return null;
        }
        return $integration->api_class;
    }

    public function getApiClassAttribute()
    {
        return new $this->name;
    }
}
