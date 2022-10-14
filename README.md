# Magento 2 - Import map for ES modules

This module generates an import-map script tag to be used with JS modules.<br>
Cross browser support is provided by https://github.com/guybedford/es-module-shims.

## Install

    composer require ubermanu/magento2-es-import-map

## Example

Create a `example.mjs` file into your module `view/frontend/web/mjs` folder:

```js
// app/code/Vendor/Module/view/frontend/web/mjs/example.mjs
export const something = 'Hello world';
```

Add the following to any `*.phtml` template file:

```html
<script type="module">
  import { something } from '/Vendor_Module/mjs/example.mjs';
  alert(something);
</script>
```

## Named imports

To use named imports, create an `import-map.json` file into the view dir of your module (or theme):

```jsonld
// app/code/Vendor/Module/view/frontend/import-map.json
{
    "imports": {
        "jquery": "https://cdn.skypack.dev/jquery"
    }
}
```
