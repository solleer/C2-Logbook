<?php
namespace Solleer\C2Logbook;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class User {
    private $client;
    private $id;
    private $addedResultFields = ['num_intervals'];
    private $cache = [];

    public function __construct(Client $client, string $id = "me") {
        $this->client = $client;
        $this->id = $id;
    }

    private function clientCall($method, $query = '', $otherData = []) {
        $response = $this->client->request($method, $query, $otherData);
        return $this->interpretResponse($response);
    }

    public function getUser() {
        return $this->clientCall('GET', $this->id);
    }

    public function updateUser(\stdClass $data): bool {
        return (bool) $this->clientCall('PATCH', $this->id, ['json' => $data]);
    }

    /*
     * The filter parameter accepts 4 properties: from, to, type, updated_after
     */
    public function getResults($filter) {
        $filter = array_intersect_key($filter, ['from' => 0, 'to' => 0, 'type' => 0, 'updated_after' => 0]);
        $cacheId = md5(serialize($filter));

        if (!isset($this->cache[$cacheId])) {
            $filter['number'] = 200;
            $filter['page'] = 1;

            $response = $this->client->request('GET', $this->id . '/results', [
                'query' => $filter]);

            $data = $this->interpretResponse($response);

            if (!$data) $this->cache[$cacheId] = [];
            else {
                foreach ($this->asyncGetOtherPages($this->decodeBody($response->getBody()), $filter) as $pageResult)
                    $data = array_merge($data, $pageResult);
                foreach ($data as $key => $workout) $data[$key] = $this->addAdditionalResultFields($workout);
                $this->cache[$cacheId] = $data;
            }
        }
        
        return $this->cache[$cacheId];
    }

    private function asyncGetOtherPages($response, $filter) {
        if (!isset($response['meta']['pagination'])) return [];

        // Initiate each request but do not block
        $promises = [];
        for ($i = 2; $i <= $response['meta']['pagination']['total_pages']; $i++) {
            $filter['page'] = $i;
            $promises[] = $this->client->getAsync($this->id . '/results', ['query' => $filter]);
        }

        // Wait on all of the requests to complete. Throws a ConnectException
        // if any of the requests fail
        $results = \GuzzleHttp\Promise\unwrap($promises);

        // Wait for the requests to complete, even if some of them fail
        $results = \GuzzleHttp\Promise\settle($promises)->wait();

        // You can access each result using the key provided to the unwrap
        // function.
        //echo $results['image']['value']->getHeader('Content-Length')[0];
        //echo $results['png']['value']->getHeader('Content-Length')[0];

        foreach ($results as $value) {
            yield $this->interpretResponse($value['value']);
        }
    }

    public function addResult($data) {
        $data = $this->removeAddedResultFields($data);
        $response = $this->client->request('POST', $this->id . '/results', ['json' => $data]);
        return $response->getStatusCode() === 201;
    }

    public function getResult($id) {
        if (!isset($this->cache[$id])) {
            $response = $this->client->request('GET', $this->id . '/results/' . $id);
            $data = $this->interpretResponse($response);
            if ($data) $data = $this->addAdditionalResultFields($data);
            $this->cache[$id] = $data;
        }

        return $this->cache[$id];
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
        foreach ($this->addedResultFields as $field) unset($data->$field);
        return $data;
    }

    private function addAdditionalResultFields(array $data): array {
        if (isset($data['workout_type'])) { // Ensure its a workout
            if (strpos($data['workout_type'], "Interval") !== false && isset($data['workout']))
                $data['num_intervals'] = count($data['workout']['intervals']) ?? null;
        }
        return $data;
    }
}
