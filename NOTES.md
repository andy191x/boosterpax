
Helpful notes about how this project works.

---

## Pack opening effect

### Clipping

https://css-tricks.com/clipping-masking-css/

	<svg height="0" width="0">
	  <defs>
		<clipPath id="clip1">
		  <polygon points="98.4999978 153.75..."/>
		</clipPath>
	  </defs>
	</svg>
	
	<svg height="0" width="0">
	  <defs>
		<clipPath id="clip1">
		  <rect y="110" x="137" width="90" height="90"/>
		  <rect x="0" y="110" width="90" height="90"/>
		  <rect x="137" y="0" width="90" height="90"/>
		  <rect x="0" y="0" width="90" height="90"/>
		</clipPath>
	  </defs>
	</svg>

	.clip1 {
		clip-path: url(#clip1);
	}

### Layout

* layer 4, pack unmasked, visible
* layer 3, pack top masked, hidden
* layer 2, pack bottom masked, hidden
* layer 1, mtg card bg, hidden

### Algorithm for converting user line

* Create a square of points around the booster perimeter.
* Select the last two points of the user drawn line.
* Perform correction on the points.
* Select the two nearest square points around the perimeter of the user points.
* Perform correction on the chosen points.
** Points must be be on different sides of the bounding square.
** If points are on the same side, advance end point to next side.
* Generate SVG and inverse SVG on chosen points.
* Start separation animation.

---

## Responsive design

This app was designed to work on low resolutions (iPhone 5) and large resolutions (4k desktop). Additionally, it handles portrait, landscape, and square aspect ratios.

### The booster bar

This bar contains the status label, "Draw All" and "Sort" buttons. The text here was carefully chosen to work across most resolutions without clipping.

### During the pack opening phase

There are two modes used for the pack opening animation: "1_card" and "2_card". "2_card" is a wider animation generally chosen on desktop displays. "1_card" is mostly for mobile and chooses a scale for the card images based on the device size.

In order to keep the animation code simple, the animation "stage" and cards are sized once during the page load. If the user resizes their browser while drawing cards, the stage will not resize.

### During the pack viewing phase

Standard responsive CSS with media queries is used in the phase. The visible cards are represented by the DOMCard class from the previous phase, preserving the calculated size from when they were opened.


---

## Adding a set

Example "lea".

* Download scryfall data and cards. Convert them to the local format.

    cd /home/sites/personal-web/boosterpax.com/script/db
	php scryfall_get.php lea > lea_s.json
	php scryfall_convert.php lea_s.json > lea.json
	php scryfall_getimages.php lea_s.json lea large
	
	mv lea.json ../../db/
	mv lea ../../www/cards/
	
	rm -f lea_s.json
	
* Add booster artwork at "www/cards/SETNAME/large/booster.png". There is a PSD in the "art" folder.
* Add booster thumbnail at "www/images/booster-thumbs/SETNAME.png".  There is a PSD in the "art" folder.
* Add logic to generate the packs in "PackGenerator::generate()".
* Add a URI slug for the set in "CardSet::uriSlugCreateMap()".
* Add a packinfo entry in "app/page/index.php".
* Add a colored logo banner in "app/page/booster.html.twig".

---

## Pack seed

A unique pack will be generated with each pageload. Packs can be saved with a seed URL, however the seed URL will not be displayed in the navigation bar, in order to prevent refreshes generating the same page.

For example:

* User loads /booster/alpha/
* User clicks "Save Draw"
* User receives a link with format: /booster/alpha/?seed=12345
* User navigates to /booster/alpha/?seed=12345
* Webserver redirects to /booster/alpha/ and includes the "seed" cookie.
* Webserver checks for presence of the "seed" cookie when rendering /booster/alpha/ , then removes the cookie after applying the saved seed.
