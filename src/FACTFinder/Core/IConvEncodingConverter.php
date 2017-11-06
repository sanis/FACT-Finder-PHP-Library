<?php

namespace FACTFinder\Core;

use Psr\Log\LoggerAwareTrait;

/**
 * Implements the AbstractEncodingConverter using the iconv module.
 */
class IConvEncodingConverter extends AbstractEncodingConverter
{
    use LoggerAwareTrait;

    public function __construct(ConfigurationInterface $configuration)
    {
        parent::__construct($configuration);
    }

    protected function convertString($inCharset, $outCharset, $string)
    {
        if ($inCharset == $outCharset || empty($inCharset) || empty($outCharset)) {
            return $string;
        }
        // See http://www.php.net/manual/en/function.iconv.php for more
        // information on '//TRANSLIT'.
        $result = iconv($inCharset, $outCharset . '//TRANSLIT', $string);

        if ($result === false) {
            $this->logger && $this->logger->warning(
                "Conversion from $inCharset to $outCharset not possible. "
                . "The string is still encoded with $inCharset."
            );
            $result = $string;
        }

        return $result;
    }
}
