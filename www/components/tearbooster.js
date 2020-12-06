
//
// Tearbooster
// Animated booster tearing effect class.
// Dependencies: global.js utility functions, jquery, anime.js
//

//
// Class definition
//

class Tearbooster
{
    //
    // Public routines
    //

    /**
     * @param config - Object with key/value pairs for class configuration.
     *        config.id - custom user identifier
     *        config.parent - JQuery selector of parent object
     *        config.pack_scale - scale for rendering the pack
     *        config.pack_width - width (in pixels) of booster pack source image
     *        config.pack_height - height (in pixels) of booster pack source image
     *        config.pack_image - booster pack image URL
     *        config.xpad - width (in pixels) of x-axis padding
     *        config.ypad - height (in pixels) of y-axis padding
     *        config.margin_left - (optional) width (in pixels) of left margin
     *        config.margin_top - (optional) height (in pixels) of top margin
     *        config.zindex - (optional) base z-index
     *        config.fps - (optional) drawing fps
     *        config.on_tear_start - (optional) callback when tearing animation starts. syntax: function (tearbooster)
     *        config.on_tear_complete - (optional) callback when tearing animation completes. syntax: function (tearbooster)
     */
    constructor(config)
    {
        let self = this;

        if (!config.hasOwnProperty('margin_left'))
        {
            config['margin_left'] = 0;
        }
        if (!config.hasOwnProperty('margin_top'))
        {
            config['margin_top'] = 0;
        }
        if (!config.hasOwnProperty('zindex'))
        {
            config['zindex'] = 1;
        }
        if (!config.hasOwnProperty('fps'))
        {
            config['fps'] = 60;
        }
        if (!config.hasOwnProperty('on_tear_start'))
        {
            config['on_tear_start'] = null;
        }
        if (!config.hasOwnProperty('on_tear_complete'))
        {
            config['on_tear_complete'] = null;
        }

        this.state = 'init'; // init|ready|tearing|splitting|done
        this.config = config;
        this.parent = this.config.parent;
        this.root = null;
        this.canvas = null;
        this.canvas_js = null;
        this.ctx = null;
        this.instance_id = 'tearbooster_' + Tearbooster.instance_id;
        Tearbooster.instance_id++;

        this.pack_width_scale = Math.round(this.config.pack_width * this.config.pack_scale);
        this.pack_height_scale = Math.round(this.config.pack_height * this.config.pack_scale);

        this.point_array = []; // mouse coordinates
        this.last_draw = 0;

        this.idle_anime = null;
        this.idle_timer = null;

        // Add component to DOM
        this._injectCommonHTML();
        this._injectInstHTML();

        this.root = $('#' + this.instance_id);
        this.canvas = this.root.find('canvas').eq(0);
        this.canvas_js = this.canvas.get(0);

        this.canvas_js.width = this.pack_width_scale + (this.config.xpad * 2);
        this.canvas_js.height = this.pack_height_scale + (this.config.ypad * 2);

        this.ctx = this.canvas_js.getContext('2d');

        this.img_pack = this.root.find('[data-id="img_pack"]').get(0);
        this.state = 'ready';

        this.booster_doc_coords = [0, 0];

        this._drawTear(true);

        // Setup event handlers
        self.canvas.mousedown(function(event)
        {
            self._onMoveStart(event.offsetX, event.offsetY);
            return false;
        });

        self.canvas.mouseup(function(event)
        {
            self._onMoveEnd(true, event.offsetX, event.offsetY);
            return false;
        });

        self.canvas.mouseout(function(event)
        {
            self._onMoveEnd(false, 0, 0);
            return false;
        });

        self.canvas.mousemove(function(event)
        {
            self._onMoveUpdate(event.offsetX, event.offsetY);
            return false;
        });

        self.canvas_js.addEventListener('touchstart', function(event)
        {
            event.preventDefault();
            if (event.changedTouches.length > 0)
            {            
                let touch = event.changedTouches[0];
                let pos = self.root.offset();
                let offsetX = touch.pageX - pos.left;
                let offsetY = touch.pageY - pos.top;

                //console.log('touchstart: ' + offsetX + ', ' + offsetY);
                self._onMoveStart(offsetX, offsetY);
            }
        });

        self.canvas_js.addEventListener('touchmove', function(event)
        {
            event.preventDefault();
            if (event.changedTouches.length > 0)
            {
                let touch = event.changedTouches[0];
                let pos = self.root.offset();
                let offsetX = touch.pageX - pos.left;
                let offsetY = touch.pageY - pos.top;
                
                //console.log('touchmove: ' + offsetX + ', ' + offsetY);
                self._onMoveUpdate(offsetX, offsetY);
            }
        });

        self.canvas_js.addEventListener('touchend', function(event)
        {
            event.preventDefault();
            if (event.changedTouches.length > 0)
            {
                let touch = event.changedTouches[0];
                let pos = self.root.offset();
                let offsetX = touch.pageX - pos.left;
                let offsetY = touch.pageY - pos.top;
                
                //console.log('touchend: ' + offsetX + ', ' + offsetY);
                self._onMoveEnd(true, offsetX, offsetY);
            }
            else
            {
                //console.log('touchend: ');
                self._onMoveEnd(false, 0, 0);
            }
        });

        self.canvas_js.addEventListener('touchcancel', function(event)
        {
            event.preventDefault();
            //console.log('touchcancel: ');
            self._onMoveEnd(false, 0, 0);
        });

        let idleFunc = function()
        {
            if (self.state != 'ready')
            {
                return;
            }

            // chrome slow-data issue
            self.img_pack = self.root.find('[data-id="img_pack"]').get(0);

            if (self.idle_anime == null)
            {
                self.idle_anime = anime.timeline({
                    loop: false,
                    autoplay: false,
                });
                self.idle_anime.add({
                    targets: self.canvas_js,
                    rotateZ: -5,
                    easing: 'easeInSine',
                    duration: 300
                });
                self.idle_anime.add({
                    targets: self.canvas_js,
                    rotateZ: 5,
                    easing: 'easeInOutSine',
                    duration: 400
                });
                self.idle_anime.add({
                    targets: self.canvas_js,
                    rotateZ: 0,
                    easing: 'linear',
                    duration: 200
                });

                self.idle_anime.restart();
            }

            if (self.idle_anime.completed)
            {
                self.idle_anime.restart();
            }
        };

        setTimeout(idleFunc, 1000);
        self.idle_timer = setInterval(idleFunc, 5000);
    }

