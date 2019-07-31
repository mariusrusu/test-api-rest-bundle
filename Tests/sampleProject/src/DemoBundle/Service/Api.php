<?php

namespace EveryCheck\TestApiRestBundle\Tests\sampleProject\src\DemoBundle\Service;

use GuzzleHttp\Client;

class Api{

	public function __construct($url,$debug)
	{
		$this->debug    = $debug;

		$this->guzzleClient = new Client([
			"base_uri" => $url,
			"timeout" => 180
		]);
	}

	public function setGuzzleClient(Client $client)
	{
		$this->guzzleClient = $guzzleClient;
	}

	
	public function postDemo(array $demo)
	{
		$response = $this->guzzleClient->request("POST", "/demo/new", $this->getOption($demo));

		if($response->getStatusCode() !== 201)
		{
			$this->handleError($response);
		}

		return json_decode($response->getBody());
	}

	public function handleError($response)
	{
		if($response->hasHeader("X-Debug-Token-Link"))
		{
			throw new \Exception("Error happened. See more at " . $response->getHeader("X-Debug-Token-Link"));
		}
		throw new \Exception("Error happened : " . $response->getStatusCode() . "\n Enable debug to see more.");
	}

	private function getOption($body, $json = true,$filename = "")
	{
		return [
			"body" => $json?json_encode($body):$body,
			"debug" => $this->debug?fopen($this->debug,'w'):false,
			"headers" => [
				'Content-Type' => $json  ? 'application/json' : 'application/octet-stream',
				"X-File-Name" => $filename
			]			
		];
	}
}