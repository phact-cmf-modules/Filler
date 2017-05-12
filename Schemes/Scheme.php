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


use Phact\Helpers\Paths;
use Phact\Main\Phact;
use Phact\Orm\Fields\BooleanField;
use Phact\Orm\Fields\CharField;
use Phact\Orm\Fields\DateField;
use Phact\Orm\Fields\DateTimeField;
use Phact\Orm\Fields\DecimalField;
use Phact\Orm\Fields\EmailField;
use Phact\Orm\Fields\Field;
use Phact\Orm\Fields\FileField;
use Phact\Orm\Fields\FloatField;
use Phact\Orm\Fields\ForeignField;
use Phact\Orm\Fields\ImageField;
use Phact\Orm\Fields\IntField;
use Phact\Orm\Fields\JsonField;
use Phact\Orm\Fields\PositionField;
use Phact\Orm\Fields\SlugField;
use Phact\Orm\Fields\TextField;
use Phact\Orm\Fields\TimeField;
use Phact\Orm\Model;
use Phact\Storage\Files\LocalFile;

abstract class Scheme
{
    public $dict;

    public $dictSettings = [];

    public $limit;

    public $fields = [];

    public $skip = [];

    public $modelClass;

    public $defaultSkip = [];

    public static $lorem = [
        0 => 'lorem',
        1 => 'ipsum',
        2 => 'dolor',
        3 => 'sit',
        4 => 'amet',
        5 => 'consectetur',
        6 => 'adipiscing',
        7 => 'elit',
        8 => 'praesent',
        9 => 'interdum',
        10 => 'dictum',
        11 => 'mi',
        12 => 'non',
        13 => 'egestas',
        14 => 'nulla',
        15 => 'in',
        16 => 'lacus',
        17 => 'sed',
        18 => 'sapien',
        19 => 'placerat',
        20 => 'malesuada',
        21 => 'at',
        22 => 'erat',
        23 => 'etiam',
        24 => 'id',
        25 => 'velit',
        26 => 'finibus',
        27 => 'viverra',
        28 => 'maecenas',
        29 => 'mattis',
        30 => 'volutpat',
        31 => 'justo',
        32 => 'vitae',
        33 => 'vestibulum',
        34 => 'metus',
        35 => 'lobortis',
        36 => 'mauris',
        37 => 'luctus',
        38 => 'leo',
        39 => 'feugiat',
        40 => 'nibh',
        41 => 'tincidunt',
        42 => 'a',
        43 => 'integer',
        44 => 'facilisis',
        45 => 'lacinia',
        46 => 'ligula',
        47 => 'ac',
        48 => 'suspendisse',
        49 => 'eleifend',
        50 => 'nunc',
        51 => 'nec',
        52 => 'pulvinar',
        53 => 'quisque',
        54 => 'ut',
        55 => 'semper',
        56 => 'auctor',
        57 => 'tortor',
        58 => 'mollis',
        59 => 'est',
        60 => 'tempor',
        61 => 'scelerisque',
        62 => 'venenatis',
        63 => 'quis',
        64 => 'ultrices',
        65 => 'tellus',
        66 => 'nisi',
        67 => 'phasellus',
        68 => 'aliquam',
        69 => 'molestie',
        70 => 'purus',
        71 => 'convallis',
        72 => 'cursus',
        73 => 'ex',
        74 => 'massa',
        75 => 'fusce',
        76 => 'felis',
        77 => 'fringilla',
        78 => 'faucibus',
        79 => 'varius',
        80 => 'ante',
        81 => 'primis',
        82 => 'orci',
        83 => 'et',
        84 => 'posuere',
        85 => 'cubilia',
        86 => 'curae',
        87 => 'proin',
        88 => 'ultricies',
        89 => 'hendrerit',
        90 => 'ornare',
        91 => 'augue',
        92 => 'pharetra',
        93 => 'dapibus',
        94 => 'nullam',
        95 => 'sollicitudin',
        96 => 'euismod',
        97 => 'eget',
        98 => 'pretium',
        99 => 'vulputate'
    ];