    getId()
    {
        return this.config.id;
    }

    /**
     * Returns booster position relative to the document.
     */
    getBoosterDocCoords()
    {
        this._cacheBoosterDocCoords();
        return this.booster_doc_coords;
    }

    /**
     * Returns booster position relative to the parent.
     */
    getBoosterParentCoords()
    {
        let doc_coords = this.getBoosterDocCoords();
        let par_coords = getCoords(this.parent.get(0));
        let coords = [ doc_coords[0] - par_coords.left, doc_coords[1] - par_coords.top ];
        return coords;
    }

    /**
     * Force a tear.
     */
    tear()
    {
        this._onMoveStart(0, 0);
        this._onMoveEnd(true, (this.config.xpad + this.pack_width_scale - 1), (this.config.ypad + this.pack_height_scale - 1));
    }

    //
    // Private event handlers
    //

    _onMoveStart(x, y)
    {
        if (this.state != 'ready')
        {
            return;
        }

        this.point_array = [];
        this._addPoint(x, y);
        this.state = 'tearing';

        if (this.idle_anime != null)
        {
            this.idle_anime.reset();
        }
        clearInterval(this.idle_timer);

        this._drawTear(true);
    }

    _onMoveUpdate(x, y)
    {
        if (this.state != 'tearing')
        {
            return;
        }

        this._addPoint(x, y);
        this._drawTear(false);
    }

