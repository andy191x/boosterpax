<?php

//
// Script logic
//

renderPage();
exit();

//
// Global routines
//

function renderPage()
{
    /**
    $cardset = new CardSet();
    if (!$cardset->loadFromFile(PROJECT_DB_FOLDER . '/' . 'drk' . '.json'))
    {
        echo 'cannot load.';
        exit();
    }

    $card_array = $cardset->getCardArray();
    foreach ($card_array as $card)
    {
        if ($card->get('rarity') == 'common')
        {
            $card->set('rarity_app', 'c3');
        }
        else if ($card->get('rarity') == 'uncommon')
        {
            $card->set('rarity_app', 'u2');
        }
        else if ($card->get('rarity') == 'rare')
        {
            $card->set('rarity_app', 'u1');
        }
    }

    $doc = array();
    $doc['set'] = $cardset->getMap();
    $doc['card_array'] = array();
    foreach ($card_array as $card)
    {
        $doc['card_array'][] = $card->getMap();
    }

    $json = json_encode($doc);
    echo $json;
    */

    /**
    $min = 1;
    $max = 5;
    $result = array();
    for ($i = $min; $i <= $max; $i++)
    {
        $result[$i] = 0;
    }

    mt_srand(123);
    for ($i = 0; $i < 100; $i++)
    {
        $index = mt_rand($min, $max);
        $result[$index]++;
    }

    print_r($result);
    */
}
