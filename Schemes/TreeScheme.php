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
 * @date 31/01/17 09:57
 */

namespace Modules\Filler\Schemes;

class TreeScheme extends Scheme
{
    public $defaultSkip = [
        'lft',
        'rgt',
        'depth',
        'root'
    ];

    public function handleDict($dict, $additional = [])
    {
        if (!isset($additional['parent'])) {
            $additional['parent'] = null;
        }
        foreach ($dict as $item) {
            $children = [];
            if (isset($item['__children'])) {
                $children = $item['__children'];
                unset($item['__children']);
            }
            $item = array_merge($additional, $item);
            $item = $this->handleItem([
                'attributes' => $this->prepareDictItem($item)
            ]);
            if ($children && $item->id) {
                $this->handleDict($children, [
                    'parent' => $item->id
                ]);
            }
        }
    }
}