    public function fill()
    {
        if ($this->dict) {
            if (mb_strpos($this->dict, '.', null, 'UTF-8') === false) {
                $this->dict = 'Modules.Filler.dict.' . $this->dict;
            }
            $path = Paths::file($this->dict, ['php']);
            if ($path && ($dict = require $path)) {
                $this->handleDict($dict);
            } else {
                throw new \Exception("Empty dict for {$this->modelClass}");
            }
        } else {
            $this->handleRandom();
        }
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        $class = $this->modelClass;
        return new $class;
    }

    public function handleRandom()
    {
        if (!$this->limit) {
            $this->limit = 100;
        }
        for ($i = 0;$i < $this->limit;$i++) {
            $this->handleItem([]);
        }
    }

    public function prepareDictItem($item)
    {
        if ($this->dictSettings) {
            foreach ($this->dictSettings as $previous => $new)  {
                $value = null;
                if (isset($item[$previous])) {
                    $value = $item[$previous];
                    unset($item[$previous]);
                    $item[$new] = $value;
                }
            }
        }
        return $item;
    }

    public function handleDict($dict, $additional = [])
    {
        foreach ($dict as $item) {
            $this->handleItem([
                'attributes' => $this->prepareDictItem($item)
            ]);
        }
    }

    public function handleItem($options)
    {
        $model = $this->getModel();
        $skip = $this->skip + $this->defaultSkip;
        if (isset($options['attributes'])) {
            $model->setAttributes($options['attributes']);
            $skip = array_merge(array_keys($options['attributes']), $skip);
        }
        $fields = $model->getFields();
        foreach ($skip as $name) {
            if (isset($fields[$name])) {
                unset($fields[$name]);
            }
        }
        $attributes = [];
        foreach ($fields as $name => $field) {
            $schemaField = isset($this->fields[$name]) ? $this->fields[$name] : [];
            list($set, $attribute) = $this->handleItemField($model->getField($name), $schemaField);
            if ($set) {
                $attributes[$name] = $attribute;
            }
        }
        $model->setAttributes($attributes);
        $model->save();
        return $model;
    }

    /**
     * @param $field Field
     * @param $schemaField
     * @return array
     */
    public function handleItemField($field, $schemaField)
    {
        $fieldClass = get_class($field);
        if ($fieldClass) {
            if ($choices = $field->choices) {
                $choicesValues = array_keys($choices);
                if (!$field->getIsRequired()) {
                    $choicesValues[] = null;
                }
                $key = array_rand($choicesValues);
                return [true, $choicesValues[$key]];
            }
            $methods = $this->getFieldsMethods();
            foreach ($methods as $class => $method) {
                if (is_a($fieldClass, $class, true)) {
                    return $this->{$method}($field, $schemaField);
                }
            }
        }
        return [false, null];
    }

    public static function getFieldsMethods()
    {
        return [
            JsonField::class => 'skipItemField',
            SlugField::class => 'skipItemField',
            PositionField::class => 'skipItemField',
            ForeignField::class => 'handleItemForeignField',
            ImageField::class => 'handleItemImageField',
            FileField::class => 'handleItemFileField',
            BooleanField::class => 'handleItemBooleanField',
            DateField::class => 'handleItemDateField',
            DateTimeField::class => 'handleItemDateTimeField',
            TimeField::class => 'handleItemTimeField',
            DecimalField::class => 'handleItemIntField',
            FloatField::class => 'handleItemIntField',
            EmailField::class => 'handleItemEmailField',

            TextField::class => 'handleItemTextField',
            CharField::class => 'handleItemCharField',
            IntField::class => 'handleItemIntField',
        ];
    }

    public function skipItemField($field, $schemaField)
    {
        return [false, null];
    }

    /**
     * @param ForeignField $field
     * @param $schemaField
     * @return array
     */
    public function handleItemForeignField($field, $schemaField)
    {
        $class = $field->getRelationModelClass();
        $filter = isset($schemaField['filter']) ? $schemaField['filter'] : [];
        $exclude = isset($schemaField['exclude']) ? $schemaField['exclude'] : [];
        $count = $class::objects()->filter($filter)->exclude($exclude)->count();
        if ($count) {
            $offset = rand(0, $count - 1);
            $item = $class::objects()->filter($filter)->exclude($exclude)->offset($offset)->get();
            if ($item) {
                return [true, $item->id];
            }
        }
        return [false, null];
    }

