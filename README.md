# Improvements for Sulu CMS

PHP 8.3+, Sulu 2.6, Symfony 7.4+


![GitHub release](https://flat.badgen.net/github/release/KotaruS/sulu-utils)
![Supports Sulu 3.0 or later](https://flat.badgen.net/badge/Sulu/2.6/52B5C9?icon=php)

## Installation

This bundle requires PHP 8.3 or later. Make sure to have installed [Node 18](https://nodejs.org/en/) or later for building the Sulu administration UI.

1. Open a command console, enter your project directory and run:

```console
composer require kotaru/sulu-utils
```

 
You'll also need to add the bundle in your `config/bundles.php` file:

```php
return [
    //...
    Kotaru\SuluUtils\SuluUtilsBundle::class => ['all' => true],
];
```

2. Register the new routes by adding the following to your `config/routes/sulu_utils.yaml`:

```yaml
sulu_utils:
    resource: "@SuluUtilsBundle/Resources/config/routing.yaml"
```

and also add `config/routes/sulu_utils_admin.yaml`:


```yaml
sulu_utils_api:
    resource: "@SuluUtilsBundle/Resources/config/routing_api.yaml"
    prefix: /admin/api

```

3. Add the file `config/packages/sulu_ai_translator.yaml` with the following configuration:

```yaml
sulu_utils:
    location:
        default_center: [49.7528799, 15.5126953] # default center for the sulu location form field and map_points form field 
        default_zoom: 8 # default zoom for location and map_points
    # available styles for ckeditor, label will be automatically translated
    styles:
        - {
              label: "app_admin.styles.button_special",
              element: "button",
              classes: ["styled__button--special"],
          }
        - {
              label: "app_admin.styles.button_special_inverse",
              element: "button",
              classes: ["styled__button--special-inverse"],
          }

```

Via `locale_mapping` you can map a locale key from your webspace to the according [official DeepL target language](https://developers.deepl.com/docs/resources/supported-languages#target-languages). Use value `null` for languages that should not be translatable.

4. Reference the frontend code by adding the following to your `assets/admin/package.json`:

```json
"dependencies": {
    "sulu-utils-bundle": "file:../../vendor/kotaru/sulu-utils/Resources/js"
}
```

5. Import the frontend code by adding the following to your `assets/admin/app.js`:

```javascript
import "sulu-utils-bundle";
```

6. Install all npm dependencies and build the admin UI ([see all options](https://docs.sulu.io/en/2.6/cookbook/build-admin-frontend.html)):

```bash
cd assets/admin
npm install
npm run build
```

7. **Enjoy the new features of your Sulu installation!**


## Admin changes/additions

### Ckeditor

- Added non-breaking space (`Ctrl/Cmd + Alt + X`)
- Added html button inside the text 
- Added block quote
- Added configurable styles through `sulu_utils.styles` config.

### Other

- Added toggle toolbar action to list views `(TogglerToolbarAction)`.
- Added `/admin/api/check/page/{uuid}` endpoint for checking user permissions to edit this page.
- Added [Redirect controller](#redirect-template).
- Added `/api/translations/{locale}` endpoint for getting all `website` domain translations for use in frontend.
- Added new link type `local`: allows the use of simple `#something` links,
- Added generic form builder for views that need form fields but don't have multiple IDs (e.g. settings pages).

### Redirect template

- create a `config/templates/pages/redirect.xml` file and use the controller
```xml
...
<key>redirect</key>

<view>pages/redirect</view>
<controller>Kotaru\SuluUtils\Controller\Website\RedirectController::indexAction</controller>
<cacheLifetime>604800</cacheLifetime>
...
```

## Form fields

### Modified `location` form field 

```xml 
<!-- example config -->
<property name="maps" type="location" >
  <meta>
    <title lang="cs">Bod na mapě</title>
    <title lang="en">Map Location</title>
  </meta>
  <params>
    <!-- specify value here or %sulu_utils.default_zoom% is used -->
    <param name="default_zoom" value="10" />
    <!-- specify value here or %sulu_utils.default_center% is used -->
    <param name="center" value="42.18399831,15.89313138" />
  </params>
</property>
```

### New `map_points` form field 

Acts the same as `location` field except it allows creating multiple points on a map in one field.

```xml 
<!-- example config -->
<property name="maps" type="map_points" >
  <meta>
    <title lang="cs">Body na mapě</title>
    <title lang="en">Map Points</title>
  </meta>
  <params>
    <!-- specify value here or %sulu_utils.default_zoom% is used -->
    <param name="default_zoom" value="10" />
    <!-- specify value here or %sulu_utils.default_center% is used -->
    <param name="center" value="42.18399831,15.89313138" />
  </params>
</property>
```

### New `text_line_autocomplete` form field 

Acts the same as `text_line` except you can provide a list of autocomplete values.

```xml 
<!-- example config -->
<property name="courses" type="text_line_autocomplete" >
  <meta>
    <title lang="cs">Chod</title>
    <title lang="en">Meal Course</title>
  </meta>
  <params>
    <param name="headline" value="true" />
    <param name="suggestions" type="collection">
        <param name="appetizer" value="Appetizer" />
        <param name="soup" value="Soup" />
        <param name="main" value="Main dish" />
        <param name="desert" value="desert" />
    </param>
  </params>
</property>
```

### New `range` field

```xml 
<!-- example config -->
<property name="size" type="range" multilingual="false">
  <meta>
    <title lang="cs">Velikost</title>
    <title lang="en">Size</title>
  </meta>
  <param name="default_value" value="12" />
    <!-- min value including (default: 0) -->
    <param name="min" value="1" />
    <!-- max value including (default: 10) -->
    <param name="max" value="12" />
    <!-- number of steps between min and max (default: 1) -->
    <param name="step" value="1" />
    <!-- show little ticks for each step -->
    <param name="ticks" value="true" /> 
    <!-- show min and max labels -->
    <param name="titles" value="false" /> 
    <!-- collection of labels for specific steps -->
    <param name="marks" type="collection">
      <param name="1">
        <meta>
          <title lang="cs">1/12</title>
          <title lang="en">1/12</title>
        </meta>
      </param>
      <param name="2">
        <meta>
          <title lang="cs">1/6</title>
          <title lang="en">1/6</title>
        </meta>
      </param>
      ...
    </param>
```

## Twig extensions

### Filters

[`parse_iframes`](#parse_iframes), [`json_decode`](#json_decode), [`format_bytes`](#format_bytes), [`get_contents`](#get_contents), [`set_index_data`](#set_index_data), [`video_url`](#video_url), [`video_id`](#video_id)

#### `parse_iframes`

For setting height on iframes and extra parsing of youtube, vimeo and google maps embeds.

**Usage:**

```twig
{% set iframe = '<iframe src="https://www.google.com/maps/embed?..." width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>' %}
{{ iframe|parse_iframes('200px') }}
Result: 
<iframe src="https://www.google.com/maps/embed?..." width="100%%" height="200px" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" style="border:0;min-height:300px" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
```
#### `json_decode`

Uses PHP json_decode, decodes to array.

**Usage:**

```twig
{% set json = '{ "x": 2, "y": "hey" }' %}
{{ json|json_decode }}
Result: 
{# array(
  x => 2, 
  y => "hey") #}
```

#### `format_bytes`

Humanizes output in bytes to friendly decimal SI multiples (`MB`, `GB`,...).

**Usage:**

```twig
{% set bytes = 1073741824 %}
{{ bytes|format_bytes }}
Result: 
1.07 GB
```

#### `get_contents`

Returns text contents of a Sulu media. Processes the file if it's json or csv to array.

**Usage:**

```twig
{% set file = Sulu\Bundle\MediaBundle\Api\Media instance %}
{{ file|get_contents }}
Result: 
{# Text contents of the file. Useful for json or csv with built in parser #}
```

#### `set_index_data`

Allows easy manipulation of arrays – adding, appending to indexes.

**Usage:**

```twig
{% set arr = [{b: 1}, {x: 2}] %}
{{ arr|set_index_data(1, {y: 3}) }}
Result: 
{# arr: [{b: 1}, {y: 3}] #}
```

#### `video_url`

Extracts the URL part of an iframe.

**Usage:**

```twig
{% set video = '<iframe width="560" height="315" src="https://www.youtube.com/embed/dQw4w9WgXcQ?si=0A7y6tuaBLdqVVwJ" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>' %}
{{ video|video_url }}
Result: 
{# https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?si=0A7y6tuaBLdqVVwJ #}
```
#### `video_id`

Extracts the id part of a youtube or vimeo video.

**Usage:**

```twig
{% set video = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&pp=ygUIcmlja3JvbGw%3D' %}
{{ video|video_id }}
Result: 
{# dQw4w9WgXcQ #}
```

### Functions

[`sulu_resolve_page`](#sulu_resolve_page), [`uuid`](#uuid), [`get_setting`](#get_setting), 

#### `sulu_resolve_page`

**Arguments:**

- `uuid`: page uuid
- `locale`: locale to resolve page for

Return a page url for a give locale

**Usage:**

```twig
{{ sulu_resolve_page('4afdde53-917b-40d2-aed7-3d74c485c39c','en') }}
Result: 
/en/some-path
```

#### `uuid`

**Arguments:**

- `length`: length of the uuid

Return a uuid4 of x length

**Usage:**

```twig
{{ uuid() }}
Result: 
{# uuid4 value of specified length (default: 32), e.g. b9ee2ccce2db41d8a734205372e020c6 #}
```

#### `get_setting`

**Arguments:**

- `settingKey`: the key of the setting that you want to retrieve

Return contents of a setting entity under provided key

**Usage:**

```twig
{{ get_setting('news_homepage') }}
Result: 
{# ['id' => 4afdde53-917b-40d2-aed7-3d74c485c39c] or any value #}
```


### Tests

[`instanceof`](#instanceof), [`number`](#number)

#### `instanceof`
 
Checks if the variable is instance of ...

**Usage:**

```twig
{{ objectX is instanceof App\Objects\Y }}
Result: 
{# true or false #}
```

#### `number`

Checks if the variable is a number.

**Arguments:**

- `strict`: if true, doesn't accept numeric strings.

**Usage:**

```twig
{% set x = '411' %}
{{ x is number }}
{{ x is number(true) }}
Result: 
{# true #}
{# false #}
```