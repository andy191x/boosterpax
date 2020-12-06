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

    // Add packs
    $packinfo_array = array();

    $packinfo = array();
    $packinfo['type'] = 'booster';
    $packinfo['name'] = 'Alpha';
    $packinfo['setcode'] = 'lea';
    $packinfo['slug'] = CardSet::uriSlugFromSetCode($packinfo['setcode']);
    $packinfo['subtext'] = '295 cards, August 1993.';
    $packinfo_array[] = $packinfo;

    $packinfo['type'] = 'booster';
    $packinfo['name'] = 'Beta';
    $packinfo['setcode'] = 'leb';
    $packinfo['slug'] = CardSet::uriSlugFromSetCode($packinfo['setcode']);
    $packinfo['subtext'] = '302 cards, October 1993.';
    $packinfo_array[] = $packinfo;

    $packinfo['type'] = 'booster';
    $packinfo['name'] = 'Unlimited';
    $packinfo['setcode'] = '2ed';
    $packinfo['slug'] = CardSet::uriSlugFromSetCode($packinfo['setcode']);
    $packinfo['subtext'] = '302 cards, December 1993.';
    $packinfo_array[] = $packinfo;

    $packinfo['type'] = 'booster';
    $packinfo['name'] = 'Arabian Nights';
    $packinfo['setcode'] = 'arn';
    $packinfo['slug'] = CardSet::uriSlugFromSetCode($packinfo['setcode']);
    $packinfo['subtext'] = '78 cards, December 1993.';
    $packinfo_array[] = $packinfo;

    $packinfo['type'] = 'booster';
    $packinfo['name'] = 'Antiquities';
    $packinfo['setcode'] = 'atq';
    $packinfo['slug'] = CardSet::uriSlugFromSetCode($packinfo['setcode']);
    $packinfo['subtext'] = '100 cards, March 1994.';
    $packinfo_array[] = $packinfo;

    $packinfo['type'] = 'booster';
    $packinfo['name'] = 'Legends';
    $packinfo['setcode'] = 'leg';
    $packinfo['slug'] = CardSet::uriSlugFromSetCode($packinfo['setcode']);
    $packinfo['subtext'] = '310 cards, June 1994.';
    $packinfo_array[] = $packinfo;

    $packinfo['type'] = 'booster';
    $packinfo['name'] = 'Revised';
    $packinfo['setcode'] = '3ed';
    $packinfo['slug'] = CardSet::uriSlugFromSetCode($packinfo['setcode']);
    $packinfo['subtext'] = '306 cards, June 1994.';
    $packinfo_array[] = $packinfo;

    $packinfo['type'] = 'booster';
    $packinfo['name'] = 'The Dark';
    $packinfo['setcode'] = 'drk';
    $packinfo['slug'] = CardSet::uriSlugFromSetCode($packinfo['setcode']);
    $packinfo['subtext'] = '119 cards, August 1994.';
    $packinfo_array[] = $packinfo;

    $packinfo['type'] = 'spacer';
    $packinfo['subtext'] = '';
    $packinfo_array[] = $packinfo;

    $packinfo_row_array = array();
    $row_item_count = 4;
    $col = 0;

    foreach ($packinfo_array as $packinfo)
    {
        if ($col == 0)
        {
            $packinfo_row_array[] = array();
        }

        $row = count($packinfo_row_array) - 1;
        $packinfo_row_array[$row][] = $packinfo;

        $col++;
        if ($col == $row_item_count)
        {
            $col = 0;
        }
    }

    $t->setVar('packinfo_row_array', $packinfo_row_array);

    // Render page
    echo renderTwigLayout1('/index.html.twig', 'Realistic MTG Booster Experience');
}