    _onMoveEnd(has_coords, x, y)
    {
        if (this.state != 'tearing')
        {
            return;
        }

        if (has_coords)
        {
            this._addPoint(x, y);
        }
        
        this._cacheBoosterDocCoords();
        this.state = 'splitting';
        this._drawTear(true);
        this._splitStart();
    }

    //
    // Private routines
    //

    _cacheBoosterDocCoords()
    {
        if (this.state == 'ready' || this.state == 'tearing')
        {
            let coords = getCoords(this.canvas_js);
            this.booster_doc_coords = [ coords.left + this.config.xpad, coords.top + this.config.ypad ];
        }
        else if (this.state == 'splitting')
        {
            // coords won't be valid because component is no longer visible
        }
    }

    _addPoint(x, y)
    {
        x = Math.round(x);
        y = Math.round(y);

        if (x > this.config.xpad && x < (this.config.xpad + this.pack_width_scale) &&
            y > this.config.ypad && y < (this.config.ypad + this.pack_height_scale)
        )
        {
            if (this.point_array.length < 2)
            {
                this.point_array.push([x, y]);
            }
            else
            {
                this.point_array[1][0] = x;
                this.point_array[1][1] = y;
            }
        }
    }
    
    _drawTear(force)
    {
        if (!force)
        {
            let diff = (Date.now() - this.last_draw);
            if (diff < (1000 / this.config.fps))
            {
                return;
            }
        }

        this.last_draw = Date.now();

        //console.log('drawing...');

        this.ctx.clearRect(0, 0, this.canvas_js.width, this.canvas_js.height);
        this.ctx.drawImage(this.img_pack, 0, 0, this.config.pack_width, this.config.pack_height, this.config.xpad, this.config.ypad, this.pack_width_scale, this.pack_height_scale);

        if (this.point_array.length > 1)
        {
            this.ctx.strokeStyle = '#000000';
            this.ctx.beginPath();
            this.ctx.moveTo(this.point_array[0][0], this.point_array[0][1]);
            for (let i = 1; i < this.point_array.length; i++)
            {
                this.ctx.lineTo(this.point_array[i][0], this.point_array[i][1]);
            }
            //this.ctx.lineTo(this.point_array[this.point_array.length - 1][0], this.point_array[this.point_array.length - 1][1]);
            this.ctx.stroke();
        }
    }

