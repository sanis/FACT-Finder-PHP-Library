<?php

namespace FACTFinder\Core\Server;

/**
 * This implementation retrieves the FACT-Finder data from the file system. File
 * names are generated from request parameters. For the naming convention see
 * the getFileName() method.
 * Responses are queried sequentially and lazily and are cached as long as
 * parameters don't change.
 */
class FileSystemDataProvider extends AbstractDataProvider
{
    /**
     * @var string
     */
    protected $fileLocation;

    public function __construct(\FACTFinder\Core\ConfigurationInterface $configuration)
    {
        parent::__construct($configuration);
    }

    public function setConnectTimeout($id, $timeout)
    {
    }

    public function setTimeout($id, $timeout)
    {
    }

    public function setFileLocation($path)
    {
        $this->fileLocation = ($path[strlen($path) - 1] == DS) ? $path : $path . DS;
    }

    public function loadResponse($id)
    {
        if (!isset($this->connectionData[$id])) {
            throw new \InvalidArgumentException('Tried to get response for invalid ID $id.');
        }

        $connectionData = $this->connectionData[$id];

        $action = $connectionData->getAction();
        if (empty($action)) {
            $this->logger->error('Request type missing.');
            $connectionData->setNullResponse();
            return;
        }
        $fileNamePrefix = $this->getFileNamePrefix($connectionData);

        $fileExtension = $this->getFileExtension($connectionData);
        $queryString = $this->getQueryString($connectionData);
        $fileName = $this->getFileName($fileNamePrefix, md5($queryString), $fileExtension);

        if (!$this->hasFileNameChanged($id, $fileName)) {
            return;
        }

        $this->logger && $this->logger->info("Trying to load file: $fileName");

        $fileContent = null;
        if (!$fileContent = @file_get_contents($fileName)) {
            throw new \Exception(
                'File "' . $fileName . ' (original: ' . $fileNamePrefix . $queryString . $fileExtension . '" not found'
            );
        }

        $response = new Response($fileContent, 200, 0, '');

        $connectionData->setResponse($response, $fileName);
    }

    private function getFileNamePrefix($connectionData)
    {
        $action = $connectionData->getAction();

        // Replace the .ff file extension with a dot.
        return preg_replace('/[.]ff$/i', '.', $action);
    }

    private function getFileExtension($connectionData)
    {
        $parameters = $connectionData->getParameters();

        $fileExtension = '.raw';
        if (isset($parameters['format'])) {
            $fileExtension = '.' . $parameters['format'];
        }

        return $fileExtension;
    }

    private function getQueryString($connectionData)
    {
        $parameters = clone $connectionData->getParameters();

        unset($parameters['format']);
        unset($parameters['user']);
        unset($parameters['pw']);
        unset($parameters['timestamp']);
        unset($parameters['channel']);

        $rawParameters = &$parameters->getArray();
        // We received that array by reference, so we can sort it to sort the
        // Parameters object internally, too.
        ksort($rawParameters, SORT_STRING);

        return $parameters->toJavaQueryString();
    }

    private function getFileName($prefix, $queryString, $extension)
    {
        return $this->fileLocation . $prefix . $queryString . $extension;
    }

    private function hasFileNameChanged($id, $newFileName)
    {
        $connectionData = $this->connectionData[$id];

        if ($connectionData->getResponse() instanceof NullResponse) {
            return true;
        }

        return $newFileName != $connectionData->getPreviousUrl();
    }
}
