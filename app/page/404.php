<?php

//
// Script logic
//

renderPage404();
exit();

//
// Global routines
//

function renderPage404()
{
    error_404();
    echo renderTwigLayout1('/404.html.twig', 'Page Not Found');
}

