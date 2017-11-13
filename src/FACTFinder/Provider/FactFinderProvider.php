<?php

namespace FACTFinder\Provider;

class FactFinderProvider implements \Pimple\ServiceProviderInterface
{
    public function register(\Pimple\Container $pimple)
    {
        $pimple['factfinder.configuration'] = function (\Pimple\Container $container) {
            return new \FACTFinder\Core\XmlConfiguration(
                $container['factfinder.parameters']['xml'],
                $container['factfinder.parameters']['env']
            );
        };

        $pimple['factfinder.encodingconverter'] = function (\Pimple\Container $container) {
            if (extension_loaded('iconv')) {
                $type = new \FACTFinder\Core\IConvEncodingConverter($container['factfinder.configuration']);
            } elseif (function_exists('utf8_encode')
                && function_exists('utf8_decode')) {
                $type = new \FACTFinder\Core\Utf8EncodingConverter($container['factfinder.configuration']);
            } else {
                throw new \Exception('No encoding conversion available.');
            }

            if ($container->offsetExists('factfinder.logger')) {
                $type->setLogger($container['factfinder.logger']);
            }

            return $type;
        };

        $pimple['factfinder.requestparser'] = function (\Pimple\Container $container) {
            $requestParser = new \FACTFinder\Core\Client\RequestParser(
                $container['factfinder.configuration'],
                $container['factfinder.encodingconverter']
            );

            if ($container->offsetExists('factfinder.logger')) {
                $requestParser->setLogger($container['factfinder.logger']);
            }

            return $requestParser;
        };

        $pimple['factfinder.requestfactory'] = function (\Pimple\Container $container) {
            return new \FACTFinder\Core\Server\MultiCurlRequestFactory(
                $container['factfinder.configuration'],
                $container['factfinder.requestparser']->getRequestParameters()
            );
        };

        $pimple['factfinder.request'] = function (\Pimple\Container $container) {
            return $container['factfinder.requestfactory']->getRequest();
        };

        $pimple['factfinder.clienturlbuilder'] = function (\Pimple\Container $container) {
            $urlBuilder = new \FACTFinder\Core\Client\UrlBuilder(
                $container['factfinder.configuration'],
                $container['factfinder.requestparser']
            );

            if ($container->offsetExists('factfinder.logger')) {
                $urlBuilder->setLogger($container['factfinder.logger']);
            }

            return $urlBuilder;
        };

        $pimple['factfinder.adapter.search'] = $pimple->factory(
            function (\Pimple\Container $container) {
                $search = new \FACTFinder\Adapter\Search(
                    $container['factfinder.configuration'],
                    $container['factfinder.request'],
                    $container['factfinder.clienturlbuilder'],
                    $container['factfinder.encodingconverter']
                );

                if ($container->offsetExists('factfinder.logger')) {
                    $search->setLogger($container['factfinder.logger']);
                }

                return $search;
            }
        );

        $pimple['factfinder.adapter.suggest'] = $pimple->factory(
            function (\Pimple\Container $container) {
                $suggest = new \FACTFinder\Adapter\Suggest(
                    $container['factfinder.configuration'],
                    $container['factfinder.request'],
                    $container['factfinder.clienturlbuilder'],
                    $container['factfinder.encodingconverter']
                );

                if ($container->offsetExists('factfinder.logger')) {
                    $suggest->setLogger($container['factfinder.logger']);
                }

                return $suggest;
            }
        );
    }

}
