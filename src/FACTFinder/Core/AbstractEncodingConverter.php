<?php

namespace FACTFinder\Core;

use FACTFinder\Util\Parameters;

/**
 * Takes care of differences in encoding between different participants of the
 * communication. Internal to the library all strings are encoded as UTF-8, so
 * the methods of this class are only for converting to and from UTF-8. The
 * source and target encodings are determined by the configuration.
 * This abstract class does not specify how the actual conversion of a single
 * string is done. Create a subclass to implement the conversion method.
 * Also note that none of these methods handle URL en- or decoding but only deal
 * with plain character encodings.
 */
abstract class AbstractEncodingConverter
{
    const LIBRARY_ENCODING = 'UTF-8';
    /**
     * @var string
     */
    protected $pageContentEncoding;
    /**
     * @var string
     */
    protected $clientUrlEncoding;

    /**
     * @param ConfigurationInterface $configuration Configuration object to use.
     */
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->pageContentEncoding = $configuration->getPageContentEncoding();
        $this->clientUrlEncoding = $configuration->getClientUrlEncoding();
    }

    /**
     * Converts data held by the library for use on the rendered page.
     * Hence, it converts from the library's encoding (UTF-8) to the configured
     * page content encoding.
     *
     * @param mixed $data Could either be a string or an associative array.
     *
     * @return mixed
     */
    public function encodeContentForPage($data)
    {
        return $this->convert(self::LIBRARY_ENCODING, $this->pageContentEncoding, $data);
    }

    /**
     * Converts data obtained from the client URL for use within the library.
     * Hence, it converts from the configured client URL encoding to the
     * library's encoding (UTF-8).
     *
     * @param mixed $data Data obtained from the client URL. Note that this
     *                    data should already be URL decoded. Could either be a string or an
     *                    associative array.
     *
     * @return mixed
     */
    public function decodeClientUrlData($data)
    {
        return $this->convert($this->clientUrlEncoding, self::LIBRARY_ENCODING, $data);
    }

    /**
     * Converts data held by the library for use in a client URL.
     * Hence, it converts from the configured client URL encoding to the
     * library's encoding (UTF-8).
     *
     * @param mixed $data Data to be used in the client URL. Note that this
     *                    data should not yet be URL encoded. Could either be a string or an
     *                    associative array.
     *
     * @return mixed
     */
    public function encodeClientUrlData($data)
    {
        return $this->convert(self::LIBRARY_ENCODING, $this->clientUrlEncoding, $data);
    }

    abstract protected function convertString($inCharset, $outCharset, $string);

    /**
     * Converts data from $inCharset to $outCharset.
     *
     * @param       $inCharset
     * @param       $outCharset
     * @param mixed $data If a string is given, it's encoding will be converted.
     *                    If an associative array is given, keys and values will be
     *                    converted recursively. All other data types will be returned
     *                    unchanged.
     *
     * @return mixed
     */
    protected function convert($inCharset, $outCharset, $data)
    {
        if ($inCharset == $outCharset) {
            return $data;
        }
        return $this->convertRecursive($inCharset, $outCharset, $data);
    }

    /**
     * Converts data from $inCharset to $outCharset.
     *
     * @param       $inCharset
     * @param       $outCharset
     * @param mixed $data If a string is given, it's encoding will be converted.
     *                    If an associative array is given, keys and values will be
     *                    converted recursively. All other data types will be returned
     *                    unchanged.
     *
     * @return mixed
     */
    protected function convertRecursive($inCharset, $outCharset, $data)
    {
        if ($data instanceof Parameters) {
            if (count($data->getArray()) == 1 && current(array_keys($data->getArray())) == '') {
                $result = $data;
            } else {
                $result = new Parameters($this->convert($inCharset, $outCharset, $data->getArray()));
            }
        } elseif (is_array($data)) {
            $result = [];
            foreach ($data as $k => $v) {
                $k = $this->convertRecursive($inCharset, $outCharset, $k);
                $result[$k] = $this->convertRecursive($inCharset, $outCharset, $v);
            }
        } elseif (is_string($data)) {
            $result = $this->convertString($inCharset, $outCharset, $data);
        } else {
            $result = $data;
        }

        return $result;
    }
}
