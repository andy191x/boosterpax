
//
// StageController
// Logic handler for booster opening/viewing "stage".
// Dependencies:
//  global.js utility functions
//  jquery
//  tearbooster
//

//
// Class definition
//

class StageController
{
    //
    // Public routines
    //

    /**
     * @param config - Object with key/value pairs for class configuration.
     *        config.parent - JQuery selector of parent object
     *        config.cardset - Card set
     *        config.card_array - Cards in pack
     *        config.sort - (optional) pack|name|rarity|color|cmc|type|random
     *        config.sort_ascending - (optional) boolean
     *        config.on_draw_remain_changed - (optional) callback when the number of cards remaining changed. syntax: function (stagecontroller, count)
     *        config.on_view_pack - (optional) callback when the "view_pack" state has been shown. syntax: function (stagecontroller)
     *        config.on_view_card_click - (optional) callback when a card is clicked during the "view_pack" state. syntax: function (stagecontroller, card_id)
     *        config.on_view_card_mousemove - (optional) callback when the mouse moves over a card in the "view_pack" state. syntax: function (stagecontroller, card_id)
     *        config.on_view_card_mouseout - (optional) callback when the mouse leaves a card in the "view_pack" state. syntax: function (stagecontroller, card_id)
     */
    constructor(config)
    {
        if (!config.hasOwnProperty('sort'))
        {
            config.sort = 'pack';
        }
        if (!config.hasOwnProperty('sort_ascending'))
        {
            config.sort_ascending = false;
        }
        if (!config.hasOwnProperty('on_draw_remain_changed'))
        {
            config['on_draw_remain_changed'] = null;
        }
        if (!config.hasOwnProperty('on_view_pack'))
        {
            config['on_view_pack'] = null;
        }
        if (!config.hasOwnProperty('on_view_card_click'))
        {
            config['on_view_card_click'] = null;
        }
        if (!config.hasOwnProperty('on_view_card_mousemove'))
        {
            config['on_view_card_mousemove'] = null;
        }
        if (!config.hasOwnProperty('on_view_card_mouseout'))
        {
            config['on_view_card_mouseout'] = null;
        }

        this.state = 'init'; // init|preload|open_pack|draw_pack|view_pack
        this.config = config;
        this.animation = {}; // generic animation data

        this.card_pack_image = '';
        this.card_back_image = '';

        this.card_size_px = [ 672, 936 ];  // @1.0: w, h
        this.pack_size_px = [ 800, 1500 ]; // @1.0: w, h
        this.pack_pad_px = [ 200, 16 ];    // @1.0: w, h
        this.card_offset_pack_px = [ 64, 282 ]; // @1.0: w, h
        this.scale = 1.0; // rendering scale
        this.stage_width_px = this.config.parent.width();
        this.mode = '2_card'; // 2_card|1_card
        this.drawall = false;

        this.preload_state = 'init'; // init|error|done
        this.preload_ticks = 1;

        this.open_pack_state = 'init'; // init|wait_tear|move_cards|done
        this.tearbooster = null;

        this.draw_pack_state = 'init'; // init|wait_click|flipping|wait_final_click
        this.draw_index = 0;
        this.flip_lastindex = -1;

        this.view_pack_state = 'init'; // init|view
        this.view_isotope = null;
        this.view_domcard_array = [];

        this.stagecard1 = null;
        this.stagecard2 = null;
        this.stagecard3 = null;

        // Calculate responsive size/scale. If the user resizes their browser while drawing cards, then the page will need to be refreshed.
        // Choose best fit based on aspect ratio.
        let vw = window.innerWidth;
        let vh = window.innerHeight;
        let aspect = (vw / vh);

        if (aspect > 0.88)
        {
            // landscape: use largest 2-card arrangement
            this.mode = '2_card';

            let reserved_width = (this.card_size_px[0] * 2.5);
            let reserved_height = (this.pack_size_px[1] + 120);

            let scale_array = [ 1.00, 0.75, 0.66, 0.50, 0.33, 0.25 ];

            for (let i = 0; i < scale_array.length; i++)
            {
                this.scale = scale_array[i];

                let sw = (reserved_width * this.scale);
                let sh = (reserved_height * this.scale);

                if (vw >= sw && vh >= sh)
                {
                    break;
                }
            }
        }
        else
        {
            // portrait: use largest 1-card arrangement
            this.mode = '1_card';

            let reserved_width = (this.pack_size_px[0] + (this.pack_pad_px[0] * 2));
            this.scale = clamp(this.stage_width_px / reserved_width, 0.25, 1.00);
        }

        //console.log('mode: ' + this.mode);
        //console.log('scale: ' + this.scale);

        let min_height = Math.max(vh, this.card_size_px[1] * 2.5 * this.scale);
        this.config.parent.css({ 'min-height': min_height + 'px' });
    }

