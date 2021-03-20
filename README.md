# Import map for ES modules

This module generates an import-map script tag to be used with JS modules.<br>
Cross browser support is provided by https://github.com/guybedford/es-module-shims.

## Install

    composer require ubermanu/magento2-es-import-map

## Example

Create a `main.mjs` file into the `Magento_Theme/web/js` dir of your theme.

```js
export const something = 'test';
```

Import this JS module doing the following (in any template file).

```html
<script type="module">
  import { something } from 'Magento_Theme/js/main.mjs';
  console.log(something);
</script>
```
