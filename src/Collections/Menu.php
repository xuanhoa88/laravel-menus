<?php
namespace Llama\Menus\Collections;

use Illuminate\Support\Collection;

class Menu extends Collection
{

    /**
     * Add attributes to a collection of items
     *
     * @return Llama\Menus\Collections\Menu
     */
    public function attr()
    {
        $args = func_get_args();
        $this->each(function ($item) use ($args) {
            if (count($args) >= 2) {
                $item->attr($args[0], $args[1]);
            } else {
                $item->attr($args[0]);
            }
        });
        
        return $this;
    }

    /**
     * Add meta data to a collection of items
     *
     * @return Llama\Menus\Collections\Menu
     */
    public function data()
    {
        $args = func_get_args();
        
        $this->each(function ($item) use ($args) {
            if (count($args) >= 2) {
                $item->data($args[0], $args[1]);
            } else {
                $item->data($args[0]);
            }
        });
        
        return $this;
    }

    /**
     * Appends text or HTML to a collection of items
     *
     * @param string $html            
     * @return Llama\Menus\Collections\Menu
     */
    public function append($html)
    {
        $this->each(function ($item) use ($html) {
            $item->title .= $html;
        });
        
        return $this;
    }

    /**
     * Prepends text or HTML to a collection of items
     *
     * @param string $html            
     * @return Llama\Menus\Collections\Menu
     */
    public function prepend($html, $key = null)
    {
        $this->each(function ($item) use ($html) {
            $item->title = $html . $item->title;
        });
        
        return $this;
    }
}
