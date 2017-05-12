<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Okulov Anton
 * @email qantus@mail.ru
 * @version 1.0
 * @company HashStudio
 * @site http://hashstudio.ru
 * @date 12/12/16 16:08
 */

namespace Modules\Filler\Commands;

use Modules\Filler\Schemes\FlatScheme;

use Modules\Filler\Schemes\TreeScheme;
use Phact\Commands\Command;
use Phact\Helpers\Configurator;
use Phact\Helpers\Paths;
use Phact\Orm\TreeModel;

class FillCommand extends Command
{
    public function handle($arguments = [])
    {
        $path = Paths::file('base.config.filler', ['php']);
        $data = require $path;
        if ($data) {
            foreach ($data as $class => $config) {
                $schemeName = isset($config['scheme']) ? $config['scheme'] : null;
                if (!$schemeName) {
                    $schemeName = is_a($class, TreeModel::class, true) ? 'tree' : 'flat';
                }
                $schemeClass = $this->getScheme($schemeName);
                if ($schemeClass) {
                    $scheme = Configurator::create($schemeClass, array_merge([
                        'modelClass' => $class
                    ], $config));
                    $scheme->fill();
                } else {
                    echo "Scheme for {$class} not found!" . PHP_EOL;
                }
            }
        }
    }

    public static function getScheme($name)
    {
        $list = self::getSchemesList();
        return isset($list[$name]) ? $list[$name] : null;
    }

    public static function getSchemesList()
    {
        return [
            'flat' => FlatScheme::class,
            'tree' => TreeScheme::class
        ];
    }
}