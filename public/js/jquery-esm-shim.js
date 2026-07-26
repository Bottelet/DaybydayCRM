// Re-exports the classic, globally-loaded jQuery (see public/js/jquery.min.js,
// loaded as a non-module <script> before @vite in layouts/master.blade.php) so
// the Vite-built ES module bundle's `import $ from 'jquery'` can resolve it.
// Needed because rollupOptions.output.globals only applies to UMD/IIFE output,
// not the ES module format @vite/laravel-vite-plugin emits.
export default window.jQuery;
