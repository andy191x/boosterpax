<?php

//
// CardSet
// Data model for representing an MTG card set throughout this application
//

//
// Class definition
//

class CardSet extends ErrorBase
{
    //
    // Private data
    //

    /** @var Map */ private $map;
    /** @var Card[] */ private $card_array;

    //
    // Private routines
    //

    /**
     * @return mixed[]
     */
    private static function getDefaultMap()
    {
        return array(
            'code' => '',
            'name' => '',
            'released_at' => '',
            'card_count' => 0,
        );
    }

    //
    // Public routines
    //

    public function __construct()
    {
        parent::__construct();

        $this->map = new Map();
        $this->map->setStrongType('code', 'string');          // 'lea'
        $this->map->setStrongType('name', 'string');          // 'Limited Edition Alpha'
        $this->map->setStrongType('released_at', 'string');   // '1993-08-05'
        $this->map->setStrongType('card_count', 'integer');   // 295
        $this->map->setMany(self::getDefaultMap());

        $this->card_array = array();
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->map->get($key);
    }

    /**
     * @return mixed[]
     */
    public function getMap()
    {
        return $this->map->getMap();
    }

    /**
     * @param string $key
     * @param mixed $val
     */
    public function set($key, $val)
    {
        if ($this->map->has((string)$key))
        {
            $this->map->set($key, $val);
        }
    }

    /**
     * @param Card[] $card_array
     */
    public function setCardArray($card_array)
    {
        $this->card_array = $card_array;
    }

    /**
     * @return Card[]
     */
    public function getCardArray()
    {
        return $this->card_array;
    }

    /**
     * @param string $db_json
     * @return bool
     */
    public function loadFromFile($db_json)
    {
        // Load data
        if (strlen($db_json) == 0)
        {
            $this->addError(ErrorType::makeByText('Invalid JSON file name.'));
            return false;
        }

        $json = file_get_contents($db_json);
        if ($json === false)
        {
            $this->addError(ErrorType::makeByText('Cannot load JSON file.'));
            return false;
        }

        $data = json_decode($json, true);
        if ($data === false)
        {
            $this->addError(ErrorType::makeByText('Invalid JSON data.'));
            return false;
        }

        if (!isset($data['set']) || !isset($data['card_array']))
        {
            $this->addError(ErrorType::makeByText('Invalid JSON data format.'));
            return false;
        }

        // Load objects
        $set_def = self::getDefaultMap();
        foreach ($set_def as $k => $v)
        {
            if (isset($data['set'][$k]))
            {
                $this->set($k, $data['set'][$k]);
            }
        }

        $card_array = array();
        foreach ($data['card_array'] as $data_card)
        {
            $card = new Card();
            foreach ($data_card as $k => $v)
            {
                $card->set($k, $v);
            }
            $card_array[] = $card;
        }
        $this->setCardArray($card_array);

        return true;
    }

    /**
     * Query for cards.
     * @param string $key - Card field keyname
     * @param string $op - 'is'|'is_not'|'contains'|'not_contains'
     * @param string $query
     * @param bool $match_case
     * @param int $limit
     * @return Card[]
     */
    public function query($key, $op, $query, $match_case = false, $limit = 0)
    {
        $result = array();

        $temp = new Card();
        $temp_map = $temp->getMap();
        if (!isset($temp_map[$key]))
        {
            return $result;
        }

        foreach ($this->card_array as $card)
        {
            $val = (string)$card->get($key);
            $match = false;

            if ($op == 'is')
            {
                if ($match_case)
                {
                    $match = ($val === $query);
                }
                else
                {
                    $match = (strcasecmp($val, $query) === 0);
                }
            }
            else if ($op == 'is_not')
            {
                if ($match_case)
                {
                    $match = ($val !== $query);
                }
                else
                {
                    $match = (strcasecmp($val, $query) !== 0);
                }

            }
            else if ($op == 'contains')
            {
                if ($match_case)
                {
                    $match = (strpos($val, $query) !== false);
                }
                else
                {
                    $match = (stripos($val, $query) !== false);
                }
            }
            else if ($op == 'not_contains')
            {
                if ($match_case)
                {
                    $match = (strpos($val, $query) === false);
                }
                else
                {
                    $match = (stripos($val, $query) === false);
                }
            }

            if ($match)
            {
                $result[] = clone $card;

                if ($limit > 0 && count($result) == $limit)
                {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Query for cards. Use multiple queries to build a more complex result set.
     * @param mixed[] $include - Array of queries to include in result. Format: array( array( 'name', 'contains', ... ) )
     * @param mixed[] $exclude - Array of queries to exclude from result. Format: array( array( 'name', 'contains', ... ) )
     * @param int $limit
     * @return Card[]
     */
    public function queryMulti($include = array(), $exclude = array(), $limit = 0)
    {
        $result = array();

        if (count($include) == 0 && count($exclude) > 0)
        {
            // Exclude cards from entire set
            $include[] = array('id', 'is_not', '');
        }

        $selected_map = array();

        $query_list = array_merge($include, $exclude);
        $query_index = 0;

        foreach ($query_list as $query)
        {
            $arg_key = isset($query[0]) ? $query[0] : '';
            $arg_op = isset($query[1]) ? $query[1] : '';
            $arg_query = isset($query[2]) ? $query[2] : '';
            $arg_match_case = isset($query[3]) ? $query[3] : false;
            $arg_limit = isset($query[4]) ? $query[4] : 0;

            $selected = $this->query($arg_key, $arg_op, $arg_query, $arg_match_case, $arg_limit);

            if ($query_index < count($include))
            {
                // Include
                foreach ($selected as $card)
                {
                    $id = $card->get('id');
                    if (!isset($selected_map[$id]))
                    {
                        $selected_map[$id] = $card;
                    }
                }
            }
            else
            {
                // Exclude
                foreach ($selected as $card)
                {
                    $id = $card->get('id');
                    unset($selected_map[$id]);
                }
            }

            $query_index++;
        }

        $id_array = array_keys($selected_map);
        foreach ($id_array as $id)
        {
            $result[] = $selected_map[$id];
            if ($limit > 0 && count($result) == $limit)
            {
                break;
            }
        }

        return $result;
    }

    //
    // Static public routines
    //

    /**
     * @return string[]
     */
    static public function uriSlugCreateMap()
    {
        $uri_map = array();

        $uri_map['alpha'] = 'lea';
        $uri_map['beta'] = 'leb';
        $uri_map['unlimited'] = '2ed';
        $uri_map['arabian-nights'] = 'arn';
        $uri_map['antiquities'] = 'atq';
        $uri_map['legends'] = 'leg';
        $uri_map['revised'] = '3ed';
        $uri_map['the-dark'] = 'drk';

        return $uri_map;
    }

    /**
     * @param string $uri
     * @return string
     */
    static public function uriSlugToSetCode($uri)
    {
        $uri_map = self::uriSlugCreateMap();
        return isset_or_default($uri_map, $uri, '');
    }

    /**
     * @param $setcode
     * @return string
     */
    static public function uriSlugFromSetCode($setcode)
    {
        $uri_map = self::uriSlugCreateMap();
        foreach ($uri_map as $k => $v)
        {
            if ($v == $setcode)
            {
                return $k;
            }
        }

        return '';
    }


    // ...
}
