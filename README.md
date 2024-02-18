# Destination.hu

## What is [Destination.hu](https://destination.hu)?

A hobby project I am working on whenever I find a bit of time. 

I decided to create this project when I moved to Szeged and had a hard time to find interesting places to visit/check out other than the usual tourist spots. Whenever I've made a lot of bicycle trips around the city I collected intriguing destinations around my city not just for myself, but to share with others as well. Thus, a database of those places grew, and an UI for it was needed, so Destination.hu was born.

At the moment, **the project is still in its alpha/MVP phase with a lot of proofs of concept and not the best or most stable solutions, and works the best on desktop**. Also, the most destinations are collected around my city.

Some features (some of them are work in progress):

- Categories, tags, filters.
- Pathfinding. Add a bunch of destinations to your route and the system will try to find the most optimized route to follow. You could save and share these routes (or just make them private). The pathfinder currently uses the MapBox API (planning to move to OpenMaps API) and Leaflet.
- Find interesting places in a diameter near you. Are travelling and stopped somewhere? Check out if there's something interesting to see nearby.
- Gallery, commenting, rating system.
- Userpage with the ability to add/edit places and routes.
- ...and a lot more is planned :)

## Installation instructions for developers

Assuming you are using Linux, already have Docker installed, and this is the first time you are setting up the development environment for Destination.

1. Download Wodby's Docker4PHP from here: https://github.com/wodby/docker4php/releases
2. Unpack `docker4php.tar.gz` to a `destination` folder.
3. In `destination/.env`, rename `PROJECT_NAME` and `PROJECT_NAME` to something unique.
```
PROJECT_NAME=destination
PROJECT_BASE_URL=destination.docker.localhost
```

4. So far, PHP 8.0 is used, so in `destination/.env`, comment out `PHP_TAG=8.0-dev-4.18.5`.
```
# Linux (uid 1000 gid 1000)

PHP_TAG=8.0-dev-4.18.5
#PHP_TAG=7.4-dev-4.18.5
#PHP_TAG=7.3-dev-4.18.5
```

5. In `destination/docker-compose.yml`, leave everything as-is if you prefer Nginx for a webserver. I am more used to Apache, so I usually remove the Nginx part and comment out the Apache part. 
   
    - Note that if you assume or know that you already use port `8000:80`, change it to something else under the `traefik` configuration. A quite safe bet is to set it immediately to something quite unique, so you don't have to deal with port conflicts later.
    - If you prefer to use PHPMyAdmin for database administration, comment out the part starting with `pma`.
    - You could add/remove as many services as you want and based what you're working on, but for now, this is enough to set up a version which works out of the box. 
  
6. In the root of the project (`destination/`), issue the following command:
```shell
docker-compose up -d
```

7. Once everything is set up without errors, clone this repo to the `public` subfolder (`destination/public/`).
```shell
git clone https://github.com/csisztaiarnold/destination_new.git public
```

8. Create a `.env` file from `destination/public/.env.example` and set up the environment variables. Set your MapBox API key (`MAPBOX_API_KEY`). 

9. Get into the container.
```shell
docker-compose exec php sh
```

10. Inside the container,`cd` to the `public` folder.
```shell
wodby@php.container:/var/www/html $ cd public
```

11. Install the project with Composer.
```shell
composer update
```

12. Generate an application key.
```shell
php artisan key:generate
```
13. Import the database.
14. Import the images to `destination/public/item_images/`.
15. Go to http://destination.docker.localhost:PORT

## Backend workflow

General rules:

