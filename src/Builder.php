<?php
namespace Llama\Menus;

use Llama\Menus\Collections\Menu as MenuCollection;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class Builder
{

    /**
     * The items container
     *
     * @var MenuCollection
     */
    protected $items;

    /**
     * The Menu name
     *
     * @var string
     */
    protected $name;

    /**
     * The Menu configuration data
     *
     * @var array
     */
    protected $config = [];

    /**
     * The route group attribute stack.
     *
     * @var array
     */
    protected $groupStack = [];

    /**
     * The reserved attributes.
     *
     * @var array
     */
    protected $reserved = [
        'url',
        'prefix',
        'parent',
        'secure',
        'raw'
    ];

    /**
     * Initializing the menu manager
     *
     * @param string $name            
     * @param array $config            
     * @return void
     */
    public function __construct($name, array $config = [])
    {
        $this->name = $name;
        $this->config = $config;
        $this->items = new MenuCollection();
    }

    /**
     * Pushs an item to the menu
     *
     * @param string $title            
     * @param string|array $acion            
     * @return Llama\Menus\Item $item
     */
    public function push($title, $options = [])
    {
        $id = isset($options['id']) ? $options['id'] : $this->id();
        $item = new Item($this, $id, $title, $options);
        $this->items->push($item);
        
        return $item;
    }

    /**
     * Generate an integer identifier for each new item
     *
     * @return int
     */
    protected function id()
    {
        return Str::random(32);
    }

    /**
     * Add raw content
     *
     * @return Llama\Menus\Item
     */
    public function raw($title, array $options = [])
    {
        $options['raw'] = true;
        
        return $this->push($title, $options);
    }

    /**
     * Returns menu item by name
     *
     * @return Llama\Menus\Item
     */
    public function get($title)
    {
        return $this->whereNickname($title)->first();
    }

    /**
     * Returns menu item by Id
     *
     * @return Llama\Menus\Item
     */
    public function find($id)
    {
        return $this->whereId($id)->first();
    }

    /**
     * Return all items in the collection
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Return the first item in the collection
     *
     * @return Llama\Menus\Item
     */
    public function first()
    {
        return $this->items->first();
    }

    /**
     * Return the last item in the collection
     *
     * @return Llama\Menus\Item
     */
    public function last()
    {
        return $this->items->last();
    }

    /**
     * Returns menu item by name
     *
     * @return Llama\Menus\Item
     */
    public function item($title)
    {
        return $this->whereNickname($title)->first();
    }

    /**
     * Insert a separator after the item
     *
     * @param array $attributes            
     * @return void
     */
    public function divide(array $attributes = [])
    {
        $attributes['class'] = Helper::formatGroupClass([
            'class' => 'divider'
        ], $attributes);
        
        $this->items->last()->divider = $attributes;
    }

    /**
     * Create a menu group with shared attributes.
     *
     * @param array $attributes            
     * @param callable $callback            
     * @return void
     */
    public function group(array $attributes, \Closure $callback)
    {
        $this->updateGroupStack($attributes);
        
        // Once we have updated the group stack, we will execute the user Closure and
        // merge in the groups attributes when the item is created. After we have
        // run the callback, we will pop the attributes off of this group stack.
        call_user_func($callback, $this);
        
        array_pop($this->groupStack);
    }

    /**
     * Update the group stack with the given attributes.
     *
     * @param array $attributes            
     * @return void
     */
    protected function updateGroupStack(array $attributes = [])
    {
        if (count($this->groupStack) > 0) {
            $attributes = $this->mergeWithLastGroup($attributes);
        }
        $this->groupStack[] = $attributes;
    }

    /**
     * Merge the given array with the last group stack.
     *
     * @param array $new            
     * @return array
     */
    protected function mergeWithLastGroup(array $new)
    {
        return $this->mergeGroup($new, last($this->groupStack));
    }

    /**
     * Merge the given group attributes.
     *
     * @param array $new            
     * @param array $old            
     * @return array
     */
    protected function mergeGroup(array $new, array $old)
    {
        $new['prefix'] = $this->formatGroupPrefix($new, $old);
        $new['class'] = Helper::formatGroupClass($new, $old);
        
        return array_merge(Arr::except($old, [
            'prefix',
            'class'
        ]), $new);
    }

    /**
     * Format the prefix for the new group attributes.
     *
     * @param array $new            
     * @param array $old            
     * @return string
     */
    protected function formatGroupPrefix(array $new, array $old)
    {
        if (isset($new['prefix'])) {
            return trim(Arr::get($old, 'prefix'), '/') . (($newPrefix = trim($new['prefix'], '/')) ? '/' . $newPrefix : '');
        }
        return trim(Arr::get($old, 'prefix'), '/');
    }

    /**
     * Get the prefix from the last group on the stack.
     *
     * @return string
     */
    public function getLastGroupPrefix()
    {
        if (count($this->groupStack) > 0) {
            return Arr::get(last($this->groupStack), 'prefix', '');
        }
        return null;
    }

    /**
     * Get the valid attributes from the options.
     *
     * @param array $options            
     * @return array
     */
    public function extractAttributes($options = [])
    {
        if (! is_array($options)) {
            $options = [];
        }
        if (count($this->groupStack) > 0) {
            $options = $this->mergeWithLastGroup($options);
        }
        return Arr::except($options, $this->reserved);
    }

    /**
     * Get the form action from the options.
     *
     * @return string
     */
    public function dispatch(array $options)
    {
        if (! isset($options['url'])) {
            $options['url'] = null;
        }
        return $this->getUrl($options);
    }

    /**
     * Get the action for a "url" option.
     *
     * @param array|string $options            
     * @return string
     */
    protected function getUrl(array $options)
    {
        foreach ($options as $key => $value) {
            ${$key} = $value;
        }
        
        $secure = (isset($options['secure']) && $options['secure'] === true) ? true : false;
        
        if (is_array($url)) {
            if ($this->isAbsoluteUrl($url[0])) {
                return $url[0];
            }
            return url()->to((isset($prefix) ? $prefix : '') . (isset($url[0]) ? '/' . trim($url[0], '/') : ''), array_slice($url, 1), $secure);
        }
        
        if ($this->isAbsoluteUrl($url)) {
            return $url;
        }
        
        return url()->to((isset($prefix) ? $prefix : '') . (isset($url) ? '/' . trim($url, '/') : ''), [], $secure);
    }

    /**
     * Check if the given url is an absolute url.
     *
     * @param string $url            
     * @return boolean
     */
    public function isAbsoluteUrl($url)
    {
        return parse_url($url, PHP_URL_SCHEME) or false;
    }

    /**
     * Returns items with no parent
     *
     * @return \Llama\Support\Collections\Menu
     */
    public function roots()
    {
        return $this->whereParent();
    }

    /**
     * Filter menu items by user callbacks
     *
     * @param callable $callback            
     * @return Llama\Menus\Builder
     */
    public function filter(\Closure $callback)
    {
        $this->items = $this->items->filter($callback);
        
        return $this;
    }

    /**
     * Sorts the menu based on user's callable
     *
     * @param string $sortBy            
     * @param string|callable $sortType            
     * @return Llama\Menus\Builder
     */
    public function sortBy($sortBy, $sortType = 'asc')
    {
        if (is_callable($sortBy)) {
            $rslt = call_user_func($sortBy, $this->items->toArray());
            if (! is_array($rslt)) {
                $rslt = [
                    $rslt
                ];
            }
            
            $this->items = new MenuCollection($rslt);
        }
        
        // running the sort proccess on the sortable items
        $this->items = $this->items->sort(function ($f, $s) use ($sortBy, $sortType) {
            $f = $f->{$sortBy};
            $s = $s->{$sortBy};
            
            if ($f == $s) {
                return 0;
            }
            if ($sortType == 'asc') {
                return $f > $s ? 1 : - 1;
            }
            return $f < $s ? 1 : - 1;
        });
        
        return $this;
    }

    /**
     * Generate the menu items as list items using a recursive function
     *
     * @param string $type            
     * @param string $parent            
     * @return string
     */
    public function render($type = 'ul', $parent = null, array $childrenAttributes = [])
    {
        $items = [];
        
        $tag = in_array($type, [
            'ul',
            'ol'
        ]) ? 'li' : $type;
        
        foreach ($this->whereParent($parent) as $item) {
            $items[] = '<' . $tag . Helper::mergeAttributes($item->attr()) . '>';
            
            if ($item->link) {
                $items[] = '<a' . Helper::mergeAttributes($item->link->attr()) . ' href="' . $item->url() . '">' . $item->title . '</a>';
            } else {
                $items[] = $item->title;
            }
            
            if ($item->hasChildren()) {
                $items[] = '<' . $type . Helper::mergeAttributes($childrenAttributes) . '>';
                $items[] = $this->render($type, $item->id);
                $items[] = "</{$type}>";
            }
            
            $items[] = "</{$tag}>";
            
            if ($item->divider) {
                $items[] = '<' . $tag . Helper::mergeAttributes($item->divider) . '></' . $tag . '>';
            }
        }
        
        return implode('', $items);
    }

    /**
     * Returns the menu as an unordered list.
     *
     * @param array $attributes            
     * @param array $childrenAttributes            
     * @return string
     */
    public function asUl(array $attributes = [], array $childrenAttributes = [])
    {
        return '<ul' . Helper::mergeAttributes($attributes) . '>' . $this->render('ul', null, $childrenAttributes) . '</ul>';
    }

    /**
     * Returns the menu as an ordered list.
     *
     * @param array $attributes            
     * @param array $childrenAttributes            
     * @return string
     */
    public function asOl(array $attributes = [], array $childrenAttributes = [])
    {
        return '<ol' . Helper::mergeAttributes($attributes) . '>' . $this->render('ol', null, $childrenAttributes) . '</ol>';
    }

    /**
     * Returns the menu as div containers
     *
     * @param array $attributes            
     * @param array $childrenAttributes            
     * @return string
     */
    public function asDiv(array $attributes = [], array $childrenAttributes = [])
    {
        return '<div' . Helper::mergeAttributes($attributes) . '>' . $this->render('div', null, $childrenAttributes) . '</div>';
    }

    /**
     * Return configuration value by key
     *
     * @param string $key            
     * @param mixed $default            
     * @return string
     */
    public function config($key, $default = null)
    {
        return Arr::get($this->config, $key, $default);
    }

    /**
     * Filter items recursively
     *
     * @param string $attribute            
     * @param mixed $value            
     * @return Llama\Menus\Collections\Menu
     */
    protected function filterRecursive($attribute, $value)
    {
        $collection = new MenuCollection();
        
        // Iterate over all the items in the main collection
        $this->items->each(function ($item) use ($attribute, $value, &$collection) {
            if (! $this->hasProperty($attribute)) {
                return false;
            }
            if ($item->{$attribute} == $value) {
                $collection->push($item);
                
                // Check if item has any children
                if ($item->hasChildren()) {
                    $collection = $collection->merge($this->filterRecursive($attribute, $item->id));
                }
            }
        });
        
        return $collection;
    }

    /**
     * Search the menu based on an attribute
     *
     * @param string $method            
     * @param array $args            
     *
     * @return Llama\Menus\Item
     */
    public function __call($method, $args)
    {
        preg_match('/^[W|w]here([a-zA-Z0-9_]+)$/', $method, $matches);
        if (! $matches) {
            return false;
        }
        
        $attribute = strtolower($matches[1]);
        $value = isset($args[0]) ? $args[0] : null;
        $recursive = isset($args[1]) ? $args[1] : false;
        
        if ($recursive) {
            return $this->filterRecursive($attribute, $value);
        }
        
        return $this->items->filter(function ($item) use ($attribute, $value) {
            if (! $item->hasProperty($attribute)) {
                return false;
            }
            if ($item->{$attribute} == $value) {
                return true;
            }
            return false;
        })
            ->values();
    }

    /**
     * Returns menu item by name
     *
     * @return Llama\Menus\Item
     */
    public function __get($prop)
    {
        if (property_exists($this, $prop)) {
            
            return $this->{$prop};
        }
        
        return $this->get($prop);
    }
}
