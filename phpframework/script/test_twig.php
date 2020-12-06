<?php

//
// Includes
//

require_once(__DIR__ . '/../autoload.php');

//
// Script logic
//

exit(main($argv));

//
// Global routines
//

function main($argv)
{
    $twigwrapper = new TwigWrapper();

    $twigwrapper->setCacheFolder('/tmp/twig_test');
    $twigwrapper->setTemplateFolder(__DIR__);
    if (!$twigwrapper->open())
    {
        echo 'Cannot open wrapper: ' . $twigwrapper->formatError() . PHP_EOL;
        return 1;
    }

    $twigwrapper->setRootTemplate('template.html');
    $twigwrapper->setVar('myvar', 'rendered by twig!');
    echo $twigwrapper->render() . PHP_EOL;

    return 0;
}