    public function handleItemTextField($field, $schemaField)
    {
        return $this->handleItemCharField($field, array_merge([
            'fromWords' => 20,
            'toWords' => 100
        ], $schemaField));
    }

    /**
     * @param CharField $field
     * @param $schemaField
     * @return array
     */
    public function handleItemCharField($field, $schemaField)
    {
        $fromWords = isset($schemaField['fromWords']) ? $schemaField['fromWords'] : 2;
        $toWords = isset($schemaField['toWords']) ? $schemaField['toWords'] : 5;
        $words = rand($fromWords, $toWords);
        $sentence = [];
        for ($i = 0;$i < $words;$i++) {
            $word = static::$lorem[array_rand(static::$lorem)];
            if ($i == 0) {
                $word = ucfirst($word);
            }
            $sentence[] = $word;
        }
        if ($sentence) {
            return [true, implode(' ', $sentence)];
        }
        return [false, null];
    }

    /**
     * @param IntField $field
     * @param $schemaField
     * @return array
     */
    public function handleItemIntField($field, $schemaField)
    {
        $from = isset($schemaField['from']) ? $schemaField['from'] : 0;
        $to = isset($schemaField['to']) ? $schemaField['to'] : 1000;
        $value = rand($from, $to);
        return [true, $value];
    }

    /**
     * @param ImageField $field
     * @param $schemaField
     * @return array
     */
    public function handleItemImageField($field, $schemaField)
    {
        return $this->handleItemFileField($field, array_merge([
            'path' => 'Modules.Filler.images'
        ], $schemaField));
    }

    /**
     * @param FileField $field
     * @param $schemaField
     * @return array
     */
    public function handleItemFileField($field, $schemaField)
    {
        $path = isset($schemaField['path']) ? $schemaField['path'] : 'Modules.Filler.files';
        $extensions = isset($schemaField['extensions']) ? $schemaField['extensions'] : null;
        $dir = Paths::get($path);
        if ($extensions && is_array($extensions)) {
            $files = glob($dir . '/*.{' . implode(',', $extensions) . '}', GLOB_BRACE);
        } else {
            $files = glob($dir . '/*.*');
        }
        if ($files) {
            $key = array_rand($files);
            $filepath = $files[$key];
            $value = new LocalFile($filepath);
            return [true, $value];
        }
        return [false, null];
    }


    /**
     * @param BooleanField $field
     * @param $schemaField
     * @return array
     */
    public function handleItemBooleanField($field, $schemaField)
    {
        return [true, rand(0,1)];
    }

    /**
     * @param DateTimeField $field
     * @param $schemaField
     * @return array
     */
    public function handleItemDateTimeField($field, $schemaField)
    {
        return $this->handleItemFileField($field, array_merge([
            'format' => 'Y-m-d H:i:s'
        ], $schemaField));
    }

    /**
     * @param TimeField $field
     * @param $schemaField
     * @return array
     */
    public function handleItemTimeField($field, $schemaField)
    {
        return $this->handleItemFileField($field, array_merge([
            'format' => 'H:i:s'
        ], $schemaField));
    }

    /**
     * @param DateField $field
     * @param $schemaField
     * @return array
     */
    public function handleItemDateField($field, $schemaField)
    {
        $from = isset($schemaField['from']) ? $schemaField['from'] : "-100 days";
        $to = isset($schemaField['to']) ? $schemaField['to'] : "+100 days";
        $format = isset($schemaField['format']) ? $schemaField['format'] : "Y-m-d";

        $fromTime = strtotime($from);
        $toTime = strtotime($to);

        $date = date($format, rand($fromTime, $toTime));
        return [true, $date];
    }

    /**
     * @param EmailField $field
     * @param $schemaField
     * @return array
     */
    public function handleItemEmailField($field, $schemaField)
    {
        $username = static::$lorem[array_rand(static::$lorem)];
        $domain = static::$lorem[array_rand(static::$lorem)];
        $zone = mb_substr(static::$lorem[array_rand(static::$lorem)], 0, 2);

        $email = "{$username}@{$domain}.{$zone}";

        return [true, $email];
    }
}