<?php

namespace App;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class GoogleBooks
{
    private readonly string $endpoint;
    private readonly string $apiKey;

    public function __construct(
        private readonly HttpClientInterface $client,
        #[Autowire('%env(string:GOOGLE_BOOKS_API_KEY)%')]
        string $apiKey
    ) {
        $this->endpoint = 'https://www.googleapis.com/books/v1/volumes';
        $this->apiKey = $apiKey;
    }

    public function searchBooks(string $query, int $maxResults = 10): array
    {
        $response = $this->client->request('GET', $this->endpoint, [
            'query' => [
                'q' => $query,
                'maxResults' => $maxResults,
                'key' => $this->apiKey,
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Erreur lors de la requête à l\'API Google Books.');
        }

        return $response->toArray();
    }
}
