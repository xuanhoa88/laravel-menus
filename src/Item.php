<?php
namespace Llama\Menus;

use Illuminate\Support\Str;

class Item
{

    /**
     * Reference to the menu builder
     *
     * @var Builder
     */
    protected $builder;

    /**
     * The ID of the menu item
     *
     * @var int
     */
    protected $id;

    /**
     * Item's title
     *
     * @var string
     */
    public $title;
    
    /**
     * Item's slug
     *
     * @var string
     */
    public $slug;

    /**
     * Item's title in camelCase
     *
     * @var string
     */
    public $nickname;

    /**
     * Item's seprator from the rest of the items, if it has any.
     *
     * @var array
     */
    public $divider = [];

    /**
     * Parent Id of the menu item
     *
     * @var int
     */
    protected $parent;

    /**
     * Extra information attached to the menu item
     *
     * @var array
     */
    protected $data = [];

    /**
     * Attributes of menu item
     *
     * @var array
     */
    public $attributes = [];

    /**
     * Flag for active state
     *
     * @var bool
     */
    public $isActive = false;

    /**
     * Creates a new Llama\Menus\MenuItem instance.
     *
     * @param \Llama\Menus\Builder $builder            
     * @param string $id            
     * @param string $title            
     * @param array $options            
     */
    public function __construct(Builder $builder, $id, $title, $options)
    {
        $this->builder = $builder;
        $this->id = $id;
        $this->title = $title;
        $this->slug = ($slug = isset($options['slug']) ? $options['slug'] : Str::slug($title));
        $this->nickname = isset($options['nickname']) ? $options['nickname'] : Str::camel($slug);
        $this->attributes = $this->builder->extractAttributes($options);
        $this->parent = isset($options['parent']) ? $options['parent'] : null;
        
        // Storing path options with each link instance.
        if (! is_array($options)) {
            $path = [
                'url' => $options
            ];
        } else {
            $path = array_only($options, [
                'url',
                'secure'
            ]);
        }
        
        if (isset($options['raw']) && $options['raw'] === true) {
            $path = null;
        }
        
        if (! is_null($path)) {
            $path['prefix'] = $this->builder->getLastGroupPrefix();
        }
        
        $this->link = $path ? new Link($path) : null;
        
        // Activate the item if items's url matches the request uri
        if (true === $this->builder->config('activate.auto')) {
            $this->autoActivated();
        }
    }

    /**
     * Creates a sub Item
     *
     * @param string $title            
     * @param string|array $options            
     * @return void
     */
    public function push($title, $options = '')
    {
        if (! is_array($options)) {
            $url = $options;
            $options = [];
            $options['url'] = $url;
        }
        
        $options['parent'] = $this->id;
        
        return $this->builder->push($title, $options);
    }

    /**
     * Add a plain text item
     *
     * @return Llama\Menus\Item
     */
    public function raw($title, array $options = [])
    {
        $options['parent'] = $this->id;
        
        return $this->builder->raw($title, $options);
    }

    /**
     * Insert a seprator after the item
     *
     * @param array $attributes            
     * @return Llama\Menus\Item
     */
    public function divide(array $attributes = [])
    {
        $attributes['class'] = Helper::formatGroupClass($attributes, [
            'class' => 'divider'
        ]);
        
        $this->divider = $attributes;
        
        return $this;
    }

    /**
     * Group children of the item
     *
     * @param array $attributes            
     * @param callable $callback            
     * @return Llama\Menus\Item
     */
    public function group(array $attributes, \Closure $callback)
    {
        $this->builder->group($attributes, $callback, $this);
        
        return $this;
    }

    /**
     * Add attributes to the item
     *
     * @param
     *            mixed
     * @return string|Llama\Menus\Item
     */
    public function attr()
    {
        $args = func_get_args();
        
        if (isset($args[0]) && is_array($args[0])) {
            $this->attributes = array_merge($this->attributes, $args[0]);
            return $this;
        }
        if (isset($args[0], $args[1])) {
            $this->attributes[$args[0]] = $args[1];
            return $this;
        } 
        if (isset($args[0])) {
            return isset($this->attributes[$args[0]]) ? $this->attributes[$args[0]] : null;
        }
        return $this->attributes;
    }

    /**
     * Generate URL for link
     *
     * @return string
     */
    public function url()
    {
        // If the item has a link proceed:
        if (! is_null($this->link)) {
            // If item's link has `href` property explcitly defined return it
            if ($this->link->href) {
                return $this->link->href;
            }
            // Otherwise dispatch to the proper address
            return $this->builder->dispatch($this->link->path);
        }
    }

    /**
     * Prepends text or html to the item
     *
     * @return Llama\Menus\Item
     */
    public function prepend($html)
    {
        $this->title = $html . $this->title;
        
        return $this;
    }

    /**
     * Appends text or html to the item
     *
     * @return Llama\Menus\Item
     */
    public function append($html)
    {
        $this->title .= $html;
        
        return $this;
    }

    /**
     * Checks if the item has any children
     *
     * @return boolean
     */
    public function hasChildren()
    {
        return count($this->builder->whereParent($this->id)) > 0;
    }

