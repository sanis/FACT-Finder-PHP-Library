<?php

namespace FACTFinder\Core;

use Psr\Log\LoggerAwareTrait;

/**
 * Implements the AbstractEncodingConverter using utf8_encode() and
 * utf_decode().
 */
class Utf8EncodingConverter extends AbstractEncodingConverter
{
    use LoggerAwareTrait;

    public function __construct(ConfigurationInterface $configuration)
    {
        parent::__construct($configuration);
    }

    protected function convertString($inCharset, $outCharset, $string)
    {
        if (!empty($inCharset) && !empty($outCharset) && strtolower($inCharset) != strtolower($outCharset)) {
            if (strtolower($inCharset) == 'utf-8') {
                if (strtolower($outCharset) != 'iso-8859-1') {
                    $this->logger && $this->logger->warning(
                        "utf8_decode() does not support $outCharset. If $outCharset is not compatible with ISO-8859-1, "
                        . 'the resulting string may contain wrong or invalid characters.'
                    );
                }
                $string = utf8_decode($string);
            } elseif (strtolower($outCharset) == 'utf-8') {
                if (strtolower($inCharset) != 'iso-8859-1') {
                    $this->logger && $this->logger->warning(
                        "utf8_encode() does not support $inCharset. If $inCharset is not compatible with ISO-8859-1, "
                        . 'the resulting string may contain wrong characters.'
                    );
                }
                $string = utf8_encode($string);
            } else {
                $this->logger && $this->logger->error('Conversion between non-UTF-8 encodings not possible.');
                throw new \InvalidArgumentException("Cannot handle conversion from $inCharset to $outCharset!");
            }
        }
        return $string;
    }
}
