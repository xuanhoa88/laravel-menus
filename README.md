# Laravel Menu
[![Latest Stable Version](https://poser.pugx.org/llama-laravel/menus/v/stable.svg)](https://packagist.org/packages/llama-laravel/menus)
[![Latest Unstable Version](https://poser.pugx.org/llama-laravel/menus/v/unstable.svg)](https://packagist.org/packages/llama-laravel/menus)
[![Total Downloads](https://poser.pugx.org/llama-laravel/menus/downloads.svg)](https://packagist.org/packages/llama-laravel/menus)
[![License](https://poser.pugx.org/llama-laravel/menus/license.svg)](https://packagist.org/packages/llama-laravel/menus)


A quick and easy way to create menus in [Laravel 5](http://laravel.com/)


## Documentation

* [Installation](#installation)
* [Getting Started](#getting-started)
* [Routing](#routing)
	- [URLs](#urls)	
	- [Named Routes](#named-routes)
	- [Controller Actions](#controller-actions)
	- [HTTPS](#https)
* [Sub-items](#sub-items)
* [Set Item's ID Manualy](#)
* [Set Item's Nicknames Manualy](#)
* [Referring to Items](#referring-to-items)
	- [Get Item by Title](#get-item-by-title)
	- [Get Item by Id](#get-item-by-id)
	- [Get All Items](#get-all-items)
	- [Get the First Item](#get-the-first-item)
	- [Get the Last Item](#get-the-last-item)
	- [Get Sub-items of the Item](#get-sub-items-of-the-item)
	- [Magic Where Methods](#magic-where-methods)
* [Referring to Menu Objects](#referring-to-menu-instances)
* [HTML Attributes](#html-attributes)
* [Manipulating Links](#manipulating-links)
	- [Link's Href Property](#links-href-property)
* [Active Item](#active-item)
	- [RESTful URLs](#restful-urls)
	- [URL Wildcards](#url-wildcards)
* [Inserting a Separator](#inserting-a-separator)
* [Append and Prepend](#append-and-prepend)
* [Raw Items](#raw-items)
* [Menu Groups](#menu-groups)
* [URL Prefixing](#url-prefixing)
* [Nested Groups](#nested-groups)
* [Meta Data](#meta-data)
* [Filtering the Items](#filtering-the-items)
* [Sorting the Items](#sorting-the-items)
* [Rendering Methods](#rendering-methods)
	- [Menu as Unordered List](#menu-as-unordered-list)
	- [Menu as Ordered List](#menu-as-ordered-list)
	- [Menu as Div](#menu-as-div)
	- [Menu as Bootstrap 3 Navbar](#menu-as-bootstrap-3-navbar)
* [Advanced Usage](#advanced-usage)
	+ [A Basic Example](#a-basic-example)
* [Configuration](#configuration)
* [If You Need Help](#if-you-need-help)
* [License](#license)


## Installation


```bash
composer require llama-laravel/menus
```

Now, append Laravel Menu service provider to `providers` array in `config/app.php`.

```php
<?php

'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Foundation\Providers\ArtisanServiceProvider::class,
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
	
	...
        
        Llama\Menus\MenuServiceProvider::class,
        
        ...

],
?>
```

At the end of `config/app.php` add `'Menu'    => 'Llama\Menus\Facades\Menu'` to the `$aliases` array:

```php
<?php

'aliases' => [

    'App'       => Illuminate\Support\Facades\App::class,
    'Artisan'   => Illuminate\Support\Facades\Artisan::class,
    ...
    'Menu'       => Llama\Menus\Facades\Menu::class,

],
?>
```

This registers the package with Laravel and creates an alias called `Menu`.


## Getting Started

You can define the menu definitions inside a [laravel middleware](http://laravel.com/docs/master/middleware). As a result anytime a request hits your application, the menu objects will be available to all your views.


Here is a basic usage:


```php
<?php
\Menu::macro('MyNavBar', function($menu){
  
  $menu->push('Home');
  $menu->push('About',    'about');
  $menu->push('services', 'services');
  $menu->push('Contact',  'contact');
  
});
?>
```

**Attention** `$MyNavBar` is just a hypothetical name I used in these examples; You may name your menus whatever you please.

In the above example `\Menu::macro()` creates a menu named `MyNavBar`, Adds the menu instance to the `\Menu::collection` and ultimately makes `$myNavBar` object available across all application views.

This method accepts a callable inside which you can define your menu items. `add` method defines a new item. It receives two parameters, the first one is the item title and the second one is options.

*options* can be a simple string representing a URL or an associative array of options and HTML attributes which we'll discuss shortly.



**To render the menu in your view:**

`llama-laravel/menus` provides three rendering methods out of the box. However you can create your own rendering method using the right methods and attributes.

As noted earlier, `llama-laravel/menus` provides three rendering formats out of the box, `asUl()`, `asOl()` and `asDiv()`. You can read about the details [here](#rendering-methods).

```php
{!! $MyNavBar->asUl() !!}
```

You can also access the menu object via the menu collection:

```php
{!! \Menu::get('MyNavBar')->asUl() !!}
```

This will render your menu like so:

```html
<ul>
  <li><a href="http://yourdomain.com">Home</a></li>
  <li><a href="http://yourdomain.com/about">About</a></li>
  <li><a href="http://yourdomain.com/services">Services</a></li>
  <li><a href="http://yourdomain.com/contact">Contact</a></li>
</ul>
```
And that's all about it!


## Routing

#### URLs

You can simply assign a URL to your menu item by passing the URL as the second argument to `add` method:

```php
<?php
// ...
$menu->push('About Us', 'about-us');
// ...
```

#### Named Routes

`llama-laravel/menus` supports named routes as well:

This time instead of passing a simple string to `push()`, we pass an associative with key `route` and a named route as value:

```php
<?php

// Suppose we have these routes defined in our app/routes.php file 

//...
Route::get('/', ['as' => 'home.page',  function(){...}]);
Route::get('about', ['as' => 'page.about', function(){...}]);
//...

// Now we make the menu:

\Menu::macro('MyNavBar', function($menu){
  
  $menu->push('Home', route('home.page'));
  $menu->push('About', route('page.about'));
  // ...

});
?>
```


#### Controller Actions

Laravel Menu supports controller actions as well.

You will just need to set `action` key of your options array to a controller action:

```php
<?php

// Suppose we have these routes defined in our app/Http/routes.php file

// ...
Route::get('services', 'ServiceController@index');
//...


  // ...
  $menu->push('services', action('ServicesController@index'));
  // ...

?>
```


**Note:** if you need to send some data to routes, URLs or controller actions as a query string, you can simply include them in an array along with the route, action or URL value:

```php
<?php
\Menu::macro('MyNavBar', function($menu){
  
  $menu->push('Home', route('home.page'));
  $menu->push('About', route('page.about', ['template' => 1]));
  $menu->push('services', action('ServicesController@index', ['id' => 12]));
 
  $menu->push('Contact',  'contact');

});
?>
```

#### HTTPS

If you need to serve the route over HTTPS, call `secure()` on the item's `link` attribute or alternatively add key `secure` to the options array and set it to `true`:

```php
<?php
	// ...
	$menu->push('Members', 'members')->link->secure();
	
	
	// or alternatively use the following method
	
	$menu->push('Members', ['url' => 'members', 'secure' => true]);
	
	// ...

?>
```

The output as `<ul>` would be:

```html
<ul>
	...
	<li><a href="https://yourdomain.com/members">Members</a></li>
	...
</ul>
```


## Sub-items

Items can have sub-items too: 

```php
<?php
\Menu::macro('MyNavBar', function($menu){

  //...
  
  $menu->push('About', route('page.about'));
  
  // these items will go under Item 'About'
  
  // refer to about as a property of $menu object then call `push()` on it
  $menu->about->push('Who We are', 'who-we-are');

  // or
  
  $menu->get('about')->push('What We Do', 'what-we-do');
  
  // or
  
  $menu->item('about')->push('Our Goals', 'our-goals');
  
  //...

});
?>
```

You can also chain the item definitions and go as deep as you wish:

```php  
<?php

  // ...
  
  $menu->push('About', route('page.about'))
		     ->push('Level2', 'link address')
		          ->push('level3', 'Link address')
		               ->push('level4', 'Link address');
        
  // ...      
?>
```  

It is possible to add sub items directly using `parent` attribute:

```php  
<?php
	//...
	$menu->push('About', route('page.about'));
	$menu->push('Level2', ['url' => 'Link address', 'parent' => $menu->about->id]);
	//...
?>
```  

## Set Item's ID Manually

When you add a new item, a unique ID is automatically assigned to the item. However, there are time when you're loading the menu items from the database and you have to set the ID manually. To handle this, you can call the `id()` method against the item's object and pass your desired ID:

```php
<?php
	// ...
	$menu->push('About', route('page.about'))
	     ->id('74398247329487')
	// ...
```

Alternatively, you can pass the ID as an element of the options array when adding the menu item:

```php
<?php
	// ...
	$menu->push('About', route('page.about', ['id' => 74398247329487]));
	// ...
```

## Set Item's Nickname Manually

When you add a new item, a nickname is automatically assigned to the item for further reference. This nickname is the camel-cased form of the item's title. For instance, an item with the title: `About Us` would have the nickname: `aboutUs`.
However there are times when you have to explicitly define your menu items owing to a special character set you're using. To do this, you may simply use the `nickname()` method against the item's object and pass your desired nickname to it:

```php
<?php
	// ...
	$menu->push('About', route('page.about'))
	     ->nickname('about_menu_nickname');
	     
	// And use it like you normally would
	$menu->item('about_menu_nickname');     
	     
	// ...
```

Alternatively, you can pass the nickname as an element of the options array:

```php
<?php
	// ...
	$menu->push('About', route('page.about', ['nickname' => 'about_menu_nickname']));
	
	// And use it like you normally would
	$menu->item('about_menu_nickname');    
	// ...
```

## Referring to Items


You can access defined items throughout your code using the methods described below.

#### Get Item by Title

 along with item's title in *camel case*:

```php
<?php
	// ...
	
	$menu->itemTitleInCamelCase ...
	
	// or
	
	$menu->get('itemTitleInCamelCase') ...
	
	// or
	
	$menu->item('itemTitleInCamelCase') ...
	
	// ...
?>
```

As an example, let's insert a divider after `About us` item after we've defined it:

```php
<?php
    // ...
	
	$menu->push('About us', 'about-us')
	
	$menu->aboutUs->divide();
	
	// or
	
	$menu->get('aboutUs')->divide();
	
	// or
	
	$menu->item('aboutUs')->divide();
	
	// ...
?>
```

If you're not comfortable with the above method you can store the item's object reference in a variable for further reference:

```php
<?php
// ...
$about = $menu->push('About', 'about');
$about->push('Who We Are', 'who-we-are');
$about->push('What We Do', 'what-we-do');
// ...
```


#### Get Item By Id

You can also get an item by Id if needed:

```php
<?php
	// ...
	$menu->find(12) ...
	// ...
?>
```

#### Get All Items

```php
<?php
	// ...
	$menu->all();

	// or outside of the builder context
	\Menu::get('MyNavBar')->all();
	// ...
?>
```
`all()` returns a *Menu Collection*.

#### Get the First Item

```php
<?php
	// ...
	$menu->first();

	// or outside of the builder context
	\Menu::get('MyNavBar')->first();
	// ...
?>
```

#### Get the Last Item

```php
<?php
	// ...
	$menu->last();

	// or outside of the builder context
	\Menu::get('MyNavBar')->last();
	// ...
?>
```

#### Get Sub-Items of the Item

First of all you need to get the item using the methods described above then call `children()` on it.

To get children of `About` item:

```php
<?php
	// ...
	$aboutSubs = $menu->about->children();

	// or outside of the builder context
	$aboutSubs = \Menu::get('MyNavBar')->about->children();

	// Or
	$aboutSubs = \Menu::get('MyNavBar')->item('about')->children();
	// ...
?>
```
`children()` returns a *Menu Collection*.

To check if an item has any children or not, you can use `hasChildren()`

```php
<?php
	// ...
	if( $menu->about->hasChildren() ) {
		// Do something
	}

	// or outside of the builder context
	\Menu::get('MyNavBar')->about->hasChildren();

	// Or
	\Menu::get('MyNavBar')->item('about')->hasChildren();
	// ...
?>
```

To get all descendants of an item you may use `all`:

```php
<?php
// ...
$aboutSubs = $menu->about->all();
// ...

```


#### Magic Where Methods

You can also search the items collection by magic where methods.
These methods are consisted of a `where` concatenated with a property (object property or even meta data)

For example to get an item with parent equal to 12, you can use it like so:

```php
<?php
	// ...
	$subs = $menu->whereParent(12);
	// ...
?>
```

Or to get item's with a specific meta data:

```php
<?php
	// ...
	$menu->push('Home',     '#')->data('color', 'red');
	$menu->push('About',    '#')->data('color', 'blue');
	$menu->push('Services', '#')->data('color', 'red');
	$menu->push('Contact',  '#')->data('color', 'green');
	// ...
	
	// Fetch all the items with color set to red:
	$reds = $menu->whereColor('red');
	
?>
```

This method returns a *Laravel collection*.

If you need to fetch descendants of the matched items as well, Just set the second argument as true.

```php
<?php
$reds = $menu->whereColor('red', true);
```

This will give all items with color red and their decsendants.


## Referring to Menu Instances

You might encounter situations when you need to refer to menu instances out of the builder context.


To get a specific menu by name:

```php
<?php
	// ...
	$menu = \Menu::get('MyNavBar');
	// ...
?>
```

Or to get all menus instances:

```php
<?php
	// ...
	$menus = \Menu::all();
	// ...
?>
```

## HTML Attributes

Since all menu items would be rendered as HTML entities like list items or divs, you can define as many HTML attributes as you need for each item:


```php
<?php
\Menu::macro('MyNavBar', function($menu){

  // As you see, you need to pass the second parameter as an associative array:
  $menu->push('Home', ['url'  => route('home.page'),  'class' => 'navbar navbar-home', 'id' => 'home']);
  $menu->push('About', ['url'  => route('page.about'), 'class' => 'navbar navbar-about dropdown']);
  $menu->push('services', action('ServicesController@index'));
  $menu->push('Contact', 'contact');

});
?>
```

If we choose HTML lists as our rendering format like `ul`, the result would be something similar to this:

```html
<ul>
  <li class="navbar navbar-home" id="home"><a href="http://yourdomain.com">Home</a></li>
  <li class="navbar navbar-about dropdown"><a href="http://yourdomain.com/about">About</a></li>
  <li><a href="http://yourdomain.com/services">Services</a></li>
  <li><a href="http://yourdomain.com/contact">Contact</a></li>
</ul>
```

It is also possible to set or get HTML attributes after the item has been defined using `attr()` method.


If you call `attr()` with one argument, it will return the attribute value for you.
If you call it with two arguments, It will consider the first and second parameters as a key/value pair and sets the attribute. 
You can also pass an associative array of attributes if you need to add a group of HTML attributes in one step; Lastly if you call it without any arguments it will return all the attributes as an array.

```php
<?php
	//...
	$menu->push('About', ['url' => 'about', 'class' => 'about-item']);
	
	echo $menu->about->attr('class');  // output:  about-item
	
	$menu->about->attr('class', 'another-class');
	echo $menu->about->attr('class');  // output:  about-item another-class

	$menu->about->attr(['class' => 'yet-another', 'id' => 'about']); 
	
	echo $menu->about->attr('class');  // output:  about-item another-class yet-another
	echo $menu->about->attr('id');  // output:  id
	
	print_r($menu->about->attr());
	
	/* Output
	Array
	(
		[class] => about-item another-class yet-another
		[id] => id
	)
	*/
	
	//...
?>
```

You can use `attr` on a collection, if you need to target a group of items:

```php
<?php
  // ...
  $menu->push('About', 'about');
  
  $menu->about->push('Who we are', 'about/whoweare');
  $menu->about->push('What we do', 'about/whatwedo');
  
  // add a class to children of About
  $menu->about->children()->attr('class', 'about-item');
  
  // ...
```

## Manipulating Links

All the HTML attributes will go to the wrapping tags(li, div, etc); You might encounter situations when you need to add some HTML attributes to `<a>` tags as well.

Each `Item` instance has an attribute which stores an instance of `Link` object. This object is provided for you to manipulate `<a>` tags.

Just like each item, `Link` also has an `attr()` method which functions exactly like item's:

```php
<?php
\Menu::macro('MyNavBar', function($menu){

 //  ...
  
  $about = $menu->push('About', ['url'  => route('page.about'), 'class' => 'navbar navbar-about dropdown']);
  
  $about->link->attr(['class' => 'dropdown-toggle', 'data-toggle' => 'dropdown']);
  
 // ...
  
});
?>
```

#### Link's Href Property

If you don't want to use the routing feature of `llama-laravel/menus` or you don't want the builder to prefix your URL with anything (your host address for example), you can explicitly set your link's href property:

```
<?php
// ...
$menu->push('About')->link->href('#');
// ...
?>
```

## Active Item

You can mark an item as activated using `active()` on that item:

```php
<?php
	// ...
	$menu->push('Home', '#')->active();
	// ...
	
	/* Output
	
	<li class="active"><a href="#">#</a></li>	
	
	*/
	
?>
```

You can also add class `active` to the anchor element instead of the wrapping element (`div` or `li`):

```php
<?php
	// ...
	$menu->push('Home', '#')->link->active();
	// ...
	
	/* Output
	
	<li><a class="active" href="#">#</a></li>	
	
	*/
	
?>
```

Laravel Menu does this for you automatically according to the current **URI** the time you register the item.

You can also choose the element to be activated (item or the link) in `settings.php` which resides in package's config directory:

```php

	// ...
	'activate.element' => 'item',    // item|link
	// ...

```

#### RESTful URLs

RESTful URLs are also supported as long as `restful` option is set as `true` in `config/settings.php` file, E.g. menu item with url `resource` will be activated by `resource/slug` or `resource/slug/edit`.  

You might encounter situations where your app is in a sub directory instead of the root directory or your resources have a common prefix; In such case you need to set `rest_base` option to a proper prefix for a better restful activation support. `rest_base` can take a simple string, array of string or a function call as value.

#### URL Wildcards

`llama-laravel/menus` makes you able to define a pattern for a certain item, if the automatic activation can't help:

```php
<?php
// ...
$menu->push('Articles', 'articles')->active('this-is-another-url/*');
// ...
```

So `this-is-another-url`, `this-is-another-url/and-another` will both activate `Articles` item.

## Inserting a Separator

You can insert a separator after each item using `divide()` method:

```php
<?php
	//...
	$menu->push('Separated Item', 'item-url')->divide()
	
	// You can also use it this way:
	
	$menu->('Another Separated Item', 'another-item-url');
	
	// This line will insert a divider after the last defined item
	$menu->divide()
	
	//...
	
	/*
	Output as <ul>:
	
		<ul>
			...
			<li><a href="item-url">Separated Item</a></li>
			<li class="divider"></li>
			
			<li><a href="another-item-url">Another Separated Item</a></li>
			<li class="divider"></li>
			...
		</ul>
		
	*/

?>
```

`divide()` also gets an associative array of attributes:

```php
<?php
	//...
	$menu->push('Separated Item', 'item-url')->divide(['class' => 'my-divider']);
	//...
	
	/*
	Output as <ul>:
	
		<ul>
			...
			<li><a href="item-url">Separated Item</a></li>
			<li class="my-divider divider"></li>
		
			...
		</ul>
		
	*/
?>
```


## Append and Prepend


You can `append` or `prepend` HTML or plain-text to each item's title after it is defined:

```php
<?php
\Menu::macro('MyNavBar', function($menu){

  // ...
  
  $about = $menu->push('About', ['url'  => route('page.about'), 'class' => 'navbar navbar-about dropdown']);
  
  $menu->about->attr(['class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'])
              ->append(' <b class="caret"></b>')
              ->prepend('<span class="glyphicon glyphicon-user"></span> ');
              
  // ...            

});
?>
```

The above code will result:

```html
<ul>
  ...
  
  <li class="navbar navbar-about dropdown">
   <a href="about" class="dropdown-toggle" data-toggle="dropdown">
     <span class="glyphicon glyphicon-user"></span> About <b class="caret"></b>
   </a>
  </li>
</ul>

```

You can call `prepend` and `append` on collections as well.

## Raw Items

To insert items as plain text instead of hyper-links you can use `raw()`:

```php
<?php
    // ...
    $menu->raw('Item Title', ['class' => 'some-class']);  
    
    $menu->push('About', 'about');
    $menu->About->raw('Another Plain Text Item')
    // ...
    
    /* Output as an unordered list:
       <ul>
            ...
            <li class="some-class">Item's Title</li>
            <li>
                About
                <ul>
                    <li>Another Plain Text Item</li>
                </ul>
            </li>
            ...
        </ul>
    */
?>
```


## Menu Groups

Sometimes you may need to share attributes between a group of items. Instead of specifying the attributes and options for each item, you may use a menu group feature:

**PS:** This feature works exactly like Laravel group routes. 


```php
<?php
\Menu::macro('MyNavBar', function($menu){

  $menu->push('Home', ['url'  => route('home.page'), 'class' => 'navbar navbar-home', 'id' => 'home']);
  
  $menu->group(['style' => 'padding: 0', 'data-role' => 'navigation'], function($m){
        $m->push('About', ['url'  => route('page.about'), 'class' => 'navbar navbar-about dropdown']);
        $m->push('services', action('ServicesController@index'));
  }
  
  $menu->push('Contact',  'contact');

});
?>
```

Attributes `style` and `data-role` would be applied to both `About` and `Services` items:

```html
<ul>
    <li class="navbar navbar-home" id="home"><a href="http://yourdomain.com">Home</a></li>
    <li style="padding: 0" data-role="navigation" class="navbar navbar-about dropdown"><a href="http://yourdomain.com/about"About</a></li>
    <li style="padding: 0" data-role="navigation"><a href="http://yourdomain.com/services">Services</a></li>
</ul>
```


## URL Prefixing

Just like Laravel route prefixing feature, a group of menu items may be prefixed by using the `prefix` option in the  array being passed to the group.

**Attention:** Prefixing only works on the menu items addressed with `url` but not `route` or `action`. 

```php
<?php
\Menu::macro('MyNavBar', function($menu){

  $menu->push('Home', ['url'  => route('home.page'), 'class' => 'navbar navbar-home', 'id' => 'home']);
  
  $menu->push('About', ['url'  => 'about', 'class' => 'navbar navbar-about dropdown']);  // URL: /about 
  
  $menu->group(['prefix' => 'about'], function($m){
  
  	$about->push('Who we are?', 'who-we-are');   // URL: about/who-we-are
  	$about->push('What we do?', 'what-we-do');   // URL: about/what-we-do
  	
  });
  
  $menu->push('Contact',  'contact');

});
?>
```

This will generate:

```html
<ul>
    <li  class="navbar navbar-home" id="home"><a href="/">Home</a></li>
    
    <li  data-role="navigation" class="navbar navbar-about dropdown"><a href="http://yourdomain.com/about/summary"About</a>
    	<ul>
    	   <li><a href="http://yourdomain.com/about/who-we-are">Who we are?</a></li>
    	   <li><a href="http://yourdomain.com/about/who-we-are">What we do?</a></li>
    	</ul>
    </li>
    
    <li><a href="services">Services</a></li>
    <li><a href="contact">Contact</a></li>
</ul>
```

## Nested Groups

Laravel Menu supports nested grouping feature as well. A menu group merges its own attribute with its parent group then shares them between its wrapped items:

```php
<?php
\Menu::macro('MyNavBar', function($menu){

	// ...
	
	$menu->group(['prefix' => 'pages', 'data-info' => 'test'], function($m){
		
		$m->push('About', 'about');
		
		$m->group(['prefix' => 'about', 'data-role' => 'navigation'], function($a){
		
			$a->push('Who we are', 'who-we-are?');
			$a->push('What we do?', 'what-we-do');
			$a->push('Our Goals', 'our-goals');
		});
	});
	
});
?>
```

If we render it as a ul:

```html
<ul>
	...
	<li data-info="test">
		<a href="http://yourdomain.com/pages/about">About</a>
		<ul>
			<li data-info="test" data-role="navigation"><a href="http://yourdomain.com/pages/about/who-we-are"></a></li>
			<li data-info="test" data-role="navigation"><a href="http://yourdomain.com/pages/about/what-we-do"></a></li>
			<li data-info="test" data-role="navigation"><a href="http://yourdomain.com/pages/about/our-goals"></a></li>
		</ul>
	</li>
</ul>
```


## Meta Data

You might encounter situations when you need to attach some meta data to each item; This data can be anything from item placement order to permissions required for accessing the item; You can do this by using `data()` method.

`data()` method works exactly like `attr()` method:

If you call `data()` with one argument, it will return the data value for you.
If you call it with two arguments, It will consider the first and second parameters as a key/value pair and sets the data. 
You can also pass an associative array of data if you need to add a group of key/value pairs in one step; Lastly if you call it without any arguments it will return all data as an array.

```php
<?php
\Menu::macro('MyNavBar', function($menu){

  // ...
  
  $menu->push('Users', route('admin.users'))
       ->data('permission', 'manage_users');

});
?>
```

You can also access a data as if it's a property:

```php
<?php
	
	//...
	
	$menu->push('Users', '#')->data('placement', 12);
	
	// you can refer to placement as if it's a public property of the item object
	echo $menu->users->placement;    // Output : 12
	
	//...
?>
```

Meta data don't do anything to the item and won't be rendered in HTML either. It is the developer who would decide what to do with them.

You can use `data` on a collection, if you need to target a group of items:

```php
<?php
  // ...
  $menu->push('Users', 'users');
  
  $menu->users->push('New User', 'users/new');
  $menu->users->push('Uses', 'users');
  
  // add a meta data to children of Users
  $menu->users->children()->data('anything', 'value');
  
  // ...
```

## Filtering the Items

We can filter menu items by a using `filter()` method. 
`Filter()` receives a closure which is defined by you.It then iterates over the items and run your closure on each of them.

You must return false for items you want to exclude and true for those you want to keep.


Let's proceed with a real world scenario:

I suppose your `User` model can check whether the user has an specific permission or not:

```php
<?php
\Menu::macro('MyNavBar', function($menu){

  // ...
  
  $menu->push('Users', route('admin.users'))
       ->data('permission', 'manage_users');

})->filter(function($item){
  if(User::get()->can( $item->data('permission'))) {
      return true;
  }
  return false;
});
?>
```
As you might have noticed we attached the required permission for each item using `data()`.

As result, `Users` item will be visible to those who has the `manage_users` permission.


## Sorting the Items

`llama-laravel/menus` can sort the items based on either a user defined function or a key which can be item properties like id,parent,etc or meta data stored with each item.


To sort the items based on a property and or meta data:

```php
<?php
\Menu::macro('main', function($m){

	$m->push('About', '#')     ->data('order', 2);
	$m->push('Home', '#')      ->data('order', 1);
	$m->push('Services', '#')  ->data('order', 3);
	$m->push('Contact', '#')   ->data('order', 5);
	$m->push('Portfolio', '#') ->data('order', 4);

})->sortBy('order');		
?>
```

`sortBy()` also receives a second parameter which specifies the ordering direction: Ascending order(`asc`) and Descending Order(`dsc`). 

Default value is `asc`.


To sort the items based on `Id` in descending order:

```php
<?php
\Menu::macro('main', function($m){

	$m->push('About');
	$m->push('Home');
	$m->push('Services');
	$m->push('Contact');
	$m->push('Portfolio');

})->sortBy('id', 'desc');		
?>
```


Sorting the items by passing a closure:

```php
<?php
\Menu::macro('main', function($m){

	$m->push('About')     ->data('order', 2);
	$m->push('Home')      ->data('order', 1);
	$m->push('Services')  ->data('order', 3);
	$m->push('Contact')   ->data('order', 5);
	$m->push('Portfolio') ->data('order', 4);

})->sortBy(function($items) {
	// Your sorting algorithm here...
	
});		
?>
```

The closure takes the items collection as argument.


## Rendering Methods

Several rendering formats are available out of the box:

#### Menu as Unordered List

```html
  {!! $MenuName->asUl() !!}
```

`asUl()` will render your menu in an unordered list. it also takes an optional parameter to define attributes for the `<ul>` tag itself:

```php
{!! $MenuName->asUl( ['class' => 'awesome-ul'] ) !!}
```

Result:

```html
<ul class="awesome-ul">
  <li><a href="http://yourdomain.com">Home</a></li>
  <li><a href="http://yourdomain.com/about">About</a></li>
  <li><a href="http://yourdomain.com/services">Services</a></li>
  <li><a href="http://yourdomain.com/contact">Contact</a></li>
</ul>
```

#### Menu as Ordered List


```php
  {!! $MenuName->asOl() !!}
```

`asOl()` method will render your menu in an ordered list. it also takes an optional parameter to define attributes for the `<ol>` tag itself:

```php
{!! $MenuName->asOl( ['class' => 'awesome-ol'] ) !!}
```

Result:

```html
<ol class="awesome-ol">
  <li><a href="http://yourdomain.com">Home</a></li>
  <li><a href="http://yourdomain.com/about">About</a></li>
  <li><a href="http://yourdomain.com/services">Services</a></li>
  <li><a href="http://yourdomain.com/contact">Contact</a></li>
</ol>
```

#### Menu as Div


```php
  {!! $MenuName->asDiv() !!}
```

`asDiv()` method will render your menu as nested HTML divs. it also takes an optional parameter to define attributes for the parent `<div>` tag itself:

```php
{!! $MenuName->asDiv( ['class' => 'awesome-div'] ) !!}
```

Result:

```html
<div class="awesome-div">
  <div><a href="http://yourdomain.com">Home</a></div>
  <div><a href="http://yourdomain.com/about">About</a></div>
  <div><a href="http://yourdomain.com/services">Services</a></div>
  <div><a href="http://yourdomain.com/contact">Contact</a></div>
</div>
```

#### Menu as Bootstrap 3 Navbar

Laravel Menu provides a parital view out of the box which generates menu items in a bootstrap friendly style which you can **include** in your Bootstrap based navigation bars:

You can access the partial view by `config('llama.menus.view')`.

All you need to do is to include the partial view and pass the root level items to it:

```
...

@include(config('llama.menus.view'), ['items' => $mainNav->roots()])

...

```

This is how your Bootstrap code is going to look like:

```html
<nav class="navbar navbar-default" role="navigation">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">Brand</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">

       @include(config('llama.menus.view'), ['items' => $mainNav->roots()])

      </ul>
      <form class="navbar-form navbar-right" role="search">
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Search">
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
      </form>
      <ul class="nav navbar-nav navbar-right">

        @include(config('llama.menus.view'), ['items' => $loginNav->roots()])

      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
```

## Advanced Usage

As noted earlier you can create your own rendering formats.

#### A Basic Example

If you'd like to render your menu(s) according to your own design, you should create two views.

* `View-1`  This view contains all the HTML codes like `nav` or `ul` or `div` tags wrapping your menu items.
* `View-2`  This view is actually a partial view responsible for rendering menu items (it is going to be included in `View-1`.)


The reason we use two view files here is that `View-2` calls itself recursively to render the items to the deepest level required in multi-level menus.

Let's make this easier with an example:

In our `app/Http/routes.php`:

```php
<?php
\Menu::macro('MyNavBar', function($menu){
  
  $menu->push('Home');
  
   $menu->push('About', route('page.about'));
   
   $menu->about->push('Who are we?', 'who-we-are');
   $menu->about->push('What we do?', 'what-we-do');

  $menu->push('services', 'services');
  $menu->push('Contact',  'contact');
  
});
?>
```

In this example we name View-1 `custom-menu.blade.php` and View-2 `custom-menu-items.blade.php`.

**custom-menu.blade.php**
```php
<nav class="navbar">
  <ul class="horizontal-navbar">
    @include('custom-menu-items', ['items' => $MyNavBar->roots()])
  </ul>
</nav><!--/nav-->
```

**custom-menu-items.blade.php**
```php
@foreach($items as $item)
  <li @if($item->hasChildren()) class="dropdown" @endif>
      <a href="{!! $item->url() !!}">{!! $item->title !!} </a>
      @if($item->hasChildren())
        <ul class="dropdown-menu">
              @include('custom-menu-items', ['items' => $item->children()])
        </ul> 
      @endif
  </li>
@endforeach
```

Let's describe what we did above, In `custom-menus.blade.php` we put whatever HTML boilerplate code we had according to our design, then we included `custom-menu-items.blade.php` and passed the menu items at *root level* to `custom-menu-items.blade.php`:

```php
...
@include('custom-menu-items', ['items' => $menu->roots()])
...
```

In `custom-menu-items.blade.php` we ran a `foreach` loop and called the file recursively in case the current item had any children.

To put the rendered menu in your application template, you can simply include `custom-menu` view in your master layout.

## Configuration

You can adjust the behavior of the menu builder in `config/llama/menus.php` file. Currently it provide a few options out of the box:

* **activate.auto** Automatically activates menu items based on the current URI.
* **activate.parents** Activates the parents of an active item.
* **activate.class** Default CSS class name for active items.
* **activate.element** You can choose the HTML element to which you want to add activation classes (anchor or the wrapping element).
* **inheritance** If you need descendants of an item to inherit meta data from their parents, make sure this option is enabled.
* **restful** Activates RESTful URLS. E.g `resource/slug` will activate item with `resource` url.
* **rest_base** The base URL that all restful resources might be prefixed with.

You're also able to override the default settings for each menu. To override settings for menu, just add the lower-cased menu name as a key in the settings array and add the options you need to override:

```php
<?php
return [
	'default' => [
		'activate'    => [
			'auto' => true,
			'parents' => true,
			'class' => 'active',
			'element'   => 'item', // item|link
		],
		'restful'          => true,
	],
	'your_menu_name' => [
		'activate'    => [
			'auto' => false
		]
	]
];
```



## If You Need Help

Please submit all issues and questions using GitHub issues and I will try to help you.


## License

*llama-laravel/menus* is free software distributed under the terms of the MIT license.
