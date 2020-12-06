
//
// DOMCard
// HTML element for representing a playing card
// Dependencies: global.js utility functions, jquery, anime.js
//

//
// Class definition
//

class DOMCard
{
    //
    // Public routines
    //

    /**
     * @param config - Object with key/value pairs for class configuration.
     *        config.id - custom user identifier
     *        config.parent - JQuery selector of parent object
     *        config.image - image source path "/path/to/image.jpg"
     *        config.image_width - image width
     *        config.image_height - image height
     *        config.x - (optional) x position. default 0.
     *        config.y - (optional) y position. default 0.
     *        config.width - (optional) element width. default (image_width)
     *        config.height - (optional) element height. default (image_height)
     *        config.scale - (optional) element scale. default 1.0.
     *        config.rot_x - (optional) x rotation. default 0.0.
     *        config.rot_y - (optional) y rotation. default 0.0.
     *        config.rot_z - (optional) z rotation. default 0.0.
     *        config.hide - (boolean) (optional) create the card hidden. default false.
     *        config.zindex - (optional) base z-index. default 0.
     *        config.border - (optional) 'alpha'|'beta'. default: 'beta'.
     *        config.class_array - (optional) extra css classes. default []
     *        config.data_map - (optional) object with key/values for data attributes
     */
    constructor(config)
    {
        let self = this;

        if (!config.hasOwnProperty('x'))
        {
            config['x'] = 0;
        }
        if (!config.hasOwnProperty('y'))
        {
            config['y'] = 0;
        }
        if (!config.hasOwnProperty('width'))
        {
            config['width'] = config.image_width;
        }
        if (!config.hasOwnProperty('height'))
        {
            config['height'] = config.image_height;
        }
        if (!config.hasOwnProperty('scale'))
        {
            config['scale'] = 1.0;
        }
        if (!config.hasOwnProperty('rot_x'))
        {
            config['rot_x'] = 0.0;
        }
        if (!config.hasOwnProperty('rot_y'))
        {
            config['rot_y'] = 0.0;
        }
        if (!config.hasOwnProperty('rot_z'))
        {
            config['rot_z'] = 0.0;
        }
        if (!config.hasOwnProperty('hide'))
        {
            config['hide'] = false;
        }
        if (!config.hasOwnProperty('zindex'))
        {
            config['zindex'] = 0;
        }
        if (!config.hasOwnProperty('border'))
        {
            config['border'] = 'beta';
        }
        if (!config.hasOwnProperty('class_array'))
        {
            config['class_array'] = [];
        }
        if (!config.hasOwnProperty('data_map'))
        {
            config['data_map'] = {};
        }

        this.config = config;
        this.parent = this.config.parent;
        this.root = null;
        this.ani = null;

        this.instance_id = 'domcard_' + DOMCard.instance_id;
        DOMCard.instance_id++;

        // Add component to DOM
        this._injectInstHTML();
        this.root = $('#' + this.instance_id);
        this._updateCSS(['all']);
        if (!this.config.hide)
        {
            this.show();
        }
    }

    getId()
    {
        return this.config.id;
    }

    getConfig()
    {
        return this.config;
    }

    /**
     * @returns {DOMNode}
     */
    getElement()
    {
        return this.root.get(0);
    }

    getElementJQ()
    {
        return this.root;
    }

    hide()
    {
        this.root.hide();
    }

    show()
    {
        this.root.show();
    }

    remove()
    {
        this.root.remove();
    }

    /**
     * @param {string} image
     * @param {number} image_width (optional)
     * @param {number} image_height (optional)
     */
    setImage(image, image_width = 0, image_height = 0)
    {
        this.config.image = image;

        if (image_width > 0)
        {
            this.config.image_width = image_width;
        }
        if (image_height > 0)
        {
            this.config.image_height = image_height;
        }
        this._updateCSS(['image']);
    }

    /**
    * Set position, in pixels.
     * @param {number} x
     * @param {number} y
     */
    setPos(x, y)
    {
        this.config.x = x;
        this.config.y = y;
        this._updateCSS(['pos']);
    }

    /**
     * @returns {number[]}
     */
    getPos()
    {
        return [ this.config.x, this.config.y ];
    }

    /**
    * Set size, in pixels.
     * @param {number} width
     * @param {number} height
     */
    setSize(width, height)
    {
        this.config.width = width;
        this.config.height = height;
        this._updateCSS(['size']);
    }

    /**
     * @param {number} scale
     */
    setScale(scale)
    {
        this.config.scale = scale;
        this._updateCSS(['size']);
    }

    /**
    * Set rotation, in degrees.
     * @param {number} x
     * @param {number} y
     * @param {number} z
     */
    setRot(x, y, z)
    {
        this.config.rot_x = x;
        this.config.rot_y = y;
        this.config.rot_z = z;
        this._updateCSS(['rot']);
    }

    /**
     * @param {number} zindex
     */
    setZIndex(zindex)
    {
        this.config.zindex = zindex;
        this._updateCSS(['zindex']);
    }

    /**
     * @param {string} border
     */
    setBorder(border)
    {
        this.config.border = border;
        this._updateCSS(['border']);
    }

    /**
     * @param {string[]} class_array
     */
    setClassArray(class_array)
    {
        this.config.class_array = class_array;
        this._updateCSS(['class']);
    }

