<?php
namespace Solleer\C2Logbook;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class User {
    private $client;
    private $id;
    private $addedResultFields = ['num_intervals'];

    public function __construct(Client $client, $id = "me") {
        $this->client = $client;
        $this->id = $id;
    }

    public function getUser() {
        $response = $this->client->request('GET', $this->id);
        return $this->interpretResponse($response);
    }

    public function updateUser(\stdClass $data): bool {
        $response = $this->client->request('PATCH', $this->id, ['json' => $data]);
        return (bool) $this->interpretResponse($response);
    }

    /*
     * The filter parameter accepts 4 properties: from, to, type, updated_after
     */
    public function getResults($filter) {
        $response = $this->client->request('GET', $this->id . '/results', [
            'query' => $filter]);
        $data = $this->interpretResponse($response);
        if (!$data) return false;

        foreach ($this->getOtherResultPages($this->decodeBody($response->getBody())) as $pageResult)
            $data = array_merge($data, $pageResult);
        foreach ($data as $key => $workout) $data[$key] = $this->addAdditionalResultFields($workout);
        return $data;
    }

    private function getOtherResultPages($response) {
        while (isset($response['meta']['pagination']['links']['next'])) {
            $nextPageLink = $response['meta']['pagination']['links']['next'];

            $response = $this->client->request('GET', $nextPageLink]);
            $data = $this->interpretResponse($response);
            if ($data) yield $data;
        }
    }

    public function addResult($data) {
        $data = $this->removeAddedResultFields($data);
        $response = $this->client->request('POST', $this->id . '/results', ['json' => $data]);
        return $response->getStatusCode() === 201;
    }

    public function getResult($id) {
        $response = $this->client->request('GET', $this->id . '/results/' . $id);
        $data = $this->interpretResponse($response);
        if ($data) $data = $this->addAdditionalResultFields($data);
        return $data;
    }

    public function updateResult($id, \stdClass $data) {
        $data = $this->removeAddedResultFields($data);
        $response = $this->client->request('PATCH', $this->id . '/results/' . $id, ['json' => $data]);
        return $this->interpretResponse($response);
    }

    public function deleteResult($id) {
        $response = $this->client->request('DELETE', $this->id . '/results/' . $id);
        return $this->interpretResponse($response);
    }

    /*
     * Takes a response object and returns false if it was unsuccessful
     * or the data if is was successful
     */
    private function interpretResponse(ResponseInterface $response) {
        if ($response->getStatusCode() !== 200) return false;
        return $this->decodeBody($response->getBody())['data'];
    }

    private function decodeBody($json) {
        return json_decode($json, true);
    }

    private function removeAddedResultFields(\stdClass $data): \stdClass  {
        foreach ($this->addedFields as $field) unset($data->$field);
        return $data;
    }

    private function addAdditionalResultFields(array $data): array {
        if (isset($data['workout_type'])) { // Ensure its a workout
            if (strpos($data['workout_type'], "Interval") !== false)
                $data['num_intervals'] = count($data['workout']) ?? null;
        }
        return $data;
    }
}
