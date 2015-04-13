<?php

namespace App\Service;

use App\Exception\BadConfigureException;
use Symfony\Component\Finder\Finder;

/**
 * Class Configuration.
 */
class Configuration
{
    const DECODED = 1;
    const ENCODED = 2;

    const STATION = 'station';
    const LINE = 'line';

    /**
     * @param string $type
     *
     * @return array
     */
    public function get($type)
    {
        $configurations = [];

        $finder = new Finder();
        $finder->files()->in(__PATH_CONFIGURATION__.'/'.$type)->sort(function (\SplFileInfo $a, \SplFileInfo $b) {
            return strcmp(base64_decode($a->getFilename()), base64_decode($b->getFilename()));
        });

        foreach ($finder as $key => $configuration) {
            $configurations[] = [
                'name' => base64_decode($configuration->getFileName()),
                'encoded' => $configuration->getFileName(),
            ];
        }

        return $configurations;
    }

    /**
     * @param string $type
     * @param string $name
     *
     * @return array mixed
     *
     * @throws BadConfigureException
     */
    public function load($type, $name)
    {
        $pathFile = __PATH_CONFIGURATION__.'/'.$type.'/'.base64_encode($name);
        if (!is_file($pathFile)) {
            throw new BadConfigureException(sprintf('Configuration %s doesn\'t exists', $name));
        }

        return json_decode(file_get_contents($pathFile), true);
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $data
     */
    public function save($type, $name, $data)
    {
        $pathFile = __PATH_CONFIGURATION__.'/'.$type.'/'.base64_encode($name);
        file_put_contents($pathFile, json_encode($data));
    }

    /**
     * @param string $config
     * @param array  $configurations
     * @param int    $typeSearch
     *
     * @return bool
     */
    public function isConfiguration($config, array $configurations, $typeSearch = self::DECODED)
    {
        $key = 'name';
        if (self::ENCODED === $typeSearch) {
            $key = 'encoded';
        }

        foreach ($configurations as $configuration) {
            if ($config === $configuration[$key]) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $type
     * @param array  $configuration
     *
     * @throws BadConfigureException
     */
    public function delete($type, array $configuration)
    {
        $path = __PATH_CONFIGURATION__.'/'.$type.'/'.$configuration['encoded'];
        if (!is_file($path)) {
            throw new BadConfigureException('Configuration file not found');
        }

        unlink($path);
    }
}