    start()
    {
        let self = this;
        this._onPreload();
    }

    getState()
    {
        return this.state;
    }

    drawAll()
    {
        let self = this;
        self.drawall = true;
        
        if (self.state == 'open_pack')
        {
            if (this.tearbooster != null)
            {
                this.tearbooster.tear();
            }
        }
        else if (self.state == 'draw_pack')
        {
            if (self.draw_pack_state == 'wait_click')
            {
                if (self.drawall && self.draw_index < (self.config.card_array.length - 1))
                {
                    self.draw_index = (self.config.card_array.length - 1);
                }
            }

            self._onStageCard1_Click();
        }
    }

    sort(new_sort, ascending = true)
    {
        let self = this;
        let valid_sort = [ 'pack', 'name', 'rarity', 'color', 'cmc', 'type', 'random' ];

        if (valid_sort.indexOf(new_sort) != -1)
        {
            self.config.sort = new_sort;
            self.config.sort_ascending = ascending;

            if (self.state == 'view_pack' && self.view_pack_state == 'view')
            {
                if (self.config.sort == 'random')
                {
                    self.view_isotope.isotope('shuffle');
                }
                else
                {
                    self.view_isotope.isotope({
                        sortBy: new_sort,
                        sortAscending: ascending
                    });
                }
            }
        }
    }

    getSort()
    {
        return this.config.sort;
    }

    getSortAscending()
    {
        return this.config.sort_ascending;
    }

    //
    // Private event handlers
    //

    _onPreload()
    {
        let self = this;

        if (self.state != 'init')
        {
            return;
        }

        self.state = 'preload';
        self.preload_state = 'init';

        // Show widget
        let preload_init = self.config.parent.find('.preload_init');
        preload_init.show();

        let preload_text = self.config.parent.find('[data-name="preload_text"]');
        let preload_tick = setInterval(function()
        {
            if (self.state == 'preload' && self.preload_state == 'init')
            {
                let text = 'Loading';
                for (let i = 1; i <= self.preload_ticks; i++)
                {
                    text += '.';
                }

                self.preload_ticks++;
                if (self.preload_ticks > 3)
                {
                    self.preload_ticks = 1;
                }

                preload_text.text(text);
            }
        }, 500);

        // Load images
        self.card_pack_image = '/cards/' + self.config.cardset.code + '/large/booster.png';
        self.card_back_image = '/cards/card_back_solid.jpg';

        let image_array = [];
        image_array.push(self.card_pack_image);
        image_array.push('/cards/card_back_null.png');
        image_array.push(self.card_back_image);
        for (let i = 0; i < self.config.card_array.length; i++)
        {
            let card = self.config.card_array[i];
            image_array.push('/cards/' + self.config.cardset.code + '/large/' + card.id + '.jpg');
        }

        preloadImages(
            image_array,
            function()
            {
                clearInterval(preload_tick);
                self._onPreloadComplete();
            },
            1000 * 120,
            function()
            {
                clearInterval(preload_tick);
                self._onPreloadTimeout();
            }
        );
    }