    _splitStart()
    {
        // Generate perimeter points
        let peri_point_array = [];

        let gran_px = 8;
        let gran_x = Math.round(this.pack_width_scale / gran_px);
        let gran_y = Math.round(this.pack_height_scale / gran_px);
        let min_gap = Math.round(gran_x / 4);

        let side_info = {}; // 0 = top, 1 = right, 2 = bottom, 3 = left
        side_info[0] = { 'granularity': gran_x, 'size_px': this.pack_width_scale };
        side_info[1] = { 'granularity': gran_y, 'size_px': this.pack_height_scale };
        side_info[2] = { 'granularity': gran_x, 'size_px': this.pack_width_scale };
        side_info[3] = { 'granularity': gran_y, 'size_px': this.pack_height_scale };

        for (let i = 0; i < 4; i++)
        {
            let step_px = side_info[i].size_px / side_info[i].granularity;
            let pos = 0;

            for (let j = 0; j < side_info[i].granularity; j++)
            {
                let point = [ 0, 0 ];

                if (i == 0)
                {
                    point = [pos, 0];
                }
                else if (i == 1)
                {
                    point = [(this.pack_width_scale - 1), pos];
                }
                else if (i == 2)
                {
                    point = [(this.pack_width_scale - 1) - pos, (this.pack_height_scale - 1)];
                }
                else if (i == 3)
                {
                    point = [0, (this.pack_height_scale - 1) - pos];
                }

                point[0] = Math.round(this.config.xpad + point[0]);
                point[1] = Math.round(this.config.ypad + point[1]);

                peri_point_array.push(point);
                pos += step_px;
            }
        }

        // Determine user points
        for (let i = this.point_array.length; i < 2; i++)
        {
            this.point_array.push([this.config.xpad, this.config.ypad]);
        }

        let point_start = this.point_array[0];
        let point_end = this.point_array[this.point_array.length - 1];

        // Map user points to perimeter points
        let peri_start_info = this._nearestPoint2D(point_start, peri_point_array);
        let peri_end_info = this._nearestPoint2D(point_end, peri_point_array);

        let peri_start_side = 0;
        let peri_end_side = 0;

        let index = 0;
        for (let i = 0; i < 4; i++)
        {
            if (peri_start_info.index >= index && peri_start_info.index < (index + side_info[i].granularity))
            {
                peri_start_side = i;
            }
            if (peri_end_info.index >= index && peri_end_info.index < (index + side_info[i].granularity))
            {
                peri_end_side = i;
            }
            index += side_info[i].granularity;
        }

        let peri_gap = Math.round(this._pointDistance2D(peri_start_info.pt, peri_end_info.pt) / gran_px);

        // Correct impossible tears
        if (peri_start_side == peri_end_side)
        {
            // (straight lines)
            let new_end = peri_start_info.index + side_info[peri_start_side].granularity;
            if (new_end >= peri_point_array.length)
            {
                new_end = new_end - peri_point_array.length;
            }
            peri_end_info = this._nearestPoint2D(peri_point_array[new_end], peri_point_array);
            peri_end_side = 0; // (recalculate if needed)
        }
        else if (peri_gap < min_gap)
        {
            // (sharp corners)
            let half_min_gap = Math.round(min_gap / 2);

            let new_start = 0;
            let new_start_side = 0;
            let new_end = 0;
            let new_end_side = 0;
            if (peri_start_info.index < peri_end_info.index)
            {
                new_start = peri_start_info.index;
                new_start_side = peri_start_side;
                new_end = peri_end_info.index;
                new_end_side = peri_end_side;
            }
            else
            {
                new_start = peri_end_info.index;
                new_start_side = peri_end_side;
                new_end = peri_start_info.index;
                new_end_side = peri_start_side;
            }

            if (new_start_side == 0 && new_end_side == 3)
            {
                new_start += half_min_gap;
                new_end -= half_min_gap;
            }
            else
            {
                new_start -= half_min_gap;
                new_end += half_min_gap;
            }

            new_start = clamp(new_start, 0, peri_point_array.length - 1);
            new_end = clamp(new_end, 0, peri_point_array.length - 1);

            peri_start_info = this._nearestPoint2D(peri_point_array[new_start], peri_point_array);
            peri_start_side = 0; // (recalculate if needed)
            peri_end_info = this._nearestPoint2D(peri_point_array[new_end], peri_point_array);
            peri_end_side = 0; // (recalculate if needed)
        }

        // Generate split half polygons
        let h1_point_array = [];
        let h2_point_array = [];

        let h1_start = Math.min(peri_start_info.index, peri_end_info.index);
        let h1_end = Math.max(peri_start_info.index, peri_end_info.index);

        for (let i = h1_start; i <= h1_end; i++)
        {
            h1_point_array.push(peri_point_array[i]);
        }
        for (let i = 0; i < peri_point_array.length; i++)
        {
            if (i <= h1_start || i >= h1_end)
            {
                h2_point_array.push(peri_point_array[i]);
            }
        }

        // Update SVGs
        $('#tearbooster_pack1_svg > polygon').attr('points', this._pointArrayToSVGPolygonString(h1_point_array, -this.config.xpad, -this.config.ypad));
        $('#tearbooster_pack2_svg > polygon').attr('points', this._pointArrayToSVGPolygonString(h2_point_array, -this.config.xpad, -this.config.ypad));

        // Determine direction
        let h1_centroid = this._polygonCentroid(h1_point_array);
        let h1_centroid_norm = [ ((h1_centroid[0] - this.config.xpad) / this.pack_width_scale), ((h1_centroid[1] - this.config.ypad) / this.pack_height_scale) ];
        let h1_dir = [ (h1_centroid_norm[0] - 0.5) * 2.0, (h1_centroid_norm[1] - 0.5) * 2.0 ];

        let h2_centroid = this._polygonCentroid(h2_point_array);
        let h2_centroid_norm = [ ((h2_centroid[0] - this.config.xpad) / this.pack_width_scale), ((h2_centroid[1] - this.config.ypad) / this.pack_height_scale) ];
        let h2_dir = [ (h2_centroid_norm[0] - 0.5) * 2.0, (h2_centroid_norm[1] - 0.5) * 2.0 ];

        // Animate
        let self = this;

        let pack0_container = this.root.find('[data-id="pack0_container"]');
        let pack1_container = this.root.find('[data-id="pack1_container"]');
        let pack1_img = pack1_container.find('img').eq(0);
        let pack2_container = this.root.find('[data-id="pack2_container"]');
        let pack2_img = pack2_container.find('img').eq(0);

        pack0_container.hide();
        pack1_container.css({left: this.config.xpad + 'px', top: this.config.ypad + 'px', width: this.pack_width_scale, height: this.pack_height_scale });
        pack1_img.css({ width: this.pack_width_scale, height: this.pack_height_scale });
        pack1_container.show();
        pack2_container.css({left: this.config.xpad + 'px', top: this.config.ypad + 'px', width: this.pack_width_scale, height: this.pack_height_scale });
        pack2_img.css({ width: this.pack_width_scale, height: this.pack_height_scale });
        pack2_container.show();

        if (this.config['on_tear_start'] !== null)
        {
            this.config['on_tear_start'](this);
        }

        let duration = 1500;
        let dist = this.pack_height_scale;
        let rot = 45;

        let a1_complete = false;
        let a2_complete = false;

        let func_complete = function()
        {
            if (a1_complete && a2_complete)
            {
                pack1_container.hide();
                pack2_container.hide();

                self.state = 'done';

                if (self.config['on_tear_complete'] !== null)
                {
                    self.config['on_tear_complete'](self);
                }

                self.root.remove();
            }
        }

        anime({
            targets: pack1_container.get(0),
            left: (dist * h1_dir[0]),
            top:  (dist * h1_dir[1]),
            rotateZ: (-rot),
            opacity: 0.0,
            easing: 'linear',
            duration: duration,
            complete: function (anim) {
                a1_complete = true;
                func_complete();
            }
        });

        anime({
            targets: pack2_container.get(0),
            left: (dist * h2_dir[0]),
            top:  (dist * h2_dir[1]),
            rotateZ: (rot),
            opacity: 0.0,
            easing: 'linear',
            duration: duration,
            complete: function (anim) {
                a2_complete = true;
                func_complete();
            }
        });
    }

