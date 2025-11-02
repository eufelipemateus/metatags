<?php

namespace FelipeMateus\Metatags;

/* A Laravel package to fetch webpage metadata ( Open Graph | Twitter | Facebook | Article ) */

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client;

/**
 * Metatag class is to get Metadata from webpage URL
 *
 * @category PHP_Class
 * @package  Metatags
 * @author   Nishit Maheta <nishit.maheta@mobiosolutions.com>
 */
class Metatags
{

    /**
     * Get Meta data from URL
     *
     * @param string  $url
     * @param boolean $onlyOGMetatags get only og metatags
     *
     * @return array
     */
   static function get( $url, $onlyOGMetatags = false )
    {

        $html = self::getMetaContents($url);
        if (!is_string($html)) {
            $html = '';
        }
        $doc = new DOMDocument();
        @$doc->loadHTML($html);

        $xpath = new DOMXPath($doc);

        if(!$onlyOGMetatags){
            $metaQuery = '//*/meta';
        }else{
            $metaQuery = '//*/meta[starts-with(@property, \'og:\')]';
        }

        $mMetas = $xpath->query($metaQuery);
        $mmetas = array();

        foreach ($mMetas as $meta) {
            if ($meta instanceof \DOMElement) {
                $key = $meta->getAttribute('name');
                $value = $meta->getAttribute('value');

                if (empty($key)) {
                    $key = $meta->getAttribute('property');
                }

                if (empty($key)) {
                    $key = $meta->getAttribute('itemprop');
                }

                if (!empty($key)) {
                    if (empty($value)) {
                        $value = $meta->getAttribute('content');
                    }
                    $mmetas[$key] = $value;
                }
            }
        }

        return $mmetas;
    }

    /**
     *  Get contenet from url using CURL
     *
     * @param string $url
     *
     * @return object
     */
    protected static function getMetaContents($url)
    {
        $client = new Client([
            'timeout' => 30,
            'verify' => true,
            'allow_redirects' => true,
        ]);
        $appUrl = getenv('APP_URL') ?: 'https://laravel.com';
        $userAgent = "(compatible; Laravel Metatags/1.0; +{$appUrl})";
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'User-Agent' => $userAgent,
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ],
            ]);
            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            throw new \Exception('Erro ao buscar conteúdo da URL: ' . $e->getMessage());
        }
    }
}
