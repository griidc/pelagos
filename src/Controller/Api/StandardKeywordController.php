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
        $lookfor = $request->query->get('lookfor');

        if (empty($lookfor)) {
            $uri = 'https://vocabs.ardc.edu.au/apps/vocab_widget/proxy/?api_key=public&action=top&repository=anzsrc-2020-for';
        } else {
            $uri = 'https://vocabs.ardc.edu.au/apps/vocab_widget/proxy/?api_key=public&action=narrow&repository=anzsrc-2020-for&lookfor=' . $lookfor;
        }

        $guzzleClient = new Client();

        $json = [];

        try {
            $response = $guzzleClient->request(
                'GET',
                $uri
            );

            if (preg_match('/^function\((.*)\);$/s', $response->getBody()->getContents(), $matches)) {
                $json = json_decode($matches[1]);
            }
        } catch (GuzzleException $e) {
            throw new HttpException(500, 'Could not get keywords!');
        }

        return new JsonResponse($json);
    }
}
