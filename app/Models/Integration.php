<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Dinero;

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
            //'user_id' => $userId,
            'api_type' => $type
        ])->get();

        if ($integration) {
            $apiConfig = $integration[0];

            $className = $apiConfig->name;

            call_user_func_array(['App\\' . $className, 'initialize'], [$apiConfig]);
            $apiInstance = call_user_func_array(['App\\Models\\' . $className, 'getInstance'], []);

            return $apiInstance;
        }
        throw new \Exception('The user has no integrated APIs');
    }
}
