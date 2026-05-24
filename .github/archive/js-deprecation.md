[1/4] Resolving packages...
warning element-ui > async-validator > babel-runtime > core-js@2.6.12: core-js@<3.23.3 is no longer maintained and not recommended for usage due to the number of issues. Because of the V8 engine whims, feature detection in old core-js versions could cause a slowdown up to 100x even if nothing is polyfilled. Some versions have web compatibility issues. Please, upgrade your dependencies to the actual version of core-js.
warning natives@1.1.6: This module relies on Node.js's internals and will break at some point. Do not use it, and update to graceful-fs@4.x.
warning vue@2.7.16: Vue 2 has reached EOL and is no longer actively maintained. See https://v2.vuejs.org/eol/ for more details.
warning vue-loader > @vue/component-compiler-utils > consolidate@0.15.1: Please upgrade to consolidate v1.0.0+ as it has been modernized with several long-awaited fixes implemented. Maintenance is supported by Forward Email at https://forwardemail.net ; follow/watch https://github.com/ladjs/consolidate for updates and release changelog
[2/4] Fetching packages...
[3/4] Linking dependencies...
warning " > bootstrap-select@1.13.18" has unmet peer dependency "bootstrap@>=3.0.0".
warning " > vis-timeline@7.7.4" has unmet peer dependency "@egjs/hammerjs@^2.0.0".
warning " > vis-timeline@7.7.4" has unmet peer dependency "component-emitter@^1.3.0".
warning " > vis-timeline@7.7.4" has unmet peer dependency "keycharm@^0.2.0 || ^0.3.0 || ^0.4.0".
warning " > vis-timeline@7.7.4" has unmet peer dependency "moment@^2.24.0".
warning " > vis-timeline@7.7.4" has unmet peer dependency "propagating-hammerjs@^1.4.0 || ^2.0.0".
warning " > vis-timeline@7.7.4" has unmet peer dependency "uuid@^3.4.0 || ^7.0.0 || ^8.0.0 || ^9.0.0 || ^10.0.0 || ^11.0.0".
warning " > vis-timeline@7.7.4" has unmet peer dependency "vis-data@^6.3.0 || ^7.0.0".
warning " > vis-timeline@7.7.4" has unmet peer dependency "vis-util@^5.0.1".
warning " > vis-timeline@7.7.4" has unmet peer dependency "xss@^1.0.0".
warning " > laravel-vite-plugin@0.8.1" has incorrect peer dependency "vite@^3.0.0 || ^4.0.0".
warning " > style-loader@4.0.0" has unmet peer dependency "webpack@^5.27.0".
warning " > vue-loader@15.11.1" has unmet peer dependency "css-loader@*".
warning " > vue-loader@15.11.1" has unmet peer dependency "webpack@^3.0.0 || ^4.1.0 || ^5.0.0-0".
[4/4] Building fresh packages...
success Saved lockfile.
Done in 6.35s.
yarn run v1.22.22
$ vite build
The CJS build of Vite's Node API is deprecated. See https://vite.dev/guide/troubleshooting.html#vite-cjs-node-api-deprecated for more details.
vite v5.4.21 building for production...
transforming (204) node_modules/async-validator/es/rule/required.jsDeprecation Warning [import]: Sass @import rules are deprecated and will be removed in Dart Sass 3.0.0.

More info and automated migrator: https://sass-lang.com/d/import

╷
8 │ @import 'components/base';
│         ^^^^^^^^^^^^^^^^^
╵
resources/assets/sass/app.scss 8:9  root stylesheet

Deprecation Warning [import]: Sass @import rules are deprecated and will be removed in Dart Sass 3.0.0.

More info and automated migrator: https://sass-lang.com/d/import

╷
9 │ @import 'components/notifications';
│         ^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
resources/assets/sass/app.scss 9:9  root stylesheet

Deprecation Warning [import]: Sass @import rules are deprecated and will be removed in Dart Sass 3.0.0.

More info and automated migrator: https://sass-lang.com/d/import

╷
10 │ @import 'components/topcontactinfo';
│         ^^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
resources/assets/sass/app.scss 10:9  root stylesheet

Deprecation Warning [import]: Sass @import rules are deprecated and will be removed in Dart Sass 3.0.0.

More info and automated migrator: https://sass-lang.com/d/import

╷
11 │ @import 'components/sidebar';
│         ^^^^^^^^^^^^^^^^^^^^
╵
resources/assets/sass/app.scss 11:9  root stylesheet

Deprecation Warning [import]: Sass @import rules are deprecated and will be removed in Dart Sass 3.0.0.

