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

    // Render page
    echo renderTwigLayout1('/about.html.twig', 'About BoosterPax');
}
