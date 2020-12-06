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
    $t = getModel()->getTwigWrapper();

    // Parse booster set
    $route = getModel()->getPageData()->get('route');
    $uri_slug = '';
    $matches = array();
    if (preg_match('#^/booster/([^/]+)/$#', $route, $matches))
    {
        $uri_slug = $matches[1];
    }

    $setcode = CardSet::uriSlugToSetCode($uri_slug);

    if (strlen($setcode) == 0)
    {
        render404();
        exit();
    }

    // Parse seed
    $seed = isset($_REQUEST['seed']) ? (int)$_REQUEST['seed'] : 0;
    if ($seed > 0)
    {
        setcookie('seed', (string)$seed, time() + 120);
        redirect('/booster/' . $uri_slug . '/');
        exit();
    }

    if (isset($_COOKIE['seed']))
    {
        $seed = (int)$_COOKIE['seed'];
        setcookie('seed', (string)$seed, time() - 3600);
    }

    // Generate booster
    $cardset = new CardSet();
    if (!$cardset->loadFromFile(PROJECT_DB_FOLDER . '/' . $setcode . '.json'))
    {
        render500('Cardset load failed for setcode "' . $setcode . '": ' . $cardset->getLastError()->getText());
        exit();
    }

    $pack_card_array = array();
    $packgenerator = new PackGenerator();
    $packgenerator->setCardSet($cardset);
    if ($seed > 0)
    {
        $packgenerator->setSeed($seed);
    }
    if (!$packgenerator->generate($pack_card_array))
    {
        render500('Booster generation failed for setcode "' . $setcode . '": ' . $packgenerator->getLastError()->getText());
        exit();
    }

    $cardset_json = json_encode($cardset->getMap());
    $t->setVar('cardset_json', $cardset_json);

    $pack_serialized = array();
    foreach ($pack_card_array as $pack_card)
    {
        $pack_serialized[] = $pack_card->getMap();
    }
    $pack_json = json_encode($pack_serialized);
    $t->setVar('pack_json', $pack_json);

    // Render page
    $t->setVar('route', $route);
    $t->setVar('set_name', $cardset->get('name'));
    $t->setVar('set_code', $cardset->get('code'));
    $t->setVar('save_url', URL_PROJECT . '/booster/' . $uri_slug . '/?seed=' . $packgenerator->getSeed());
    
    echo renderTwigLayout1('/booster.html.twig', $cardset->get('name'), array('show_header' => false));
}