More info and automated migrator: https://sass-lang.com/d/import

╷
12 │ @import 'components/task-sidebar';
│         ^^^^^^^^^^^^^^^^^^^^^^^^^
╵
resources/assets/sass/app.scss 12:9  root stylesheet

Deprecation Warning [import]: Sass @import rules are deprecated and will be removed in Dart Sass 3.0.0.

More info and automated migrator: https://sass-lang.com/d/import

╷
4 │ @import "bootstrap-sass/assets/stylesheets/bootstrap";
│         ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
resources/assets/sass/vendor.scss 4:9  root stylesheet

Deprecation Warning [global-builtin]: Global built-in functions are deprecated and will be removed in Dart Sass 3.0.0.
Use color.mix instead.

More info and automated migrator: https://sass-lang.com/d/import

╷
46 │     @return mix($table-shade, $color, $percent);
│             ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
resources/assets/sass/components/datatables.scss 46:13   shade()
resources/assets/sass/components/datatables.scss 180:31  @import
resources/assets/sass/app.scss 14:9                      root stylesheet

Deprecation Warning [global-builtin]: Global built-in functions are deprecated and will be removed in Dart Sass 3.0.0.
Use color.adjust instead.

More info and automated migrator: https://sass-lang.com/d/import

    ╷
439 │                 border: 1px solid darken( $table-paging-button-active, 27% );
│                                   ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
resources/assets/sass/components/datatables.scss 439:35  @import
resources/assets/sass/app.scss 14:9                      root stylesheet

Deprecation Warning [color-functions]: darken() is deprecated. Suggestions:

color.scale($color, $lightness: -31.2954545455%)
color.adjust($color, $lightness: -27%)

More info: https://sass-lang.com/d/color-functions

    ╷
439 │                 border: 1px solid darken( $table-paging-button-active, 27% );
│                                   ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
resources/assets/sass/components/datatables.scss 439:35  @import
resources/assets/sass/app.scss 14:9                      root stylesheet

Deprecation Warning [global-builtin]: Global built-in functions are deprecated and will be removed in Dart Sass 3.0.0.
Use color.adjust instead.

More info and automated migrator: https://sass-lang.com/d/import

    ╷
441 │                     lighten($table-paging-button-active, 28%),
│                     ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
resources/assets/sass/components/datatables.scss 441:21  @import
resources/assets/sass/app.scss 14:9                      root stylesheet

Deprecation Warning [color-functions]: lighten() is deprecated. Suggestions:

color.scale($color, $lightness: 100%)
color.adjust($color, $lightness: 28%)

More info: https://sass-lang.com/d/color-functions

    ╷
441 │                     lighten($table-paging-button-active, 28%),
│                     ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
resources/assets/sass/components/datatables.scss 441:21  @import
resources/assets/sass/app.scss 14:9                      root stylesheet

Deprecation Warning [global-builtin]: Global built-in functions are deprecated and will be removed in Dart Sass 3.0.0.
Use color.adjust instead.

More info and automated migrator: https://sass-lang.com/d/import

    ╷
460 │                     lighten($table-paging-button-hover, 28%),
│                     ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
resources/assets/sass/components/datatables.scss 460:21  @import
resources/assets/sass/app.scss 14:9                      root stylesheet

Deprecation Warning [color-functions]: lighten() is deprecated. Suggestions:

color.scale($color, $lightness: 30%)
color.adjust($color, $lightness: 28%)

More info: https://sass-lang.com/d/color-functions

    ╷
460 │                     lighten($table-paging-button-hover, 28%),
│                     ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
resources/assets/sass/components/datatables.scss 460:21  @import
resources/assets/sass/app.scss 14:9                      root stylesheet

Deprecation Warning [global-builtin]: Global built-in functions are deprecated and will be removed in Dart Sass 3.0.0.
Use color.adjust instead.

More info and automated migrator: https://sass-lang.com/d/import

    ╷
468 │                     lighten($table-paging-button-hover, 10%),
│                     ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
resources/assets/sass/components/datatables.scss 468:21  @import
resources/assets/sass/app.scss 14:9                      root stylesheet

Deprecation Warning [color-functions]: lighten() is deprecated. Suggestions:

color.scale($color, $lightness: 10.7142857143%)
color.adjust($color, $lightness: 10%)

More info: https://sass-lang.com/d/color-functions

    ╷
468 │                     lighten($table-paging-button-hover, 10%),
│                     ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
resources/assets/sass/components/datatables.scss 468:21  @import
resources/assets/sass/app.scss 14:9                      root stylesheet

Deprecation Warning [color-functions]: darken() is deprecated. Suggestions:

