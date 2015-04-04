<?php

namespace App\Service;

use App\Exception\ClientException;
use GuzzleHttp\Client as ClientGuzzle;
use GuzzleHttp\Exception\RequestException;

/**
 * Class ClientInsee.
 */
class ClientInsee extends ClientGuzzle
{
    /**
     * @param string $city
     *
     * @return array
     *
     * @throws ClientException
     */
    public function getCodeInsee($city)
    {
        try {
            $response = $this->get('/api/records/1.0/search', [
                'query' => [
                    'dataset' => 'population-francaise-des-communes-2011',
                    'q' => $city,
                ],
            ])->json();
        } catch (RequestException $exception) {
            throw new ClientException('Error during the call to the Insee api');
        }

        $cities = [];
        foreach ($response['records'] as $record) {
            $cities[] = [
                'name' => $record['fields']['nom_de_la_commune'],
                'region' => $record['fields']['nom_de_la_region'],
                'department' => $record['fields']['code_departement'],
                'insee' => $record['fields']['code_insee'],
            ];
        }

        if (!count($cities)) {
            throw new ClientException(sprintf('No city found for data %s', $city));
        }

        return $cities;
    }
}