    _onPreloadComplete()
    {
        let self = this;

        let valid_state = (self.state == 'preload' && self.preload_state == 'init');
        if (!valid_state)
        {
            return;
        }

        let preload_init = self.config.parent.find('.preload_init');
        preload_init.hide();

        self.stagecard1 = self._createDOMCardBySetBG(self.config.parent);
        self.stagecard2 = self._createDOMCardBySetBG(self.config.parent);
        self.stagecard3 = self._createDOMCardBySetBG(self.config.parent);

        let tb_config = {};
        tb_config['id'] = 'tb1';
        tb_config['parent'] = self.config.parent;
        tb_config['pack_scale'] = self.scale;
        tb_config['pack_width'] = self.pack_size_px[0];
        tb_config['pack_height'] = self.pack_size_px[1];
        tb_config['pack_image'] = self.card_pack_image;
        tb_config['xpad'] = (self.pack_pad_px[0] * self.scale);
        tb_config['ypad'] = (self.pack_pad_px[1] * self.scale);
        tb_config['zindex'] = 10;
        tb_config['margin_left'] = (self.stage_width_px - ((self.scale * tb_config['pack_width']) + (tb_config['xpad'] * 2))) / 2;
        tb_config['on_tear_start'] = function(tearbooster)
        {
            self._onTearStart();
        };
        tb_config['on_tear_complete'] = function(tearbooster)
        {
            self._onTearComplete();
        };

        self.stagecard1.click(function(e)
        {
            self._onStageCard1_Click();
            return false;
        });
        self.stagecard1.mousedown(function()
        {
            return false;
        });
        self.stagecard2.click(function(e)
        {
            self._onStageCard2_Click();
            return false;
        });
        self.stagecard2.mousedown(function()
        {
            return false;
        });
        self.stagecard3.click(function()
        {
            self._onStageCard3_Click();
            return false;
        });
        self.stagecard3.mousedown(function()
        {
            return false;
        });

        self.preload_state = 'done';
        self.state = 'open_pack';
        self.open_pack_state = 'wait_tear';
        self.tearbooster = new Tearbooster(tb_config);
    }

    _onPreloadTimeout()
    {
        let self = this;

        let valid_state = (self.state == 'preload' && self.preload_state == 'init');
        if (!valid_state)
        {
            return;
        }

        let preload_init = self.config.parent.find('.preload_init');
        preload_init.hide();

        let preload_error = self.config.parent.find('.preload_error');
        preload_error.show();

        self.preload_state = 'error';
    }

    _onTearStart()
    {
        let self = this;
        
        if (self.state != 'open_pack' || self.open_pack_state != 'wait_tear')
        {
            return;
        }

        let coords = self.tearbooster.getBoosterParentCoords();
        coords[0] += Math.round(self.card_offset_pack_px[0] * self.scale);
        coords[1] += Math.round(self.card_offset_pack_px[1] * self.scale);

        self.stagecard1.setPos(coords[0], coords[1]);
        self.stagecard1.show();
    }

    _onTearComplete()
    {
        let self = this;

        if (self.state != 'open_pack' || self.open_pack_state != 'wait_tear')
        {
            return;
        }

        let coords = self.tearbooster.getBoosterParentCoords();
        coords[0] += Math.round(self.card_offset_pack_px[0] * self.scale);
        coords[1] += Math.round(self.card_offset_pack_px[1] * self.scale);

        self.tearbooster = null;
        self.open_pack_state = 'move_cards';

        if (self.mode == '2_card')
        {
            let cards_x = (self.stage_width_px / (self.card_size_px[0] * self.scale));
            let card1_x = 0;
            let card2_x = 0;
            let spacer_y = (self.card_size_px[0] * self.scale) * 0.10;

            //console.log('cards_x: ' + cards_x);

            if (cards_x >= 3.5)
            {
                // center drawn card on wider views
                let spacer_x = (self.card_size_px[0] * self.scale) * 0.10;
                card1_x = (self.stage_width_px / 2) - ((self.card_size_px[0] / 2) * self.scale) - (self.card_size_px[0] * self.scale) - spacer_x;
                card2_x = (self.stage_width_px / 2) - ((self.card_size_px[0] / 2) * self.scale);
            }
            else
            {
                // center both piles
                let spacer_x = (self.card_size_px[0] * self.scale) * 0.05;
                card1_x = (self.stage_width_px / 2) - ((self.card_size_px[0] * self.scale) + spacer_x);
                card2_x = (self.stage_width_px / 2) + spacer_x;
            }

            self.animation = {
                start_x: coords[0],
                start_y: coords[1],
                end_x: card1_x,
                end_y: spacer_y,
                card1_x: card1_x,
                card2_x: card2_x,
            };

            anime({
                targets: self.stagecard1.getElement(),
                left: self.animation.end_x,
                top: self.animation.end_y,
                easing: 'easeInOutQuad',
                duration: 1000,
                //begin: function (anim) {},
                //update: function (anim) {},
                complete: function (anim)
                {
                    if (self.state != 'open_pack' || self.open_pack_state != 'move_cards')
                    {
                        return;
                    }
                    self.stagecard1.setPos(self.animation.end_x, self.animation.end_y);
                    self._onMoveCardsComplete();
                }
            });
        }
        else
        {
            // 1_card
            let spacer = (self.card_size_px[0] * self.scale) * 0.05;
            self.animation = {
                start_x: coords[0],
                start_y: coords[1],
                end_x: spacer,
                end_y: spacer
            };

            anime({
                targets: self.stagecard1.getElement(),
                left: self.animation.end_x,
                top: self.animation.end_y,
                easing: 'easeInOutQuad',
                duration: 1000,
                //begin: function (anim) {},
                //update: function (anim) {},
                complete: function (anim)
                {
                    if (self.state != 'open_pack' || self.open_pack_state != 'move_cards')
                    {
                        return;
                    }
                    self.stagecard1.setPos(self.animation.end_x, self.animation.end_y);
                    self._onMoveCardsComplete();
                }
            });
        }
    }