    _splitComplete()
    {

    }

    _nearestPoint2D(pt, pt_array)
    {
        if (pt_array.length == 0)
        {
            return pt;
        }

        let closest = pt;
        let closest_dist = 0x7fffffff;
        let closest_index = -1;

        for (let i = 0; i < pt_array.length; i++)
        {
            let d = this._pointDistance2D(pt_array[i], pt);
            d = Math.round(d);

            if (d < closest_dist)
            {
                closest = pt_array[i];
                closest_dist = d;
                closest_index = i;
            }
        }

        let pt_info = {};
        pt_info['pt'] = closest;
        pt_info['dist'] = closest_dist;
        pt_info['index'] = closest_index;

        return pt_info;
    }

    _pointDistance2D(pt1, pt2)
    {
        let d = Math.sqrt(Math.pow(pt2[0] - pt1[0], 2) + Math.pow(pt2[1] - pt1[1], 2));
        return d;
    }

    _pointArrayToSVGPolygonString(pt_array, xdiff, ydiff)
    {
        let str = '';
        if (pt_array.length > 1)
        {
            for (let i = 0; i < pt_array.length; i++)
            {
                if (i > 0)
                {
                    str += ', ';
                }
                str += (pt_array[i][0] + xdiff) + ' ' + (pt_array[i][1] + ydiff);
            }
        }
        return str;
    }

