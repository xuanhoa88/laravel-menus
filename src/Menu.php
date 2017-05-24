<?php
namespace Llama\Menus;

use Llama\Menus\Collections\Menu as MenuCollection;
use Illuminate\Support\Traits\Macroable;

class Menu
{
    use Macroable;

    /**
     * Menu collection
     *
     * @var MenuCollection
     */
    protected $collection;

    /**
     * List of menu items
     *
     * @var array
     */
    protected $menu = [];

    /**
     * Initializing the menu builder
     *
     * @param MenuCollection $collection            
     */
    public function __construct(MenuCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Create a new menu instance
     *
     * @param string $name            
     * @param callable $callback            
     * @return \Llama\Menus\Menu
     */
    public function macro($name, \Closure $callback = null)
    {
        // Registering the menu.
        if (! array_key_exists($name, $this->menu)) {
            $this->menu[$name] = new Builder($name, $this->parseConfig($name));
        }
        
        // Invoke callback
        if ($callback) {
            call_user_func($callback, $this->menu[$name]);
        }
        
        // Storing each menu instance in the collection
        $this->collection->put($name, $this->menu[$name]);
        
        // Make the instance available in all views
        view()->share($name, $this->menu[$name]);
        
        return $this->menu[$name];
    }

    /**
     * Loads and merges configuration data
     *
     * @param string $name            
     * @return array
     */
    public function parseConfig($name)
    {
        $options = app('config')->get('llama.menus');
        if (empty($options['default'])) {
            $options['default'] = [];
        }
        if (! is_array($options['default'])) {
            $options['default'] = [
                $options['default']
            ];
        }
        if (isset($options[$name]) && is_array($options[$name])) {
            return array_merge($options['default'], $options[$name]);
        }
        return $options['default'];
    }

    /**
     * Return Menu instance from the collection by key
     *
     * @param string $key            
     * @return \Llama\Menus\Item
     */
    public function get($key)
    {
        return $this->collection->get($key);
    }

    /**
     * Return Menu collection
     *
     * @return \Llama\Support\Collections\Menu
     */
    public function all()
    {
        return $this->collection;
    }
}
