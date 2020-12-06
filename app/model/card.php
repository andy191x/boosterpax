<?php

//
// Card
// Data model for representing an MTG card throughout this application
//

//
// Class definition
//

class Card extends ErrorBase
{
    //
    // Private data
    //

    /** @var Map */ private $map;

    //
    // Public routines
    //

    public function __construct()
    {
        parent::__construct();

        $this->map = new Map();
        $this->map->setStrongType('id', 'string');          // scryfall UUID string 'd5c83259-9b90-47c2-b48e-c7d78519e792'
        $this->map->setStrongType('name', 'string');        // 'Animate Wall'
        $this->map->setStrongType('colors', 'string');      // CSV color string: 'W,B' or 'W'
        $this->map->setStrongType('rarity', 'string');      // 'common'|'uncommon'|'rare'|...
        $this->map->setStrongType('rarity_app', 'string');   // Custom rarity field. Arabian nights uses this.
        $this->map->setStrongType('cmc', 'integer');        // converted mana cost
        $this->map->setStrongType('type', 'string');        // 'Enchantment'|...

        $this->map->setMany(array(
            'id' => '',
            'name' => '',
            'colors' => '',
            'rarity' => '',
            'rarity_app' => '',
            'cmc' => 0,
            'type' => ''
        ));
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

    //
    // Static public routines
    //

    /**
     * @param Card[] $card_array
     * @param int $format
     * @param string $eol
     */
    static public function debug($card_array, $format = 0, $eol = PHP_EOL)
    {
        echo 'Cards: ' . count($card_array) . $eol;
        echo '---' . $eol;
        foreach ($card_array as $card)
        {
            if ($format == 0)
            {
                echo $card->get('name') . $eol;
            }
            else
            {
                echo $card->get('name') . ' (' . $card->get('id') . ')' . $eol;
            }
        }
    }

    // ...
}
