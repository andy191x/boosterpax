
function html_encode(str)
{
	return String(str)
		.replace(/&/g, '&amp;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#39;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;');
}

function clamp(val, min, max)
{
	if (min > max)
	{
		var t = min;
		min = max;
		max = t;
	}
	if (val < min)
	{
		val = min;
	}
	if (val > max)
	{
		val = max;
	}
	return val;
}

// get document coordinates of the element
// from: https://javascript.info/coordinates
function getCoords(elem)
{
	let box = elem.getBoundingClientRect();

	return {
		top: box.top + window.pageYOffset,
		left: box.left + window.pageXOffset
	};
}

// returns an interpolation between two inputs (v0, v1) for a parameter (t) in the closed unit interval [0, 1]
function lerp(v0, v1, t)
{
  return (1 - t) * v0 + t * v1;
}

function preloadImage(url, callback)
{
	let img = new Image();
	img.onload = callback;
	img.src = url;
}

function preloadImages(url_array, callback, timeout = 0, timeout_callback = null)
{
	if (url_array.length < 1)
	{
		return;
	}

	let timed_out = false;
	let counter = 0;
	for (let i = 0; i < url_array.length; i++)
	{
		preloadImage(url_array[i], function()
		{
			if (timed_out)
			{
				return;
			}

			counter++;
			//console.log('preloadImages: ' + counter);
			if (counter == url_array.length)
			{
				//console.log('preloadImages: signaling callback');
				callback();
			}
		});
	}

	if (timeout > 0)
	{
		setTimeout(function()
		{
			if (counter < url_array.length)
			{
				timed_out = true;
				if (timeout_callback != null)
				{
					timeout_callback();
				}
			}
		}, timeout);
	}
}

/**
 * Format rarity text for user display.
 * @param {object} card
 * @param {boolean} include_print_rarity
 * @return {string}
 */
function formatRarity(card, include_print_rarity = false)
{
	if (card.rarity_app.length > 0)
	{
		let first_char = card.rarity_app.slice(0, 1);
		if (first_char == 'c')
		{
			return 'Common' + (include_print_rarity ? ' (' + card.rarity_app.toUpperCase() + ')' : '');
		}
		else if (first_char == 'u')
		{
			return 'Uncommon' + (include_print_rarity ? ' (' + card.rarity_app.toUpperCase() + ')' : '');
		}
		else if (first_char == 'r')
		{
			return 'Rare' + (include_print_rarity ? ' (' + card.rarity_app.toUpperCase() + ')' : '');
		}
	}

	let first_char = card.rarity.slice(0, 1);
	return first_char.toUpperCase() + card.rarity.slice(1);
}

/**
 * "a common", "an uncommon"
 * @param {object} card
 * @returns {string}
 */
function formatRaritySentence(card)
{
	let rarity = (formatRarity(card, false)).toLowerCase();
	return (rarity == 'uncommon' ? 'an' : 'a') + ' ' + rarity;
}

function toggleFlex(jq)
{
	let display = jq.eq(0).css('display');
	jq.css('display', (display == 'flex' ? 'none' : 'flex'));
}
