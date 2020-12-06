<?php

//
// PackGenerator
// Class for generating MTG booster packs for any given set.
//

//
// Class definition
//

class PackGenerator extends ErrorBase
{
    //
    // Private data
    //

    /** @var CardSet */ private $cardset;
    /** @var int */ private $seed;

    //
    // Public routines
    //

    public function __construct()
    {
        parent::__construct();
        $this->cardset = new CardSet();
        $this->seed = mt_rand(0, 0x7fffffff);
    }

    /**
     * @param CardSet $cardset
     */
    public function setCardSet($cardset)
    {
        $this->cardset = $cardset;
    }

    /**
     * @param int $seed
     */
    public function setSeed($seed)
    {
        $this->seed = (int)abs($seed);
    }

    public function getSeed()
    {
        return $this->seed;
    }

    /**
     * @param Card[] $card_array
     * @return bool
     */
    public function generate(&$card_array)
    {
        $card_array = array();
        $code = $this->cardset->get('code');

        if ($code == 'lea' || $code == 'leb' || $code == '2ed')
        {
            // From: https://www.reddit.com/r/mtgfinance/comments/8d8fvb/the_definitive_guide_to_print_runs/
            // Alpha/beta/unlimited print sheets were 11 x 11 (121 cards/sheet).
            // Boosters had 15 cards with 1 card from the rare sheet, 3 cards from the uncommon sheet and 11 cards from the common sheet.
            // Alpha:
            //  Rare sheet: 116 rares, 5 islands.
            //  Uncommon sheet: 95 uncommons, 26 lands.
            //  Common sheet: 74 commons, 47 lands.
            // Beta/unlimited:
            //  Rare sheet: 117 rares, 4 islands.
            //  Uncommon sheet: 95 uncommons, 26 lands.
            //  Common sheet: 75 commons, 46 lands.

            // Assemble sheets
            $basic_lands = $this->basicLandSort($this->cardset->query('type', 'contains', 'Basic Land ', true));
            $islands = $this->cardset->query('name', 'is', 'island');

            $rare_sheet = $this->cardset->query('rarity', 'is', 'rare');
            if ($code == 'lea')
            {
                $rare_sheet[] = clone $islands[0];
                $rare_sheet[] = clone $islands[1];
                $rare_sheet[] = clone $islands[0];
                $rare_sheet[] = clone $islands[1];
                $rare_sheet[] = clone $islands[0];
            }
            else
            {
                $rare_sheet[] = clone $islands[0];
                $rare_sheet[] = clone $islands[1];
                $rare_sheet[] = clone $islands[2];
                $rare_sheet[] = clone $islands[0];
            }

            $uncommon_sheet = $this->cardset->query('rarity', 'is', 'uncommon');
            for ($i = 0; $i < 26; $i++)
            {
                $land_index = $i % count($basic_lands);
                $uncommon_sheet[] = clone $basic_lands[$land_index];
            }

            $common_sheet = $this->cardset->queryMulti(
                array(
                    array('rarity', 'is', 'common')
                ),
                array(
                    array('type', 'contains', 'Basic Land ')
                )
            );
            $common_land_count = ($code == 'lea') ? 47 : 46;
            for ($i = 0; $i < $common_land_count; $i++)
            {
                $land_index = $i % count($basic_lands);
                $common_sheet[] = clone $basic_lands[$land_index];
            }

            // Generate booster
            mt_srand($this->seed);
            $card_array = array_merge($card_array, $this->pickCards($common_sheet, 11, true));
            $card_array = array_merge($card_array, $this->pickCards($uncommon_sheet, 3, true));
            $card_array = array_merge($card_array, $this->pickCards($rare_sheet, 1, true));
            mt_srand();
            return true;
        }
        else if ($code == 'arn' || $code == 'atq' || $code == 'drk')
        {
            // From: https://www.reddit.com/r/mtgfinance/comments/8d8fvb/the_definitive_guide_to_print_runs/
            // Arabian Nights was also printed on 11x11 sheets, but there was no rare sheet.
            // The set was distributed in eight card boosters, each booster had two cards from the uncommon sheet and six cards from the common sheet.
            // Cards could appear multiple times on a single sheet, rarities ranged from U2 (twice on the uncommon sheet) to C11 (eleven times on the common sheet).
            // ---
            // From: https://mtg.gamepedia.com/Arabian_Nights
            // The U2 are considered the "rares" of the set.

            // From: https://www.reddit.com/r/mtgfinance/comments/8d8fvb/the_definitive_guide_to_print_runs/
            // Antiquties was pretty similar to Arabian Nights regarding card distribution, it was printed on 11x11 sheets and distributed in eight card boosters with two cards from the uncommon sheet and six cards from the common sheet.
            // The rarities were a bit different though, some of the cards only appeared once on the uncommon sheet (U1) and the lowest rarity was C4
            // ---
            // From: http://mtg.icequake.net/www.crystalkeep.com/magic/lists/files/list-aq.txt
            // 100 Cards Total (7 White, 7 Green, 7 Blue, 7 Red, 7 Black, 21 Land, 44 Artifacts; 41 Commons, 59 Uncommons)
            //  Explanation of Rarity: the number after the rarity (e.g.:common 1), means that that card appears that many times on the print sheet.

            // From: https://www.reddit.com/r/mtgfinance/comments/8d8fvb/the_definitive_guide_to_print_runs/
            // The Dark was printed on 11x11 sheets and distributed in eight card boosters (with two uncommons and six commons per booster) like Arabian Nights and Antiquities.
            // Rarities ranged from U1 to C3.

            // Assemble sheets
            $uncommon_card_array = $this->cardset->query('rarity_app', 'contains', 'u');
            $uncommon_sheet = array();
            foreach ($uncommon_card_array as $card)
            {
                $count = (int)str_replace('u', '', $card->get('rarity_app'));
                for ($i = 0; $i < $count; $i++)
                {
                    $uncommon_sheet[] = clone $card;
                }
            }

            $common_card_array = $this->cardset->query('rarity_app', 'contains', 'c');
            $common_sheet = array();
            foreach ($common_card_array as $card)
            {
                $count = (int)str_replace('c', '', $card->get('rarity_app'));
                for ($i = 0; $i < $count; $i++)
                {
                    $common_sheet[] = clone $card;
                }
            }

            // Generate booster
            mt_srand($this->seed);
            $card_array = array_merge($card_array, $this->pickCards($common_sheet, 6, true));
            $card_array = array_merge($card_array, $this->printRaritySort($this->pickCards($uncommon_sheet, 2, true)));
            mt_srand();
            return true;
        }
        else if ($code == 'leg')
        {
            // From: https://www.reddit.com/r/mtgfinance/comments/8d8fvb/the_definitive_guide_to_print_runs/
            // 15 cards per booster, 1 rare, 3 uncommons and 11 commons.
            // There was also a rules text card in each booster to explain the new mechanics (like everyone's favorite mechanic 'bands with other').
            // ---
            // From: http://mtg.icequake.net/www.crystalkeep.com/magic/lists/files/spoil-lg.txt
            // 310 Cards Total (43 Black, 43 Blue, 43 Green, 43 Red, 43 White, 55 Multicolor, 29 Artifacts, 11 Lands; 121 Rare, 114 Uncommon, 75 Common)
            // Explanation of Rarity: the number after the rarity (e.g.: Common 1), means that that card appears that many times on the print sheet.
            // ---
            // Andy: The wizards' release numbers for the uncommon sheet are wrong. "Unholy Citadel" and "Seafarer's Quay" are actually u1, not u2. I verified this against a photo of the print sheet.

            // Assemble sheets
            $rare_card_array = $this->cardset->query('rarity_app', 'contains', 'r');
            $rare_sheet = array();
            foreach ($rare_card_array as $card)
            {
                $count = (int)str_replace('r', '', $card->get('rarity_app'));
                for ($i = 0; $i < $count; $i++)
                {
                    $rare_sheet[] = clone $card;
                }
            }

            $uncommon_card_array = $this->cardset->query('rarity_app', 'contains', 'u');
            $uncommon_sheet = array();
            foreach ($uncommon_card_array as $card)
            {
                $count = (int)str_replace('u', '', $card->get('rarity_app'));
                for ($i = 0; $i < $count; $i++)
                {
                    $uncommon_sheet[] = clone $card;
                }
            }

            $common_card_array = $this->cardset->query('rarity_app', 'contains', 'c');
            $common_sheet = array();
            foreach ($common_card_array as $card)
            {
                $count = (int)str_replace('c', '', $card->get('rarity_app'));
                for ($i = 0; $i < $count; $i++)
                {
                    $common_sheet[] = clone $card;
                }
            }

            // Generate booster
            mt_srand($this->seed);
            $card_array = array_merge($card_array, $this->pickCards($common_sheet, 11, true));
            $card_array = array_merge($card_array, $this->pickCards($uncommon_sheet, 3, true));
            $card_array = array_merge($card_array, $this->pickCards($rare_sheet, 1, true));
            mt_srand();
            return true;
        }
        else if ($code == '3ed')
        {
            // Pick based on rarity: do not simulate printing sheets.
            $rare_card_array = $this->cardset->query('rarity', 'is', 'rare');
            $uncommon_card_array = $this->cardset->query('rarity', 'is', 'uncommon');
            $common_card_array = $this->cardset->query('rarity', 'is', 'common');

            // Generate booster
            mt_srand($this->seed);
            $card_array = array_merge($card_array, $this->pickCards($common_card_array, 11, false));
            $card_array = array_merge($card_array, $this->pickCards($uncommon_card_array, 3, false));
            $card_array = array_merge($card_array, $this->pickCards($rare_card_array, 1, true));
            mt_srand();
            return true;
        }

        $this->addError(ErrorType::makeByText('Unsupported card set.'));
        return false;
    }

