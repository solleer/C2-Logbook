<?php
namespace Solleer\C2Logbook\Maphper;
use Maphper\DataSource;
use Maphper\DataSource\Mock as ArrayDataSource;
use Solleer\C2Logbook\{User};

class Results implements DataSource {
    private $user;
    private $pk = "id";
    private $cache = [];

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function getPrimaryKey() {
        return [$this->pk];
    }

	public function findById($id) {
        return (object) $this->user->getResult($id) ?: [];
    }

	public function findByField(array $fields, $options = []) {
        $cacheId = md5(serialize(func_get_args()));

        if (!isset($this->cache[$cacheId])) {
            $results = $this->convertToObjDeep($this->user->getResults($fields));
            $newFields = array_diff_key($fields, ['from' => 0, 'to' => 0, 'type' => 0, 'updated_after' => 0]);
            
            $arrayDatasource = new ArrayDataSource(new \ArrayObject($results), $this->pk);
            $results = $arrayDatasource->findByField($newFields, $options);

            $this->cache[$cacheId] = $this->convertToObjDeep($results);
        }

        return $this->cache[$cacheId];
    }

	public function findAggregate($function, $field, $group = null, array $criteria = [], array $options = []) {
        return $function($this->findByField($criteria));
    }

	public function deleteById($id) {
        $this->user->deleteResult($id);
    }

	public function deleteByField(array $fields, array $options) {
        $results = $this->findByField($fields, $options);
        foreach ($results as $result) {
            $this->deleteById($result->{$this->pk});
        }
    }

	public function save($data) {
        $response = $this->user->addResult($data);
        if (!$response) $this->user->updateResult($data->{$this->pk}, $data);
    }

	public function getErrors() {
        return [];
    }

    private function convertToObjDeep(array $data) {
        return json_decode(json_encode($data));
    }
}