    /**
     * Returns childeren of the item
     *
     * @return Llama\Menus\Collections\Menu
     */
    public function children()
    {
        return $this->builder->whereParent($this->id);
    }

    /**
     * Returns all childeren of the item
     *
     * @return Llama\Menus\Collections\Menu
     */
    public function all()
    {
        return $this->builder->whereParent($this->id, true);
    }

    /**
     * Decide if the item should be active
     */
    protected function autoActivated()
    {
        if ($this->builder->config('restful') === true) {
            $path = ltrim(parse_url($this->url(), PHP_URL_PATH), '/');
            $rpath = request()->path();
            
            if ($this->builder->config('rest_base')) {
                $base = is_array($this->builder->config('rest_base')) ? implode('|', $this->builder->config('rest_base')) : $this->builder->config('rest_base');
                list ($path, $rpath) = preg_replace('@^(' . $base . ')/@', '', [
                    $path,
                    $rpath
                ], 1);
            }
            
            if (preg_match("@^{$path}(/.+)?\z@", $rpath)) {
                $this->activate();
            }
        } else {
            if ($this->url() == request()->url()) {
                $this->activate();
            }
        }
    }

    /**
     * Set nickname for the item manually
     *
     * @param string $nickname            
     * @return \Llama\Menus\Item
     */
    public function nickname($nickname = null)
    {
        if (is_null($nickname)) {
            return $this->nickname;
        }
        
        $this->nickname = $nickname;
        
        return $this;
    }
    
    /**
     * Set slug for the item manually
     *
     * @param string $slug
     * @return \Llama\Menus\Item
     */
    public function slug($slug = null)
    {
        if (is_null($slug)) {
            return $this->slug;
        }
        
        $this->slug = $slug;
        
        return $this;
    }

    /**
     * Set id for the item manually
     *
     * @param mixed $id            
     * @return \Llama\Menus\Item
     */
    public function id($id = null)
    {
        if (is_null($id)) {
            return $this->id;
        }
        
        $this->id = $id;
        
        return $this;
    }

    /**
     * Activate the item
     *
     * @param \Llama\Menus\Item $item            
     */
    public function activate(Item $item = null)
    {
        $item = is_null($item) ? $this : $item;
        
        // Check to see which element should have class 'active' set.
        if ($this->builder->config('activate.element') === 'item') {
            $item->active();
        } else {
            $item->link->active();
        }
        
        // If parent activation is enabled:
        if (true === $this->builder->config('activate.parents')) {
            // Moving up through the parent nodes, activating them as well.
            if ($item->parent) {
                $this->activate($this->builder->whereId($item->parent)->first());
            }
        }
    }

    /**
     * Make the item active
     *
     * @return Llama\Menus\Item
     */
    public function active($pattern = null)
    {
        if (! is_null($pattern)) {
            $pattern = ltrim(preg_replace('/\/\*/', '(/.*)?', $pattern), '/');
            if (preg_match("@^{$pattern}\z@", request()->path())) {
                $this->activate();
            }
            
            return $this;
        }
        
        $this->attributes['class'] = Helper::formatGroupClass([
            'class' => $this->builder->config('activate.class')
        ], $this->attributes);
        $this->isActive = true;
        
        return $this;
    }

    /**
     * Set or get items's meta data
     *
     * @return string|Llama\Menus\Item
     */
    public function data()
    {
        $args = func_get_args();
        
        if (isset($args[0]) && is_array($args[0])) {
            $this->data = array_merge($this->data, array_change_key_case($args[0]));
            
            // Cascade data to item's children if inheritance option is enabled
            if ($this->builder->config('inheritance')) {
                $this->cascadeData($args);
            }
            
            return $this;
        }
        if (isset($args[0], $args[1])) {
            $this->data[strtolower($args[0])] = $args[1];
            
            // Cascade data to item's children if inheritance option is enabled
            if ($this->builder->config('inheritance')) {
                $this->cascadeData($args);
            }
            
            return $this;
        }
        
        if (isset($args[0])) {
            return isset($this->data[$args[0]]) ? $this->data[$args[0]] : null;
        }
        
        return $this->data;
    }

    /**
     * Cascade data to children
     *
     * @param array $args            
     */
    public function cascadeData(array $args = [])
    {
        if (! $this->hasChildren()) {
            return false;
        }
        if (count($args) >= 2) {
            $this->children()->data($args[0], $args[1]);
        } else {
            $this->children()->data($args[0]);
        }
    }

    /**
     * Check if propery exists either in the class or the meta collection
     *
     * @param String $property            
     * @return Boolean
     */
    public function hasProperty($property)
    {
        if (property_exists($this, $property) || ! is_null($this->data($property))) {
            return true;
        }
        return false;
    }

    /**
     * Search in meta data if a property doesn't exist otherwise return the property
     *
     * @param string $prop
     * @return mixed
     */
    public function __get($prop)
    {
        if (property_exists($this, $prop)) {
            return $this->{$prop};
        }
        
        return $this->data($prop);
    }
}
