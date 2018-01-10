<?php
namespace Solleer\C2Logbook\Maphper;
use MaphperLoader\DataSource;

class Loader implements DataSource {
    public function load(array $config)  {
        return [
            'instanceOf' => 'Solleer\\C2Logbook\\Maphper\\' . $config['table']
        ];
    }
}
