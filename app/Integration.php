<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Dinero;

class Integration extends Model
{
   protected $fillable = ['name', 'client_id', 'client_secret', 'api_key', 'org_id', 'api_type'];

   /**
     * Get the api class name
     * 
     * @param  [string] $type [description]
     * @return [type]       [description]
     */
    public static function getApi($userId, $type){
        $integration = Integration::find([
            'user_id' => $userId, 
            'api_type' => $type
        ]);

        if (!$integration){
        	$integration = Integration::find([
	            'user_id' => null, 
	            'api_type' => $type
	        ]);
        }
        if ($integration){
                
            $apiConfig = $integration->First();
            
			$className = $apiConfig->name;
           
			call_user_func_array(['App\\'.$className, 'initialize'], [$apiConfig]);
            $apiInstance = call_user_func_array(['App\\'.$className, 'getInstance'], []);

            return $apiInstance;

        }
        throw new \Exception('The user has no integrated APIs');
    }
}