    _onMoveCardsComplete()
    {
        let self = this;

        if (self.state != 'open_pack' || self.open_pack_state != 'move_cards')
        {
            return;
        }

        self.open_pack_state = 'done';
        self.state = 'draw_pack';
        self.draw_pack_state = 'wait_click';
        if (self.drawall && self.draw_index < (self.config.card_array.length - 1))
        {
            self.draw_index = (self.config.card_array.length - 1);
        }

        self.stagecard1.setClassArray(['clickable']);
        self.stagecard2.setClassArray(['clickable']);
        self.stagecard3.setClassArray(['clickable']);

        if (!self.drawall)
        {
            if (self.config['on_draw_remain_changed'] !== null)
            {
                self.config['on_draw_remain_changed'](self, (self.config.card_array.length - self.draw_index));
            }
        }

        if (self.drawall)
        {
            setTimeout(function()
            {
                self._onStageCard1_Click();
            }, 0);
        }
    }

    _onStageCard1_Click()
    {
        let self = this;

        if (self.state == 'draw_pack' && self.draw_pack_state == 'wait_click')
        {
            self.draw_pack_state = 'flipping';

            self.stagecard1.setClassArray([]);
            self.stagecard2.setClassArray([]);
            self.stagecard3.setClassArray([]);

            let sc1_pos = self.stagecard1.getPos();
            let speed = 1.0; //self.drawall ? 4.0 : 1.0;

            if (self.mode == '2_card')
            {
                let card2_x = self.animation.card2_x;
                self.animation = {
                    start_x: sc1_pos[0],
                    start_y: sc1_pos[1],
                    end_x:  card2_x,
                    end_y: sc1_pos[1],
                    card2_x: card2_x
                };

                if (self.draw_index == (self.config.card_array.length - 1))
                {
                    self.stagecard1.hide();
                }

                if (self.flip_lastindex > -1)
                {
                    let card = self.config.card_array[self.flip_lastindex];
                    self.stagecard3.setImage('/cards/' + self.config.cardset.code + '/large/' + card.id + '.jpg');
                    self.stagecard3.setPos(self.animation.end_x, self.animation.end_y);
                    self.stagecard3.show();
                }
                self.flip_lastindex = self.draw_index;

                self.stagecard2.setImage(self.card_back_image);
                self.stagecard2.setPos(self.animation.start_x, self.animation.start_y);
                self.stagecard2.setRot(0.0, 0.0, 0.0);
                self.stagecard2.setZIndex(1);
                self.stagecard2.show();

                let t1 = anime.timeline({
                    loop: false,
                    autoplay: false,
                    easing: 'linear',
                });
                t1.add({
                    targets: self.stagecard2.getElement(),
                    duration: 333 / speed,
                    //easing: 'easeInOutSine',
                    left: lerp(self.animation.start_x, self.animation.end_x, 0.333),
                    top: lerp(self.animation.start_y, self.animation.end_y, 0.333),
                    rotateY: 0.0,
                });
                t1.add({
                    targets: self.stagecard2.getElement(),
                    duration: 333 / speed,
                    //easing: 'easeInOutQuad',
                    left: lerp(self.animation.start_x, self.animation.end_x, 0.666),
                    top: lerp(self.animation.start_y, self.animation.end_y, 0.666),
                    rotateY: 90.0,
                    complete: function (anim) {
                        let card = self.config.card_array[self.draw_index];
                        self.stagecard2.setImage('/cards/' + self.config.cardset.code + '/large/' + card.id + '.jpg');
                    }
                });
                t1.add({
                    targets: self.stagecard2.getElement(),
                    duration: 750 / speed,
                    easing: 'easeOutSine',
                    left: self.animation.end_x,
                    top: self.animation.end_y,
                    rotateY: 0.0,
                    complete: function (anim) {
                        if (self.state != 'draw_pack' || self.draw_pack_state != 'flipping')
                        {
                            return;
                        }

                        self.stagecard2.setPos(self.animation.end_x, self.animation.end_y);
                        self.stagecard3.hide();

                        self.stagecard1.setClassArray(['clickable']);
                        self.stagecard2.setClassArray(['clickable']);
                        self.stagecard3.setClassArray(['clickable']);

                        self._onFlippingAnimationComplete();
                    }
                });
                t1.play();
            }
            else
            {
                // 1_card
                let spacer = sc1_pos[1];

                self.animation = {
                    start_x: sc1_pos[0],
                    start_y: sc1_pos[1],
                    end_1_x: sc1_pos[0] - (self.card_size_px[0] * self.scale),
                    end_1_y: sc1_pos[1] + spacer,
                    end_2_x: (self.stage_width_px / 2) - ((self.card_size_px[0] / 2) * self.scale),
                    end_2_y: sc1_pos[1] + (spacer * 2),
                };

                if (self.draw_index == (self.config.card_array.length - 1))
                {
                    self.stagecard1.hide();
                }

                if (self.flip_lastindex > -1)
                {
                    let card = self.config.card_array[self.flip_lastindex];
                    self.stagecard3.setImage('/cards/' + self.config.cardset.code + '/large/' + card.id + '.jpg');
                    self.stagecard3.setPos(self.animation.end_2_x, self.animation.end_2_y);
                    self.stagecard3.show();
                }
                self.flip_lastindex = self.draw_index;

                self.stagecard2.setImage(self.card_back_image);
                self.stagecard2.setPos(self.animation.start_x, self.animation.start_y);
                self.stagecard2.setRot(0.0, 0.0, 0.0);
                self.stagecard2.setZIndex(0);
                self.stagecard2.show();

                let t1 = anime.timeline({
                    loop: false,
                    autoplay: false,
                    easing: 'linear',
                });
                t1.add({
                    targets: self.stagecard2.getElement(),
                    duration: 500 / speed,
                    //easing: 'easeInOutSine',
                    left: self.animation.end_1_x,
                    top: self.animation.end_1_y,
                    rotateY: 0.0,
                    complete: function (anim) {
                        if (self.state != 'draw_pack' || self.draw_pack_state != 'flipping')
                        {
                            return;
                        }

                        self.stagecard2.setZIndex(1);
                    }
                });
                t1.add({
                    targets: self.stagecard2.getElement(),
                    duration: 333 / speed,
                    //easing: 'easeInOutSine',
                    left: lerp(self.animation.end_1_x, self.animation.end_2_x, 0.333),
                    top: lerp(self.animation.end_1_y, self.animation.end_2_y, 0.333),
                    rotateY: 0.0,
                });
                t1.add({
                    targets: self.stagecard2.getElement(),
                    duration: 333 / speed,
                    //easing: 'easeInOutQuad',
                    left: lerp(self.animation.end_1_x, self.animation.end_2_x, 0.666),
                    top: lerp(self.animation.end_1_y, self.animation.end_2_y, 0.666),
                    rotateY: 90.0,
                    complete: function (anim) {
                        let card = self.config.card_array[self.draw_index];
                        self.stagecard2.setImage('/cards/' + self.config.cardset.code + '/large/' + card.id + '.jpg');
                    }
                });
                t1.add({
                    targets: self.stagecard2.getElement(),
                    duration: 750 / speed,
                    easing: 'easeOutSine',
                    left: self.animation.end_2_x,
                    top: self.animation.end_2_y,
                    rotateY: 0.0,
                    complete: function (anim) {
                        if (self.state != 'draw_pack' || self.draw_pack_state != 'flipping')
                        {
                            return;
                        }

                        self.stagecard2.setPos(self.animation.end_2_x, self.animation.end_2_y);
                        self.stagecard3.hide();

                        self.stagecard1.setClassArray(['clickable']);
                        self.stagecard2.setClassArray(['clickable']);
                        self.stagecard3.setClassArray(['clickable']);

                        self._onFlippingAnimationComplete();
                    }
                });
                t1.play();
            }
        }
    }