    /**
     * @param {string} name - 'zoom'
     * @param on_complete - callback when animation completes. syntax: function (domcard)
     */
    animate(name, on_complete = undefined)
    {
        let self = this;

        if (self.ani != null)
        {
            self.ani.reset();
            self.ani = null;
        }

        if (name == 'zoom')
        {
            let root_js = self.root.get(0);
            let root_viewrect = root_js.getBoundingClientRect();
            let root_center = [ root_viewrect.left + (self.root.width() / 2), root_viewrect.top + (self.root.height() / 2) ];
            let view_center = [ window.innerWidth / 2, window.innerHeight / 2 ];
            let move_dir = [ (view_center[0] - root_center[0]) / window.innerWidth, (view_center[1] - root_center[1]) / window.innerHeight ];
            let dist = 500;

            self.ani = anime({
                targets: root_js,
                scaleX: 2.0,
                scaleY: 2.0,
                opacity: 0.0,
                translateX: (move_dir[0] * dist),
                translateY: (move_dir[1] * dist),
                easing: 'linear',
                duration: 1000,
                complete: function (anim)
                {
                    self.ani.reset();
                    self.ani = null;

                    if (on_complete !== undefined)
                    {
                        on_complete(self);
                    }
                }
            });
        }
    }

    isAnimating()
    {
        return (this.ani !== null);
    }



    //
    // Public event handlers
    //

    click(fn)
    {
        this.root.click(fn);
    }

    mousedown(fn)
    {
        this.root.mousedown(fn);
    }

    mouseup(fn)
    {
        this.root.mouseup(fn);
    }

    mouseout(fn)
    {
        this.root.mouseout(fn);
    }

    mousemove(fn)
    {
        this.root.mousemove(fn);
    }

    //
    // Private routines
    //

    _injectInstHTML()
    {
        let data_html = '';
        let key_array = Object.keys(this.config.data_map);
        for (let i = 0; i < key_array.length; i++)
        {
            data_html += ' ';
            data_html += 'data-' + key_array[i] + '="' + html_encode(this.config.data_map[key_array[i]]) + '"';
        }

        let html = '';
        html += '<img id="' + html_encode(this.instance_id) + '" alt="" src="' + html_encode(this.config.image) + '" class="' + this._getCSSClassString() +'" style="position: absolute; left: 0px; top: 0px; width: 0px; height: 0px; display: none;"' + data_html + '/>' + "\n";
        this.parent.append(html);
    }

    /**
     * @param field_array - array of categories to be updated. possible elements: 'all'|'image'|'pos'|'size'|'rot'|'zindex'|'border'
     * @private
     */
    _updateCSS(field_array)
    {
        let set_image = false;
        let set_pos = false;
        let set_size = false;
        let set_rot = false;
        let set_zindex = false;
        let set_class = false;

        for (let i = 0; i < field_array.length; i++)
        {
            let field = field_array[i];

            if (field == 'all')
            {
                set_image = true;
                set_pos = true;
                set_size = true;
                set_rot = true;
                set_zindex = true;
                set_class = true;
            }
            else if (field == 'image')
            {
                set_image = true;
                set_size = true;
            }
            else if (field == 'pos')
            {
                set_pos = true;
            }
            else if (field == 'size')
            {
                set_size = true;
            }
            else if (field == 'rot')
            {
                set_rot = true;
            }
            else if (field == 'zindex')
            {
                set_zindex = true;
            }
            else if (field == 'border')
            {
                set_class = true;
            }
            else if (field == 'class')
            {
                set_class = true;
            }
        }

        let css = {};

        if (set_image)
        {
            this.root.attr('src', this.config.image);
        }
        if (set_pos)
        {
            css['left'] = this.config.x + 'px';
            css['top'] = this.config.y + 'px';
        }
        if (set_size)
        {
            css['width'] = (this.config.width * this.config.scale) + 'px';
            css['height'] = (this.config.height * this.config.scale) + 'px';
        }
        if (set_rot)
        {
            css['transform'] = '';
            if (Math.abs(this.config.rot_x) > 0.001)
            {
                css['transform'] += 'rotateX(' + this.config.rot_x + 'deg)';
            }

            if (Math.abs(this.config.rot_y) > 0.001)
            {
                if (css['transform'].length > 0)
                {
                    css['transform'] += ' ';
                }
                css['transform'] += 'rotateY(' + this.config.rot_y + 'deg)';
            }

            if (Math.abs(this.config.rot_z) > 0.001)
            {
                if (css['transform'].length > 0)
                {
                    css['transform'] += ' ';
                }
                css['transform'] += 'rotateZ(' + this.config.rot_z + 'deg)';
            }
        }
        if (set_zindex)
        {
            css['z-index'] = this.config.zindex;
        }
        //if (set_border)
        //{
        //    let radius = (this.config.border == 'alpha') ? 48 : 30;
        //    css['border-radius'] = (radius * this.config.scale) + 'px';
        //}
        if (set_class)
        {
            this.root.attr('class', this._getCSSClassString());
        }

        this.root.css(css);
    }

    _getCSSClassString()
    {
        let css_class = 'domcard';
        for (let i = 0; i < this.config.class_array.length; i++)
        {
            if (this.config.class_array[i] != 'domcard')
            {
                css_class += ' ' + this.config.class_array[i];
            }
        }

        css_class += ' ' + ((this.config.border == 'alpha') ? 'domcard-border-radius-alpha' : 'domcard-border-radius-beta');

        return css_class;
    }

    // ...
}

//
// Static class members
//

DOMCard.instance_id = 1;
