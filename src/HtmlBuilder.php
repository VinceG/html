<?php namespace Collective\Html;

use Illuminate\Support\HtmlString;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Traits\Macroable;
use Collective\Html\Traits\ObfuscateTrait;
use Illuminate\Contracts\Routing\UrlGenerator;

class HtmlBuilder
{
    use ObfuscateTrait,
        Macroable {
            __call as callMacro;
        }

    /**
     * The URL generator instance.
     *
     * @var \Illuminate\Contracts\Routing\UrlGenerator
     */
    protected $url;

    /**
     * The View Factory instance.
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $view;

    /**
     * The registered components.
     *
     * @var array
     */
    protected static $components = [];

    /**
     * Create a new HTML builder instance.
     *
     * @param \Illuminate\Contracts\Routing\UrlGenerator  $url
     * @param \Illuminate\Contracts\View\Factory  $view
     */
    public function __construct(UrlGenerator $url = null, Factory $view)
    {
        $this->url  = $url;
        $this->view = $view;
    }

    /**
     * Convert an HTML string to entities.
     *
     * @param  string  $value
     *
     * @return string
     */
    public function entities($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Convert entities to HTML characters.
     *
     * @param  string  $value
     *
     * @return string
     */
    public function decode($value)
    {
        return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate a link to a JavaScript file.
     *
     * @param  string  $url
     * @param  array   $attributes
     * @param  bool    $secure
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function script($url, $attributes = [], $secure = null)
    {
        $attributes['src'] = $this->url->asset($url, $secure);

        return $this->toHtmlString('<script'.$this->attributes($attributes).'></script>'.PHP_EOL);
    }

    /**
     * Generate a link to a CSS file.
     *
     * @param  string  $url
     * @param  array   $attributes
     * @param  bool    $secure
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function style($url, $attributes = [], $secure = null)
    {
        $defaults = ['media' => 'all', 'type' => 'text/css', 'rel' => 'stylesheet'];

        $attributes = $attributes + $defaults;

        $attributes['href'] = $this->url->asset($url, $secure);

        return $this->toHtmlString('<link'.$this->attributes($attributes).'>'.PHP_EOL);
    }

    /**
     * Generate an HTML image element.
     *
     * @param  string  $url
     * @param  string  $alt
     * @param  array   $attributes
     * @param  bool    $secure
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function image($url, $alt = null, $attributes = [], $secure = null)
    {
        $attributes['alt'] = $alt;

        return $this->toHtmlString('<img src="'.$this->url->asset($url,
            $secure).'"'.$this->attributes($attributes).'>');
    }

    /**
     * Generate a link to a Favicon file.
     *
     * @param string $url
     * @param array  $attributes
     * @param bool   $secure
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function favicon($url, $attributes = [], $secure = null)
    {
        $defaults = ['rel' => 'shortcut icon', 'type' => 'image/x-icon'];

        $attributes = $attributes + $defaults;

        $attributes['href'] = $this->url->asset($url, $secure);

        return $this->toHtmlString('<link'.$this->attributes($attributes).'>'.PHP_EOL);
    }

    /**
     * Generate a HTML link.
     *
     * @param  string  $url
     * @param  string  $title
     * @param  array   $attributes
     * @param  bool    $secure
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function link($url, $title = null, $attributes = [], $secure = null)
    {
        $url = $this->url->to($url, [], $secure);

        if (is_null($title) || $title === false) {
            $title = $url;
        }

        return $this->toHtmlString('<a href="'.$url.'"'.$this->attributes($attributes).'>'.$this->entities($title).'</a>');
    }

    /**
     * Generate a HTTPS HTML link.
     *
     * @param  string  $url
     * @param  string  $title
     * @param  array   $attributes
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function secureLink($url, $title = null, $attributes = [])
    {
        return $this->link($url, $title, $attributes, true);
    }

    /**
     * Generate a HTML link to an asset.
     *
     * @param  string  $url
     * @param  string  $title
     * @param  array   $attributes
     * @param  bool    $secure
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function linkAsset($url, $title = null, $attributes = [], $secure = null)
    {
        $url = $this->url->asset($url, $secure);

        return $this->link($url, $title ?: $url, $attributes, $secure);
    }

    /**
     * Generate a HTTPS HTML link to an asset.
     *
     * @param  string  $url
     * @param  string  $title
     * @param  array   $attributes
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function linkSecureAsset($url, $title = null, $attributes = [])
    {
        return $this->linkAsset($url, $title, $attributes, true);
    }

    /**
     * Generate a HTML link to a named route.
     *
     * @param  string  $name
     * @param  string  $title
     * @param  array   $parameters
     * @param  array   $attributes
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function linkRoute($name, $title = null, $parameters = [], $attributes = [])
    {
        return $this->link($this->url->route($name, $parameters), $title, $attributes);
    }

    /**
     * Generate a HTML link to a controller action.
     *
     * @param  string  $action
     * @param  string  $title
     * @param  array   $parameters
     * @param  array   $attributes
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function linkAction($action, $title = null, $parameters = [], $attributes = [])
    {
        return $this->link($this->url->action($action, $parameters), $title, $attributes);
    }

    /**
     * Generate a HTML link to an email address.
     *
     * @param  string  $email
     * @param  string  $title
     * @param  array   $attributes
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function mailto($email, $title = null, $attributes = [])
    {
        $email = $this->email($email);

        $title = $title ?: $email;

        $email = $this->obfuscate('mailto:').$email;

        return $this->toHtmlString('<a href="'.$email.'"'.$this->attributes($attributes).'>'.$this->entities($title).'</a>');
    }

    /**
     * Obfuscate an e-mail address to prevent spam-bots from sniffing it.
     *
     * @param  string  $email
     *
     * @return string
     */
    public function email($email)
    {
        return str_replace('@', '&#64;', $this->obfuscate($email));
    }

    /**
     * Generate an ordered list of items.
     *
     * @param  array   $list
     * @param  array   $attributes
     *
     * @return \Illuminate\Support\HtmlString|string
     */
    public function ol($list, $attributes = [])
    {
        return $this->listing('ol', $list, $attributes);
    }

    /**
     * Generate an un-ordered list of items.
     *
     * @param  array   $list
     * @param  array   $attributes
     *
     * @return \Illuminate\Support\HtmlString|string
     */
    public function ul($list, $attributes = [])
    {
        return $this->listing('ul', $list, $attributes);
    }

    /**
     * Generate a description list of items.
     *
     * @param  array   $list
     * @param  array   $attributes
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function dl(array $list, array $attributes = [])
    {
        $attributes = $this->attributes($attributes);

        $html = "<dl{$attributes}>";

        foreach ($list as $key => $value) {
            $html .= "<dt>{$key}</dt>";

            foreach ((array) $value as $description) {
                $html .= "<dd>{$description}</dd>";
            }
        }

        $html .= '</dl>';

        return $this->toHtmlString($html);
    }

    /**
     * Create a listing HTML element.
     *
     * @param  string  $type
     * @param  array   $list
     * @param  array   $attributes
     *
     * @return \Illuminate\Support\HtmlString|string
     */
    protected function listing($type, $list, $attributes = [])
    {
        $html = '';

        if (count($list) == 0) {
            return $html;
        }

        // Essentially we will just spin through the list and build the list of the HTML
        // elements from the array. We will also handled nested lists in case that is
        // present in the array. Then we will build out the final listing elements.
        foreach ($list as $key => $value) {
            $html .= $this->listingElement($key, $type, $value);
        }

        $attributes = $this->attributes($attributes);

        return $this->toHtmlString("<{$type}{$attributes}>{$html}</{$type}>");
    }

    /**
     * Create the HTML for a listing element.
     *
     * @param  mixed   $key
     * @param  string  $type
     * @param  mixed   $value
     *
     * @return string
     */
    protected function listingElement($key, $type, $value)
    {
        if (is_array($value)) {
            return $this->nestedListing($key, $type, $value);
        } else {
            return '<li>'.e($value).'</li>';
        }
    }

    /**
     * Create the HTML for a nested listing attribute.
     *
     * @param  mixed   $key
     * @param  string  $type
     * @param  mixed   $value
     *
     * @return string
     */
    protected function nestedListing($key, $type, $value)
    {
        if (is_int($key)) {
            return $this->listing($type, $value);
        } else {
            return '<li>'.$key.$this->listing($type, $value).'</li>';
        }
    }

    /**
     * Build an HTML attribute string from an array.
     *
     * @param  array  $attributes
     *
     * @return string
     */
    public function attributes($attributes)
    {
        $html = [];

        // For numeric keys we will assume that the key and the value are the same
        // as this will convert HTML attributes such as "required" to a correct
        // form like required="required" instead of using incorrect numerics.
        foreach ((array) $attributes as $key => $value) {
            $element = $this->attributeElement($key, $value);

            if (! is_null($element)) {
                $html[] = $element;
            }
        }

        return count($html) > 0 ? ' '.implode(' ', $html) : '';
    }

    /**
     * Build a single attribute element.
     *
     * @param  string  $key
     * @param  string  $value
     *
     * @return string
     */
    protected function attributeElement($key, $value)
    {
        // For numeric keys we will assume that the value is a boolean attribute
        // where the presence of the attribute represents a true value and the
        // absence represents a false value.
        if (is_numeric($key)) {
            return $value;
        }

        if (! is_null($value)) {
            return $key.'="'.e($value).'"';
        }
    }

    /**
     * Generate a meta tag.
     *
     * @param string $name
     * @param string $content
     * @param array  $attributes
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function meta($name, $content, array $attributes = [])
    {
        $defaults = compact('name', 'content');

        $attributes = array_merge($defaults, $attributes);

        return $this->toHtmlString('<meta'.$this->attributes($attributes).'>'.PHP_EOL);
    }

    /**
     * Register a custom component.
     *
     * @param       $name
     * @param       $view
     * @param array $signature
     *
     * @return void
     */
    public static function component($name, $view, array $signature)
    {
        static::$components[$name] = compact('view', 'signature');
    }

    /**
     * Check if a component is registered.
     *
     * @param $name
     *
     * @return bool
     */
    public static function hasComponent($name)
    {
        return isset(static::$components[$name]);
    }

    /**
     * Render a custom component.
     *
     * @param        $name
     * @param  array $arguments
     *
     * @return \Illuminate\Contracts\View\View
     */
    protected function renderComponent($name, array $arguments)
    {
        $component = static::$components[$name];
        $data      = $this->getComponentData($component['signature'], $arguments);

        return $this->view->make($component['view'], $data);
    }

    /**
     * Prepare the component data, while respecting provided defaults.
     *
     * @param  array $signature
     * @param  array $arguments
     *
     * @return array
     */
    protected function getComponentData(array $signature, array $arguments)
    {
        $data = [];

        $i = 0;
        foreach ($signature as $variable => $default) {
            // If the "variable" value is actually a numeric key, we can assume that
            // no default had been specified for the component argument and we'll
            // just use null instead, so that we can treat them all the same.
            if (is_numeric($variable)) {
                $variable = $default;
                $default  = null;
            }

            $data[$variable] = array_get($arguments, $i) ?: $default;

            $i++;
        }

        return $data;
    }

    /**
     * Transform the string to an Html serializable object.
     *
     * @param $html
     *
     * @return \Illuminate\Support\HtmlString
     */
    protected function toHtmlString($html)
    {
        return new HtmlString($html);
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param  string $method
     * @param  array  $parameters
     *
     * @return \Illuminate\Contracts\View\View|mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (static::hasComponent($method)) {
            return $this->renderComponent($method, $parameters);
        }

        return $this->macroCall($method, $parameters);
    }
}