    _onFlippingAnimationComplete()
    {
        let self = this;

        if (self.state == 'draw_pack' && self.draw_pack_state == 'flipping')
        {
            self.draw_pack_state = 'wait_click';
            self.draw_index++;

            if (!self.drawall)
            {
                if (self.config['on_draw_remain_changed'] !== null)
                {
                    self.config['on_draw_remain_changed'](self, (self.config.card_array.length - self.draw_index));
                }
            }

            if (self.drawall && self.draw_index < (self.config.card_array.length - 1))
            {
                self.draw_index = (self.config.card_array.length - 1);
            }

            if (self.draw_index == self.config.card_array.length)
            {
                self.draw_pack_state = 'wait_final_click';

                setTimeout(function()
                {
                    self._onStageCard2_Click();
                }, 1500);
            }

            if (self.drawall)
            {
                setTimeout(function()
                {
                    self._onStageCard1_Click();
                }, 0);
            }
        }
    }

    _onStageCard2_Click()
    {
        let self = this;

        if (self.state == 'draw_pack' && self.draw_pack_state == 'wait_click')
        {
            self._onStageCard1_Click();
        }
        else if (self.state == 'draw_pack' && self.draw_pack_state == 'wait_final_click')
        {
            self.state = 'view_pack';

            // Setup stage
            self.stagecard1.remove();
            self.stagecard1 = self._createDOMCardByCard(self.config.parent, self.config.card_array[0], 0, false);
            self.stagecard1.setZIndex(1);
            self.stagecard1.click(function()
            {
                return false;
            });
            self.stagecard1.mousedown(function()
            {
                return false;
            });

            // Setup isotope viewer
            let isotope_container = self.config.parent.find('.isotope');

            for (let i = 0; i < self.config.card_array.length; i++)
            {
                let card = self.config.card_array[i];

                let domcard = self._createDOMCardByCard(isotope_container, card, i);
                domcard.click(function()
                {
                    self._onIsotopeCardClick(domcard);
                    return false;
                });
                domcard.mousedown(function()
                {
                    return false;
                });
                domcard.mousemove(function()
                {
                    if (self.config['on_view_card_mousemove'] !== null)
                    {
                        self.config['on_view_card_mousemove'](self, domcard.getId());
                    }
                });
                domcard.mouseout(function()
                {
                    if (self.config['on_view_card_mouseout'] !== null)
                    {
                        self.config['on_view_card_mouseout'](self, domcard.getId());
                    }
                });

                self.view_domcard_array.push(domcard);
            }

            self.view_isotope = isotope_container.isotope({
                // options
                itemSelector: '.domcard',
                layoutMode: 'masonry',
                masonry: {
                    fitWidth: true,
                },
                getSortData: {
                    'pack': '[data-pack] parseInt',
                    name: '[data-name]',
                    rarity: '[data-rarity_sort] parseInt',
                    color: '[data-colors_sort] parseInt',
                    cmc: '[data-cmc] parseFloat',
                    type: '[data-type]'
                },
                sortBy: ((self.config.sort == 'random') ? 'pack' : self.config.sort),
                sortAscending: self.config.sort_ascending
            });

            self.view_isotope.on('arrangeComplete', function()
            {
                for (let i = 0; i < self.view_domcard_array.length; i++)
                {
                    self.view_domcard_array[i].setZIndex(0);
                }
            });

            // Notify user
            if (self.config['on_view_pack'] !== null)
            {
                self.config['on_view_pack'](self);
            }

            // Start arrange animation
            let sc2_pos = self.stagecard2.getPos();
            for (let i = 0; i < self.view_domcard_array.length; i++)
            {
                if (i == (self.view_domcard_array.length - 1))
                {
                    self.view_domcard_array[i].setZIndex(1);
                }
                else
                {
                    self.view_domcard_array[i].setZIndex(0);
                }
                self.view_domcard_array[i].setPos(sc2_pos[0], sc2_pos[1]);
                self.view_domcard_array[i].show();
            }

            isotope_container.show();
            self.stagecard2.hide();
            self.view_pack_state = 'view';

            setTimeout(function()
            {
                self.view_isotope.isotope('arrange');
            }, 300);
        }
    }