    _polygonCentroid(pt_array)
    {
        let centroid = [ 0.0, 0.0 ];

        if (pt_array.length > 0)
        {
            let sum_x = 0.0;
            let sum_y = 0.0;

            for (let i = 0; i < pt_array.length; i++)
            {
                sum_x += pt_array[i][0];
                sum_y += pt_array[i][1];
            }

            centroid[0] = sum_x / pt_array.length;
            centroid[1] = sum_y / pt_array.length;
        }

        return centroid;
    }

    _injectInstHTML()
    {
        let width_px = (this.pack_width_scale + (this.config.xpad * 2));
        let height_px = (this.pack_height_scale + (this.config.ypad * 2));
        let margin_left = this.config.margin_left;
        let margin_top = this.config.margin_top;

        let html = '';
        html += '<div id="' + html_encode(this.instance_id) + '" class="tearbooster" style="width: ' + width_px + 'px; height: ' + height_px + 'px; margin-left: ' + margin_left + 'px; margin-top: ' + margin_top + 'px;">' + "\n";
        html += '    <img data-id="img_pack" src="' + html_encode(this.config.pack_image) + '" style="display: none;">' + "\n";
        html += '    <div data-id="pack0_container" style="position: absolute; left: 0px; top: 0px; z-index: ' + (this.config.zindex + 1) + '">' + "\n";
        html += '        <canvas width="0" height="0">Your browser does not support the HTML5 canvas tag.</canvas>' + "\n";
        html += '    </div>' + "\n";
        html += '    <div data-id="pack1_container" style="position: absolute; left: 0px; top: 0px; z-index: ' + (this.config.zindex) + '; display: none;">' + "\n";
        html += '        <img class="tearbooster_pack1" src="' + html_encode(this.config.pack_image) + '">' + "\n";
        html += '    </div>' + "\n";
        html += '    <div data-id="pack2_container" style="position: absolute; left: 0px; top: 0px; z-index: ' + (this.config.zindex) + '; display: none;">' + "\n";
        html += '        <img class="tearbooster_pack2" src="' + html_encode(this.config.pack_image) + '">' + "\n";
        html += '    </div>' + "\n";
        html += '</div>' + "\n";
        html += '' + "\n";

        this.parent.append(html);
    }

    _injectCommonHTML()
    {
        let tearbooster_common = $('#tearbooster_common');

        if (tearbooster_common.length == 0)
        {
            let html = '';

            html += '<div id="tearbooster_common">' + "\n";
            html += '' + "\n";
            html += '   <svg height="0" width="0">' + "\n";
            html += '     <defs>' + "\n";
            html += '       <clipPath id="tearbooster_pack1_svg">' + "\n";
            html += '         <polygon points="0 0, 1 0, 1 1, 0 1"/>' + "\n";
            html += '       </clipPath>' + "\n";
            html += '     </defs>' + "\n";
            html += '   </svg>' + "\n";
            html += '' + "\n";
            html += '   <svg height="0" width="0">' + "\n";
            html += '       <defs>' + "\n";
            html += '           <clipPath id="tearbooster_pack2_svg">' + "\n";
            html += '                 <polygon points="0 0, 1 0, 1 1, 0 1"/>' + "\n";
            html += '           </clipPath>' + "\n";
            html += '       </defs>' + "\n";
            html += '   </svg>' + "\n";
            html += '</div>' + "\n";
            html += '' + "\n";

            $('body').eq(0).append(html);
        }
    }
}

//
// Static class members
//

Tearbooster.instance_id = 1;
