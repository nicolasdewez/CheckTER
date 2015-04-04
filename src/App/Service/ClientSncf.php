<?php

namespace App\Service;

use App\Exception\ClientException;
use GuzzleHttp\Client as ClientGuzzle;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class ClientSncf.
 */
class ClientSncf extends ClientGuzzle
{
    /**
     * @param string $codeInsee
     * @param string $city
     *
     * @return array
     *
     * @throws ClientException
     */
    public function getStations($codeInsee, $city)
    {
        try {
            $response = $this->get('/', [
                'query' => [
                    'action' => 'StopAreaList',
                    'CityExternalCode' => $codeInsee,
                ],
            ])->xml();
        } catch (RequestException $exception) {
            throw new ClientException('Error during the call to the Sncf api');
        }

        $stations = [];
        $crawler = new Crawler($response->asXML());
        $crawler->filterXPath('//StopArea')->each(function (Crawler $node) use (&$stations) {
            $attributes = $node->extract(['StopAreaName', 'StopAreaExternalCode']);
            $stations[] = [
                'name' => $attributes[0][0],
                'code' => $attributes[0][1],
            ];
        });

        if (!count($stations)) {
            throw new ClientException(sprintf('No station found in %s', $city));
        }

        return $stations;
    }
}