    /**
     * @param Card[] $card_array
     * @param int $count
     * @param bool $unique
     * @return Card[]
     */
    private function pickCards($card_array, $count, $unique)
    {
        $result = array();

        if ($unique)
        {
            if (count($card_array) < $count)
            {
                return array();
            }
        }

        $used = array();
        for ($i = 0; $i < $count; $i++)
        {
            while (true)
            {
                $index = (int)mt_rand(0, count($card_array) - 1);
                if (!isset($used[$index]))
                {
                    $result[] = clone $card_array[$index];
                    if ($unique)
                    {
                        $used[$index] = true;
                    }
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Sorts an array of basic land in a staggered order. This helps with evenly distributing them across print sheets.
     * Example: r,r,g,g,w,w -> r,g,w,r,g,w
     * @param Card[] $card_array
     * @return Card[]
     */
    private function basicLandSort($card_array)
    {
        $land = array();
        $other = array();
        $max_depth = 0;

        $land_name_array = array('Swamp', 'Island', 'Forest', 'Mountain', 'Plains');

        foreach ($card_array as $card)
        {
            $name = $card->get('name');
            if (in_array($name, $land_name_array))
            {
                if (!isset($land[$name]))
                {
                    $land[$name] = array();
                }
                $land[$name][] = $card;

                $depth = count($land[$name]);
                if ($depth > $max_depth)
                {
                    $max_depth = $depth;
                }
            }
            else
            {
                $other[] = $card;
            }
        }

        $sort_array = array();
        for ($i = 0; $i < $max_depth; $i++)
        {
            foreach ($land_name_array as $name)
            {
                if ($i < count($land[$name]))
                {
                    $sort_array[] = $land[$name][$i];
                }
            }
        }

        if (count($other) > 0)
        {
            $sort_array = array_merge($sort_array, $other);
        }

        return $sort_array;
    }

    /**
     * Sorts an array of cards based on their print rarity ("rarity_app"). Only sorts if "rarity_app" is defined for each card.
     * Example: r,r,g,g,w,w -> r,g,w,r,g,w
     * @param Card[] $card_array
     * @return Card[]
     */
    private function printRaritySort($card_array)
    {
        foreach ($card_array as $card)
        {
            if (strlen($card->get('rarity_app')) == 0)
            {
                return $card_array;
            }
        }

        $sort_array = $card_array;
        usort($sort_array, function($a, $b)
        {
            $a_score = 400;
            $b_score = 400;

            $w_score = array();
            $w_score['r'] = 100;
            $w_score['u'] = 200;
            $w_score['c'] = 300;

            $matches = array();
            if (preg_match('/(\w)(\d+)/', $a->get('rarity_app'), $matches))
            {
                $w = $matches[1];
                $d = clamp(0, 99, (int)$matches[2]);

                if (isset($w_score[$w]))
                {
                    $a_score = $w_score[$w];
                }
                $a_score += $d;
            }

            $matches = array();
            if (preg_match('/(\w)(\d+)/', $b->get('rarity_app'), $matches))
            {
                $w = $matches[1];
                $d = clamp(0, 99, (int)$matches[2]);

                if (isset($w_score[$w]))
                {
                    $b_score = $w_score[$w];
                }
                $b_score += $d;
            }

            return $b_score <=> $a_score;
        });

        return $sort_array;
    }

    // ...
}
