<?php

//
// Script logic
//

renderPage500();
exit();

//
// Global routines
//

function renderPage500()
{
    // NOTE: cannot assume any subsystems are initialized at this time
    error_500();

    $file = PROJECT_TEMPLATE_FOLDER . '/500.html';
    $text = @file_get_contents($file);
    if ($text === false) {
        $text = '';
    }
    echo $text;
}
