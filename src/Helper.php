<?php
namespace Llama\Menus;

use Illuminate\Support\Arr;

class Helper
{
    /**
     * Build an HTML attribute string from an array.
     *
     * @param array $attributes
     * @return string
     */
    protected static function attributes(array $attributes)
    {
        $html = [];
        foreach ($attributes as $key => $value) {
            $element = static::attributeElement($key, $value);
            if (! is_null($element)) {
                $html[] = $element;
            }
        }
        return count($html) > 0 ? ' ' . implode(' ', $html) : '';
    }
    
    /**
     * Build a single attribute element.
     *
     * @param string $key
     * @param string $value
     * @return string
     */
    protected static function attributeElement($key, $value)
    {
        if (is_numeric($key)) {
            $key = $value;
        }
        if (! is_null($value)) {
            return $key . '="' . e($value) . '"';
        }
    }
    
    /**
     * Merge item's attributes with a static string of attributes
     *
     * @param array $attrs
     * @param array $old
     * @return string
     */
    public static function mergeAttributes(array $attrs, array $old = [])
    {
        // Merge classes
        $attrs['class'] = static::formatGroupClass($attrs, $old);
        
        // Merging new and old array and parse it as a string
        return static::attributes(array_merge(Arr::except($old, [
            'class'
        ]), $attrs));
    }
    
    /**
     * Get the valid attributes from the options.
     *
     * @param array $options
     * @return string
     */
    public static function formatGroupClass(array $new, array $old)
    {
        if (isset($new['class'])) {
            return implode(' ', array_unique(explode(' ', trim(trim(Arr::get($old, 'class')) . ' ' . trim(arr::get($new, 'class'))))));
        }
        return Arr::get($old, 'class');
    }
}
