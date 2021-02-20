<?php


namespace App\Service;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GuzzleService
{
    /**
     * @var Client $client
     */
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Will return the given page content as string
     *
     * @param string $url
     * @return string
     * @throws GuzzleException
     */
    public function getPageContent(string $url): string
    {
        $response = $this->client->get($url)->getBody();
        return $response;
    }

    /**
     * Returns status code for get call
     *
     * @param string $url
     * @return string
     * @throws GuzzleException
     */
    public function getStatusCodeForGetCallOnUrl(string $url): string
    {
        $response = $this->client->get($url)->getStatusCode();
        return $response;
    }

}