color.scale($color, $lightness: -30%)
color.adjust($color, $lightness: -2%)

More info: https://sass-lang.com/d/color-functions

    ╷
469 │                     darken($table-paging-button-hover, 2%)
│                     ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
resources/assets/sass/components/datatables.scss 469:21  @import
resources/assets/sass/app.scss 14:9                      root stylesheet

Warning: 9 repetitive deprecation warnings omitted.
Run in verbose mode to see all warnings.


/images/undraw_upload.svg referenced in /images/undraw_upload.svg didn't resolve at build time, it will remain unchanged to be resolved at runtime
transforming (385) resources/assets/sass/app.scssDeprecation Warning [import]: Sass @import rules are deprecated and will be removed in Dart Sass 3.0.0.

More info and automated migrator: https://sass-lang.com/d/import

╷
8 │ @import "bootstrap/variables";
│         ^^^^^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 8:9  @import
resources/assets/sass/vendor.scss 4:9                               root stylesheet

Deprecation Warning [import]: Sass @import rules are deprecated and will be removed in Dart Sass 3.0.0.

More info and automated migrator: https://sass-lang.com/d/import

╷
9 │ @import "bootstrap/mixins";
│         ^^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 9:9  @import
resources/assets/sass/vendor.scss 4:9                               root stylesheet

Deprecation Warning [import]: Sass @import rules are deprecated and will be removed in Dart Sass 3.0.0.

More info and automated migrator: https://sass-lang.com/d/import

╷
12 │ @import "bootstrap/normalize";
│         ^^^^^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 12:9  @import
resources/assets/sass/vendor.scss 4:9                                root stylesheet

Deprecation Warning [import]: Sass @import rules are deprecated and will be removed in Dart Sass 3.0.0.

More info and automated migrator: https://sass-lang.com/d/import

╷
13 │ @import "bootstrap/print";
│         ^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 13:9  @import
resources/assets/sass/vendor.scss 4:9                                root stylesheet

Deprecation Warning [if-function]: The Sass if() syntax is deprecated in favor of the modern CSS syntax.

Suggestion: if(sass($bootstrap-sass-asset-helper): "bootstrap/"; else: "../fonts/bootstrap/")

More info: https://sass-lang.com/d/if-function

╷
84 │ $icon-font-path: if($bootstrap-sass-asset-helper, "bootstrap/", "../fonts/bootstrap/") !default;
│                  ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/bootstrap/_variables.scss 84:18  @import
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 8:9              @import
resources/assets/sass/vendor.scss 4:9                                           root stylesheet

Deprecation Warning [global-builtin]: Global built-in functions are deprecated and will be removed in Dart Sass 3.0.0.
Use color.adjust instead.

More info and automated migrator: https://sass-lang.com/d/import

╷
13 │ $gray-darker:            lighten($gray-base, 13.5%) !default; // #222
│                          ^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/bootstrap/_variables.scss 13:26  @import
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 8:9              @import
resources/assets/sass/vendor.scss 4:9                                           root stylesheet

Deprecation Warning [color-functions]: lighten() is deprecated. Suggestions:

color.scale($color, $lightness: 13.5%)
color.adjust($color, $lightness: 13.5%)

More info: https://sass-lang.com/d/color-functions

╷
13 │ $gray-darker:            lighten($gray-base, 13.5%) !default; // #222
│                          ^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/bootstrap/_variables.scss 13:26  @import
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 8:9              @import
resources/assets/sass/vendor.scss 4:9                                           root stylesheet

Deprecation Warning [global-builtin]: Global built-in functions are deprecated and will be removed in Dart Sass 3.0.0.
Use color.adjust instead.

More info and automated migrator: https://sass-lang.com/d/import

╷
14 │ $gray-dark:              lighten($gray-base, 20%) !default;   // #333
│                          ^^^^^^^^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/bootstrap/_variables.scss 14:26  @import
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 8:9              @import
resources/assets/sass/vendor.scss 4:9                                           root stylesheet

Deprecation Warning [color-functions]: lighten() is deprecated. Suggestions:

color.scale($color, $lightness: 20%)
color.adjust($color, $lightness: 20%)

More info: https://sass-lang.com/d/color-functions

╷
14 │ $gray-dark:              lighten($gray-base, 20%) !default;   // #333
│                          ^^^^^^^^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/bootstrap/_variables.scss 14:26  @import
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 8:9              @import
resources/assets/sass/vendor.scss 4:9                                           root stylesheet

Deprecation Warning [global-builtin]: Global built-in functions are deprecated and will be removed in Dart Sass 3.0.0.
Use color.adjust instead.

