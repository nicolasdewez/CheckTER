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

    /**
     * @param string $start
     * @param string $end
     * @param bool   $stops
     *
     * @return array
     *
     * @throws ClientException
     */
    public function getLines($start, $end, $stops = true)
    {
        $filterStations = sprintf('%s;%s|and', $start, $end);

        try {
            $response = $this->get('/', [
                'query' => [
                    'action' => 'LineList',
                    'StopareaExternalCode' => $filterStations,
                ],
            ])->xml();
        } catch (RequestException $exception) {
            throw new ClientException('Error during the call to the Sncf api');
        }

        $lines = [];
        $crawler = new Crawler($response->asXML());
        $crawler->filterXPath('//Line')->each(function (Crawler $node) use (&$lines, $stops) {
            $attributes = $node->extract(['LineName', 'LineExternalCode']);
            $lines[] = [
                'name' => $attributes[0][0],
                'code' => $attributes[0][1],
                'stops' => $stops ? $this->getStops($attributes[0][1]) : [],
            ];
        });

        if (!count($lines)) {
            throw new ClientException(sprintf('No line found between %s and %s', $start, $end));
        }

        return $lines;
    }

    /**
     * @param string $codeLine
     *
     * @return array
     *
     * @throws ClientException
     */
    public function getStops($codeLine)
    {
        try {
            $response = $this->get('/', [
                'query' => [
                    'action' => 'StopAreaList',
                    'LineExternalCode' => $codeLine,
                ],
            ])->xml();
        } catch (RequestException $exception) {
            throw new ClientException('Error during the call to the Sncf api');
        }

        $stops = [];
        $crawler = new Crawler($response->asXML());
        $crawler->filterXPath('//StopArea')->each(function (Crawler $node) use (&$stops) {
            $attributes = $node->extract(['StopAreaName', 'StopAreaExternalCode']);
            $stops[] = [
                'name' => $attributes[0][0],
                'code' => $attributes[0][1],
            ];
        });

        return $stops;
    }
}