    _onStageCard3_Click()
    {
        let self = this;

        // ...
    }

    _onIsotopeCardClick(domcard)
    {
        let self = this;

        let valid_state = (self.state == 'view_pack' && self.view_pack_state == 'view');
        if (!valid_state)
        {
            return;
        }

        let domcard_config = domcard.getConfig();
        let domcard_pos = domcard.getElementJQ().position();
        let isotope_pos = getCoords(self.config.parent.find('.isotope').get(0));

        self.stagecard1.setImage(domcard_config.image);
        self.stagecard1.setPos(isotope_pos.left + domcard_pos.left, domcard_pos.top);
        self.stagecard1.show();

        self.stagecard1.animate('zoom', function()
        {
            if (self.state == 'view_pack' && self.view_pack_state == 'view')
            {
                self.stagecard1.hide();
            }
        });

        if (self.config['on_view_card_click'] !== null)
        {
            self.config['on_view_card_click'](self, domcard.getId());
        }
    }

    //
    // Private utility routines
    //

    /**
     * @param {object} parent
     * @returns {DOMCard}
     * @private
     */
    _createDOMCardBySetBG(parent)
    {
        let config = {};
        config['id'] = '';
        config['parent'] = parent;
        config['image'] = '/cards/card_back_solid.jpg';
        config['image_width'] = this.card_size_px[0];
        config['image_height'] = this.card_size_px[1];
        config['x'] = 0;
        config['y'] = 0;
        config['scale'] = this.scale;
        config['border'] = (this.config.cardset.code == 'lea') ? 'alpha' : 'beta';
        config['class_array'] = [];
        config['data_map'] = {};
        config['hide'] = true;

        return new DOMCard(config);
    }