More info and automated migrator: https://sass-lang.com/d/import

╷
15 │ $gray:                   lighten($gray-base, 33.5%) !default; // #555
│                          ^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/bootstrap/_variables.scss 15:26  @import
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 8:9              @import
resources/assets/sass/vendor.scss 4:9                                           root stylesheet

Deprecation Warning [color-functions]: lighten() is deprecated. Suggestions:

color.scale($color, $lightness: 33.5%)
color.adjust($color, $lightness: 33.5%)

More info: https://sass-lang.com/d/color-functions

╷
15 │ $gray:                   lighten($gray-base, 33.5%) !default; // #555
│                          ^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/bootstrap/_variables.scss 15:26  @import
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 8:9              @import
resources/assets/sass/vendor.scss 4:9                                           root stylesheet

Deprecation Warning [global-builtin]: Global built-in functions are deprecated and will be removed in Dart Sass 3.0.0.
Use color.adjust instead.

More info and automated migrator: https://sass-lang.com/d/import

╷
16 │ $gray-light:             lighten($gray-base, 46.7%) !default; // #777
│                          ^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/bootstrap/_variables.scss 16:26  @import
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 8:9              @import
resources/assets/sass/vendor.scss 4:9                                           root stylesheet

Deprecation Warning [color-functions]: lighten() is deprecated. Suggestions:

color.scale($color, $lightness: 46.7%)
color.adjust($color, $lightness: 46.7%)

More info: https://sass-lang.com/d/color-functions

╷
16 │ $gray-light:             lighten($gray-base, 46.7%) !default; // #777
│                          ^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/bootstrap/_variables.scss 16:26  @import
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 8:9              @import
resources/assets/sass/vendor.scss 4:9                                           root stylesheet

Deprecation Warning [global-builtin]: Global built-in functions are deprecated and will be removed in Dart Sass 3.0.0.
Use color.adjust instead.

More info and automated migrator: https://sass-lang.com/d/import

╷
17 │ $gray-lighter:           lighten($gray-base, 93.5%) !default; // #eee
│                          ^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/bootstrap/_variables.scss 17:26  @import
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 8:9              @import
resources/assets/sass/vendor.scss 4:9                                           root stylesheet

Deprecation Warning [color-functions]: lighten() is deprecated. Suggestions:

color.scale($color, $lightness: 93.5%)
color.adjust($color, $lightness: 93.5%)

More info: https://sass-lang.com/d/color-functions

╷
17 │ $gray-lighter:           lighten($gray-base, 93.5%) !default; // #eee
│                          ^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/bootstrap/_variables.scss 17:26  @import
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 8:9              @import
resources/assets/sass/vendor.scss 4:9                                           root stylesheet

Deprecation Warning [if-function]: The Sass if() syntax is deprecated in favor of the modern CSS syntax.

Suggestion: if(sass($bootstrap-sass-asset-helper): twbs-image-path("#{$file-1x}"); else: "#{$file-1x}")

More info: https://sass-lang.com/d/if-function

╷
16 │   background-image: url(if($bootstrap-sass-asset-helper, twbs-image-path("#{$file-1x}"), "#{$file-1x}"));
│                         ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/bootstrap/mixins/_image.scss 16:25  @import
node_modules/bootstrap-sass/assets/stylesheets/bootstrap/_mixins.scss 7:9          @import
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 9:9                 @import
resources/assets/sass/vendor.scss 4:9                                              root stylesheet

Deprecation Warning [if-function]: The Sass if() syntax is deprecated in favor of the modern CSS syntax.

Suggestion: if(sass($bootstrap-sass-asset-helper): twbs-image-path("#{$file-2x}"); else: "#{$file-2x}")

More info: https://sass-lang.com/d/if-function

╷
25 │     background-image: url(if($bootstrap-sass-asset-helper, twbs-image-path("#{$file-2x}"), "#{$file-2x}"));
│                           ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/bootstrap/mixins/_image.scss 25:27  @import
node_modules/bootstrap-sass/assets/stylesheets/bootstrap/_mixins.scss 7:9          @import
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 9:9                 @import
resources/assets/sass/vendor.scss 4:9                                              root stylesheet

Deprecation Warning [if-function]: The Sass if() syntax is deprecated in favor of the modern CSS syntax.

Suggestion: if(sass($bootstrap-sass-asset-helper): twbs-font-path("#{$icon-font-path}#{$icon-font-name}.eot"); else: "#{$icon-font-path}#{$icon-font-name}.eot")

More info: https://sass-lang.com/d/if-function

