<?php

namespace App\Service;

use App\Exception\BadConfigureException;
use Symfony\Component\Finder\Finder;

/**
 * Class Configuration.
 */
class Configuration
{
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
