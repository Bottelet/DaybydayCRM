<?php
namespace App\Repositories\Client;

use App\Client;
use App\Industry;

class ClientRepository implements ClientRepositoryContract
{

    public function find($id)
    {
        return Client::findOrFail($id);
    }
    public function listAllClients()
    {
        return Client::lists('name', 'id');
    }

    public function getAllClientsCount()
    {
        return Client::all()->count();
    }

    public function listAllIndustries()
    {
        return Industry::lists('name', 'id');
    }

    public function create($requestData)
    {
        Client::create($requestData);
    }

    public function update($id, $requestData)
    {
        $client = Client::findOrFail($id);
        $client->fill($requestData->all())->save();
    }

    public function destroy($id)
    {
        $client = Client::findorFail($id);
        $client->delete();
    }
    public function vat($requestData)
    {
        $vat = $requestData->input('vat');

        $country = $requestData->input('country');
        $company_name = $requestData->input('company_name');

        // Strip all other characters than numbers
        $vat = preg_replace('/[^0-9]/', '', $vat);
        
        function cvrApi($vat)
        {
       
            if (empty($vat)) {
            // Print error message
                return('Please insert VAT');
            } else {
                // Start cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, 'http://cvrapi.dk/api?search=' . $vat . '&country=dk');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Flashpoint');

                // Parse result
                $result = curl_exec($ch);

                // Close connection when done
                curl_close($ch);

                // Return our decoded result
                return json_decode($result, 1);
            }
        }
        $result = cvrApi($vat, 'dk');

        return $result;
    }
}