- Use the PSR-2 coding standard and the PSR-4 autoloading standard.
- Line length is not enforced, but in order to keep the code readable, don't make them too long (around 120 characters is preferable).
- Whenever possible, use helper functions instead of facades (it's a personal preference, IMHO, helper functions are more readable in the code): https://laravel.com/docs/8.x/facades#facades-vs-helper-functions

### Models

- The **only** role of the models should be to communicate with the database.
- Make the methods as short as possible and try to avoid native PHP conditionals. Instead, use Eloquent's `when` method: https://laravel.com/docs/8.x/collections#method-when

### Controllers

- If you're writing a controller action and you need an Eloquent query that you'll use only once, it's perfectly fine to write the query directly in the controller.

### Routes

General rules:

- Always name the routes where applicable.
    ```php
    Route::any('my_route', [MyClass::class, 'myController'])->name('my_route_name');
    ```
- As a general rule of thumb, if no other business logic is necessary for a certain route other than returning a collection, then don't create a controller, just a method in the corresponding model, and call it from the `Route` facade in `/routes/web.php`. 
    ```php
    Route::any('my_route', function ($id) {
        return (new App\Models\MyModel)->getData($id);
    })->name('my_route_name');
    ```
- In case of conditionals in the route (e.g. returning different methods based on a user input), use `if` statements up until two conditions, and use the `switch` statement if there are more than two statemenets.
- Group routes which need authentication in the `auth` and `isemailverified` middlewares.
    ```php
    Route::middleware(['auth','isemailverified'])->group(function () {
        // My routes which need authentication go here 
    });
    ```
  
#### Example of a very basic route returning a collection 

The model:
```php
class MyModel extends Model
{
  public function getJson($id)
  {
    return self::where('id', $id)
    ->all();
  }
}
```

The route:
```php
Route::any('my_json_route/{my_variable}', function ($id) {
    return (new App\Models\MyModel)->getJson($id);
});
```

The HTML:
```html
<div id="my-element"></div>
```

The JavaScript:
```javascript
<script>
$.getJSON('my_json_route/1', function (data) {
    $.each(data, function (key, entry) {
        $('#my-element').append(entry.id);
    })
});
</script>
```

## Frontend workflow

### Blade templates

The Blade template files are located in `/resources/views`.

Every model (`Place`, `User`, `Destination`, etc.) or logical chunk (e.g. email related views) should have an own subfolder for the sake of clarity. Just keep it consistent and don't create deeper subfolders than one level.

General rules:

- Filenames should use only alphanumeric characters and dash (-).
- Avoid inline PHP (`@php // code @endphp`) as much as possible. Try to redistribute whatever you can into the controllers, helpers or static functions in the models.

#### Global page title

You could set a global page title from the backend with the `$title` variable. If the `$title` variable is not defined, **Destination** will be used as a title.

```html
<title>{{ $title ?? __('Destination') }}</title>
```

Returning a view from a route:

```php
Route::any('my_route', function () {
  return view('my-view', [
    'title' => 'My Title',
  ]);
})->name('my_route_name');
```

Returning a view from a controller:

```php
public function myController()
{
  return view('my-view', [
    'title' => 'My Title',
  ]);
}
```

### SCSS/CSS assets 

The SCSS/CSS access are located in `/public/css`.

Put 3rd party vendor files in the  `/public/css/vendor` subfolder.

General rules:

- Bootstrap could be used only for the grid. Everything else should be custom.
- SCSS should be used as a CSS-preprocessor.
- CSS files should be always minimized.
- Refer to a CSS asset location in a Blade template with `{{ asset('css/my_css.min.css') }}`
- Avoid inline CSS. In JavaScript, whenever there's more than one CSS property, use `.addClass()` instead of `.css()` and redistribute the class/ID to an SCSS file. 
- IDs and classes should use only alphanumeric characters and dash (-).

#### Global page style

You could set a global page style from the backend with the `$page_class` variable. If the `$page_class` variable is not defined, the `main` class will be used.

```html
<main class="{{ $page_class ?? 'main' }}">
</main>
```

Returning a view from a route:

```php
Route::any('my_route', function () {
  return view('my-view', [
    'page_class' => 'my-custom-page-class',
  ]);
})->name('my_route_name');
```

Returning a view from a controller:

```php
public function myController()
{
  return view('my-view', [
    'page_class' => 'my-custom-page-class',
  ]);
}
```

#### Set up PHPStorm file watcher for minimizing SCSS files (Ubuntu 20.04.1 LTS)

1. Follow the instructions at https://www.jetbrains.com/help/phpstorm/transpiling-compass-to-css.html#compass_installation
2. In case you have installed Ruby from the Software Center and the `sass` command returns a `sass: command not found` error, add the following lint to `~/.bashrc`:
```shell
export PATH="$PATH:$HOME/.gem/bin"
```
3. Go to File > Settings > Tools > File Watchers > + > SSCC. Add `--style=compressed` to the end of the string in the **Arguments** input field.
```shell
$FileName$:$FileNameWithoutExtension$.css --style=compressed
```

### JavaScript assets

The JavaScript access are located in `/public/js`.

Put 3rd party vendor files in the  `/public/js/vendor` subfolder.

General rules:

- Try to avoid JavaScript as much as possible.
- Try to avoid as much 3rd party JavaScript as possible.
- Unless it would really make a huge difference speed-wise, the use of jQuery is strongly encouraged in order to have much simpler, cleaner and more readable code than vanilla JavaScript. Cross-browser compatibility is also important. https://www.arp242.net/jquery.html
- JavaScript files should be always minimized and uglified with UglifyJS. The minimized files should be in the same directory level as the un-minimized ones to avoid unnecessary hunting for files.
- Refer to a JavaScript asset location in a Blade template with `{{ asset('js/my_javascript.min.js') }}`.
  
    ```html
    <script src="{{ asset('js/main.min.js') }}" type="text/javascript"></script>
    ```  
- Put global variables which you feed from the controllers into the header of the `/resources/views/js-global-variables.blade.php` template.
  
    ```html
    <head>
        <script>
            var myVariable = '{{ $value_from_the_controller }}';
            var myOtherVariable = '{{ $value_from_the_controller }}';
        </script>
    </head>
    ```
- Variables should be camelCase.
- Functions should be camelCase.
- Constants should be UPPERCASE.
- If you use PHPStorm, use its default Reformat Code function for code formatting.
- You could use Blade's `{{ }}` or `{!! !!}` echo statements in inline JavaScript, but redistribute as much code as possible to external functions in `/public/assets/js`. Organize them logically.

  ```javascript
  $('#element').text('{{ __('My text') }}'); // This is okay.
  ```

#### Set up PHPStorm file watcher for minimizing/uglifying JavaScript files (Ubuntu 20.04.1 LTS)

1. Install Node JS
```shell
sudo apt install nodejs
```

2. Install npm
```shell
sudo apt install npm
```

3. Follow the instructions at https://www.jetbrains.com/help/phpstorm/minifying-javascript.html#ws_minifying_js_create_file_watcher
