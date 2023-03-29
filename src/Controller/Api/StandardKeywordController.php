<?php

namespace App\Controller\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class StandardKeywordController extends AbstractController
{
    #[Route('/api/standard/keyword', name: 'app_api_standard_keyword')]
    public function index(Request $request): Response
    {
        $uri = $request->query->get('uri');

        $json = [];

        if (empty($uri)) {
            $url = 'https://vocabs.ardc.edu.au/repository/api/lda/anzsrc-2020-for/concept/topConcepts.json';
            $json = $this->getItems($url);
        } else {
            $url = 'https://vocabs.ardc.edu.au/repository/api/lda/anzsrc-2020-for/resource.json?uri=' . $uri;
            $json = $this->getLabelAndNarrower($url);
        }

        return new JsonResponse($json);
    }

    private function getLabelAndNarrower(string $uri):mixed
    {
        $guzzleClient = new Client();

        try {
            $response = $guzzleClient->request(
                'GET',
                $uri
            );

            $data = json_decode($response->getBody()->getContents());
        } catch (GuzzleException $e) {
            throw new HttpException(400, 'doesnt work');
        }

        // if (empty($data->result->_about)) {
        //     dd($data, $uri);
        // }

        $json = [];
        $json['_about'] = $data->result->primaryTopic->_about ?? [];
        $json['label'] = $data->result->primaryTopic->prefLabel->_value ?? [];

        $narrower = $data->result->primaryTopic->narrower ?? [];

        foreach ($narrower as $narrow)
        {
            $json['narrower'][]  = $this->getLabelAndNarrower($narrow->_about);
        }

        return $json;
    }

    private function getItems(string $uri):mixed
    {
        $guzzleClient = new Client();

        try {
            $response = $guzzleClient->request(
                'GET',
                $uri
            );

            $data = json_decode($response->getBody()->getContents());
        } catch (GuzzleException $e) {
            throw new HttpException(400, 'doesnt work');
        }

        // dd($data);

        $json = [];

        foreach ($data->result->items as $item) {
            $itemArray = [];

            $itemArray['_about'] = $item->_about;
            $itemArray['label'] = $item->prefLabel->_value;

            $json[] = $itemArray;

        }
        return $json;
    }
}
