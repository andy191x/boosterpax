<?php

//
// Loads and aggregates the scryfall.com JSON for a given setname.
//

//
// Includes
//

require_once (__DIR__ . '/../../app/include.php');

//
// Script logic
//

exit(main($argv));

//
// Global routines
//

function usage()
{
    return basename(__FILE__) . ' <SETCODE>';
}

function main($argv)
{
    $setcode = isset_or_default($argv, 1, '');

    if (strlen($setcode) < 3)
    {
        echo errorJSON('Invalid SETCODE. Usage: ' . usage());
        return 1;
    }

    // Poll set object from scryfall
    // from: https://scryfall.com/docs/api/sets
    // https://api.scryfall.com/sets/lea
    $url = 'https://api.scryfall.com/sets/' . mb_strtolower($setcode);

    $error = new ErrorType();
    $scryfall_set = array();
    if (!scryfallJSONGET($url, $error, $scryfall_set))
    {
        echo errorJSON('Cannot poll set data: ' . $error->getText());
        return 1;
    }

    $next_page_url = isset_or_default($scryfall_set, 'search_uri', '');
    $next_page_index = 0;
    $more = true;
    if (!preg_match('/^http/', $next_page_url))
    {
        echo errorJSON('Card search URL missing from set data.');
        return 1;
    }

    // Poll card pages from scryfall
    $card_array = array();

    while ($more)
    {
        $error = new ErrorType();
        $scryfall_cardpage = array();
        if (!scryfallJSONGET($next_page_url, $error, $scryfall_cardpage))
        {
            echo errorJSON('Cannot poll page ' . $next_page_index .': ' . $error->getText());
            return 1;
        }

        if (!isset($scryfall_cardpage['has_more']))
        {
            echo errorJSON('Invalid JSON format (1) on page ' . $next_page_index .': ' . $error->getText());
            return 1;
        }

        if (isset($scryfall_cardpage['data']) && is_array($scryfall_cardpage['data']))
        {
            $card_array = array_merge($card_array, $scryfall_cardpage['data']);
        }

        $more = (bool)$scryfall_cardpage['has_more'];
        if ($more)
        {
            $next_page_url = isset_or_default($scryfall_cardpage, 'next_page', '');
            if (!preg_match('/^http/', $next_page_url))
            {
                echo errorJSON('Next card search URL missing on page: ' . $next_page_index);
                return 1;
            }
            $next_page_index++;
        }

        if ($next_page_index == 100)
        {
            echo errorJSON('Too many paging iterations. Is everything alright?');
            return 1;
        }
    }

    // Generate new doc
    $doc = array();
    $doc['set'] = $scryfall_set;
    $doc['card_array'] = $card_array;

    echo json_encode($doc);

    return 0;
}

/**
 * @param string $text
 * @return string
 */
function errorJSON($text)
{
    $data = array();
    $data['error'] = $text;
    return json_encode($data);
}

/**
 * @param string $url
 * @param ErrorType $out_error
 * @param mixed[] $out_data
 * @param int $timeout_ms
 * @return bool
 */
function scryfallJSONGET($url, &$out_error, &$out_data, $timeout_ms = 15000)
{
    $out_error = new ErrorType();
    $out_data = array();

    // Configure curl
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); // v4 only
    curl_setopt($ch, CURLOPT_POST, 0);
    //curl_setopt($ch, CURLOPT_POSTFIELDS, $field_map);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if ($timeout_ms != 0)
    {
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout_ms);
    }

    // Send request
    $cr = curl_exec($ch);
    $curl_data = array();

    if ($cr !== false)
    {
        $curl_data['httpcode'] = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_data['cr'] = $cr;
    }
    else
    {
        $curl_data['errno'] = curl_errno($ch);
        $curl_data['error'] = curl_error($ch);
    }

    // Cleanup
    curl_close($ch);
    $ch = null;

    if ($cr === false)
    {
        $out_error = ErrorType::make(0, 'HTTP call failed.');
        return false;
    }

    // Respect scryfall's rate limits
    usleep(1000 * 150);

    // Parse result
    $result = json_decode($cr, true);

    if ($result === null)
    {
        $out_error = ErrorType::make(0, 'Invalid JSON response.');
        return false;
    }

    $out_data = $result;
    return true;
}
