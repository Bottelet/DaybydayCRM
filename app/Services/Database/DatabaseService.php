<?php
namespace App\Services\Database;

use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class DatabaseService
{

    public function truncateAllExcept()
    {
        $excludedTables = explode(',', env('EXCLUDED_TABLES', ''));

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . env('DB_DATABASE');

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;

            if (!in_array($tableName, $excludedTables)) {
                DB::table($tableName)->truncate();
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function readCsv(string $filePath): array
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);
        
        return [
            'headers' => $csv->getHeader(),
            'records' => iterator_to_array($csv->getRecords())
        ];
    } 
    
    public function importToTable(string $table, array $csvData): array
    {
        $columns = Schema::getColumnListing($table);
        $filteredData = [];
        $inserted = 0;

        foreach ($csvData['records'] as $record) {
            $filtered = array_filter($record, function($key) use ($columns) {
                return in_array($key, $columns);
            }, ARRAY_FILTER_USE_KEY);

            if (!empty($filtered)) {
                $filteredData[] = $filtered;
            }
        }

        if (!empty($filteredData)) {
            foreach (array_chunk($filteredData, 1000) as $chunk) {
                DB::table($table)->insert($chunk);
                $inserted += count($chunk);
            }
        }

        return [
            'total' => count($csvData['records']),
            'inserted' => $inserted,
            'skipped' => count($csvData['records']) - $inserted
        ];
    }

    function import_industry($filename) 
    {
        Log::info('Importing industry data from file: ' . $filename);

        if (($handle = fopen($filename, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ';');

            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                $row = array_combine($header, $data);

                Industry::create([
                    'external_id' => $row['external_id'],
                    'name' => $row['name'],
                ]);
            }

            fclose($handle);
        }
    }
}