    /**
    *  @param {object} parent
     * @param {object} card
     * @param {number} pack
     * @param {boolean} clickable
     * @returns {DOMCard}
     * @private
     */
    _createDOMCardByCard(parent, card, pack, clickable = true)
    {
        let config = {};
        config['id'] = card.id;
        config['parent'] = parent;
        config['image'] = '/cards/' + this.config.cardset.code + '/large/' + card.id + '.jpg';
        config['image_width'] = this.card_size_px[0];
        config['image_height'] = this.card_size_px[1];
        config['x'] = 0;
        config['y'] = 0;
        config['scale'] = this.scale;
        config['border'] = (this.config.cardset.code == 'lea') ? 'alpha' : 'beta';
        config['class_array'] = [];
        if (clickable)
        {
            config['class_array'].push('clickable');
        }

        config['data_map'] = {
            pack: pack,
            name: card.name,
            colors: card.colors,
            colors_sort: this._cardGetSortColor(card),
            rarity: card.rarity,
            rarity_sort: this._cardGetSortRarity(card),
            cmc: card.cmc,
            type: card.type
        }
        config['hide'] = true;

        return new DOMCard(config);
    }

    _cardGetSortRarity(card)
    {
        let obj = {
            'rare': 0,
            'uncommon': 1,
            'common': 2,
        }

        if (obj.hasOwnProperty(card.rarity))
        {
            return obj[card.rarity];
        }

        return Object.keys(obj).length;
    }

    _cardGetSortColor(card)
    {
        let obj = {
            'B': 0,
            'U': 1,
            'G': 2,
            'R': 3,
            'W': 4,
        }

        if (obj.hasOwnProperty(card.colors))
        {
            return obj[card.colors];
        }

        if (card.colors.indexOf(',') != -1)
        {
            // multi-color
            return 5;
        }

        let type_lower = card.type.toLowerCase();

        if (type_lower.indexOf('artifact') != -1)
        {
            // artifact
            return 6;
        }

        if (type_lower.indexOf('land') != -1 && type_lower.indexOf('basic') == -1)
        {
            // nonbasic land
            return 7;
        }

        if (type_lower.indexOf('basic land') != -1)
        {
            // basic land
            return 8;
        }

        return 9;
    }

    // ...
}

//
// Static class members
//

// ...
