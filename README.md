# Destination.hu

## What is [Destination.hu](https://destination.hu)?

A hobby project I am working on whenever I find a bit of time.

I decided to create this project when I moved to Szeged and had a hard time to find interesting places to visit/check out other than the usual tourist spots. Whenever I've made a lot of bicycle trips around the city I collected intriguing locations around my city not just for myself, but to share with others as well. Thus, a database of those places grew, and an UI for it was needed, so Destination.hu was born.

At the moment, **the project is still in its alpha/MVP phase with a lot of proofs of concept and not the best or most stable solutions, and works the best on desktop**. Also, the most destinations are collected only around my city.

Some features (some of them are work in progress):

- Categories, tags, filters.
- Pathfinding. Add a bunch of destinations to your route and the system will try to find the most optimized route to follow. You could save and share these routes (or just make them private). The pathfinder currently uses the MapBox API (planning to move to OpenMaps API) and Leaflet.
- Find interesting places in a diameter near you. Are you travelling and stopped somewhere? Check out if there's something interesting to see nearby.
- Gallery, commenting, rating system.
- Userpage with the ability to add/edit places and routes.

## Installation instructions for developers

Assuming you are using Linux, already have Docker installed, and this is the first time you are setting up the development environment for Destination.

1. Clone the project

```shell
git clone git@github.com:csisztaiarnold/destination.git
```


2. Create a `.env` file from `.env.example` and set up the environment variables. Set your MapBox API key (
   `MAPBOX_API_KEY`).


3. In the root of the project, issue the following command:

```shell
make up
```

4. Get into the container.

```shell
make shell
```

5. Create a `cache`, `sessions` and `views` folder in the `storage/framework` folder. Also create a `data` folder in the
   `storage/framework/cache`. Execute `chmod -R 775 framework`.


6. Install the project with Composer.

```shell
composer install
```

7. Generate an application key and clear the cache.

```shell
php artisan key:generate
php artisan cache:clear
```

8. Import the database.


9. Import the images to `destination/public/item_images/`.


10. Go to http://destination.docker.localhost:APP_PORT (set the APP_PORT in the `.env` file).
