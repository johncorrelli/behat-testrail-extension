<?php

namespace flexperto\BehatTestrailReporter\testrail;

use Httpful\Mime;
use Httpful\Request;
use Faker\Factory;

class TestrailApiClient
{
    /** @var string testrail base url */
    private $baseUrl;

    /** @var string testrail username  */
    private $username;

    /** @var string test rail api key. Might as well be an account password, though it is considered as a bad practice */
    private $apiKey;

    public function __construct(string $baseUrl, string $username, string $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->username = $username;
        $this->apiKey = $apiKey;
    }

    private function initRequest()
    {
        $request = Request::init();
        $request = $request
            ->basicAuth($this->username, $this->apiKey);

        return $request;
    }

    public function createTestRun(string $projectId)
    {
        $faker = Factory::create();
        $projectDetails = [
            'name' => $faker->colorName . ' ' . $faker->city
        ];

        $response = $this->initRequest()
            ->uri("{$this->baseUrl}/add_run/{$projectId}")
            ->body(json_encode($projectDetails, JSON_PRETTY_PRINT), Mime::JSON)
            ->method("POST")
            ->send();

        if ($response->code !== 200) {
            echo "silently saying that testrail request failed with code {$response->code} and body $response->raw_body\n";
        }

        $body = json_decode($response->raw_body);
        $runId = $body->id;

        echo "\nTestRail Test Run #{$runId} created: {$projectDetails['name']}\n\n";

        return $body->id;
    }

    public function pushResultsBatch(string $runId, $pendingResultsAccumulator)
    {
        $response = $this->initRequest()
            ->uri("{$this->baseUrl}/add_results_for_cases/{$runId}")
            ->body(json_encode([ "results" => array_values($pendingResultsAccumulator)], JSON_PRETTY_PRINT), Mime::JSON)
            ->method("POST")
            ->send();
        if ($response->code !== 200) {
            echo "silently saying that testrail request failed with code {$response->code} and body $response->raw_body\n";
        }

        echo "\nResults added to TestRail Test Run #{$runId}\n\n";
    }
}
