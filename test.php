
<?php

// test.php: Standalone script to test Laravel DB connection to 'daybyday_test'.

// Force error reporting and output for all environments
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



echo "Script started\n";

// Ensure Composer autoloader is loaded so Laravel classes are available
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app->make(Kernel::class)->bootstrap();


// Override DB connection config for this script only
config(['database.connections.mysql.database' => 'daybyday_test']);

try {
    $pdo = DB::connection()->getPdo();
    $result = DB::select('SELECT 1 as test');
    echo "Connection to 'daybyday_test' successful. Test query result: ".json_encode($result)."\n";
} catch (Exception $e) {
    echo "Database connection failed: ".$e->getMessage()."\n";
    exit(1);
}