╷
14 │     src: url(if($bootstrap-sass-asset-helper, twbs-font-path("#{$icon-font-path}#{$icon-font-name}.eot"), "#{$icon-font-path}#{$icon-font-name}.eot"));
│              ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/bootstrap/_glyphicons.scss 14:14  @import
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 14:9              @import
resources/assets/sass/vendor.scss 4:9                                            root stylesheet

Deprecation Warning [if-function]: The Sass if() syntax is deprecated in favor of the modern CSS syntax.

Suggestion: if(sass($bootstrap-sass-asset-helper): twbs-font-path("#{$icon-font-path}#{$icon-font-name}.eot?#iefix"); else: "#{$icon-font-path}#{$icon-font-name}.eot?#iefix")

More info: https://sass-lang.com/d/if-function

╷
15 │     src: url(if($bootstrap-sass-asset-helper, twbs-font-path("#{$icon-font-path}#{$icon-font-name}.eot?#iefix"), "#{$icon-font-path}#{$icon-font-name}.eot?#iefix")) format("embedded-opentype"),
│              ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
╵
node_modules/bootstrap-sass/assets/stylesheets/bootstrap/_glyphicons.scss 15:14  @import
node_modules/bootstrap-sass/assets/stylesheets/_bootstrap.scss 14:9              @import
resources/assets/sass/vendor.scss 4:9                                            root stylesheet

Warning: 267 repetitive deprecation warnings omitted.
Run in verbose mode to see all warnings.


/fonts/bootstrap/glyphicons-halflings-regular.eot referenced in /fonts/bootstrap/glyphicons-halflings-regular.eot didn't resolve at build time, it will remain unchanged to be resolved at runtime

/fonts/bootstrap/glyphicons-halflings-regular.eot?#iefix referenced in /fonts/bootstrap/glyphicons-halflings-regular.eot? didn't resolve at build time, it will remain unchanged to be resolved at runtime

/fonts/bootstrap/glyphicons-halflings-regular.woff2 referenced in /fonts/bootstrap/glyphicons-halflings-regular.woff2 didn't resolve at build time, it will remain unchanged to be resolved at runtime

/fonts/bootstrap/glyphicons-halflings-regular.woff referenced in /fonts/bootstrap/glyphicons-halflings-regular.woff didn't resolve at build time, it will remain unchanged to be resolved at runtime

/fonts/bootstrap/glyphicons-halflings-regular.ttf referenced in /fonts/bootstrap/glyphicons-halflings-regular.ttf didn't resolve at build time, it will remain unchanged to be resolved at runtime

/fonts/bootstrap/glyphicons-halflings-regular.svg#glyphicons_halflingsregular referenced in /fonts/bootstrap/glyphicons-halflings-regular.svg didn't resolve at build time, it will remain unchanged to be resolved at runtime
✓ 387 modules transformed.
warnings when minifying css:
▲ [WARNING] Expected identifier but found "*" [css-syntax-error]

    <stdin>:1255:2:
      1255 │   *cursor: hand;
           ╵   ^


▲ [WARNING] Expected identifier but found "*" [css-syntax-error]

    <stdin>:1475:2:
      1475 │   *zoom: 1;
           ╵   ^


▲ [WARNING] Expected identifier but found "*" [css-syntax-error]

    <stdin>:1506:2:
      1506 │   *cursor: hand;
           ╵   ^


▲ [WARNING] Expected identifier but found "*" [css-syntax-error]

    <stdin>:1584:2:
      1584 │   *margin-top: -1px;
           ╵   ^


✓ Manifest moved to public/build/manifest.json
public/build/.vite/manifest.json                     1.03 kB │ gzip:   0.30 kB
public/build/assets/element-icons-B-tDfklg.woff     28.20 kB
public/build/assets/element-icons-_lZGOqcG.ttf      55.96 kB
public/build/assets/app-BRtO9Pw1.css                42.08 kB │ gzip:   8.14 kB
public/build/assets/vendor-DI5DYLod.css            120.46 kB │ gzip:  20.22 kB
public/build/assets/app-BoadqyH6.css               251.42 kB │ gzip:  39.65 kB
public/build/assets/app-D6u3Zamp.js              2,115.07 kB │ gzip: 621.52 kB

(!) Some chunks are larger than 500 kB after minification. Consider:
- Using dynamic import() to code-split the application
- Use build.rollupOptions.output.manualChunks to improve chunking: https://rollupjs.org/configuration-options/#output-manualchunks
- Adjust chunk size limit for this warning via build.chunkSizeWarningLimit.
  ✓ built in 5.24s
  Done in 5.51s.
  ivpldock@4451e9bbf2e0:/var/www/projects/day$ 
