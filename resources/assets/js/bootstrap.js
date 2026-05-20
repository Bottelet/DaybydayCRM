/**
 * jQuery is loaded as a classic blocking script in master.blade.php before this module.
 * We import it here so bootstrap-sass can use it during module initialization.
 * We only set it globally if it isn't already set (to avoid overwriting the classic
 * jQuery which has all the legacy plugins attached).
 */
import $ from 'jquery';
if (typeof window.jQuery === 'undefined') {
    window.jQuery = window.$ = $;
}

import _ from 'lodash';
window._ = _;

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

import 'bootstrap-sass';
import selectpicker from 'bootstrap-select';
window.selectpicker = selectpicker;
/**
 * Vue is a modern JavaScript library for building interactive web interfaces
 * using reactive data binding and reusable components. Vue's API is clean
 * and simple, leaving you to focus on building your next great project.
 */

import Vue from 'vue';
window.Vue = Vue;
import VueResource from 'vue-resource';
Vue.use(VueResource);

import axios from 'axios';
window.axios = axios;

// Set CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Set the base URL for axios from the global DayByDay configuration.
 * This ensures that all axios requests work correctly when the app
 * is installed in a subdirectory.
 *
 * Note: DayByDay is defined in master.blade.php before this script loads,
 * so it will always be available. The typeof check is defensive programming.
 */
if (typeof DayByDay !== 'undefined' && DayByDay.baseUrl) {
    window.axios.defaults.baseURL = DayByDay.baseUrl;
}

/**
 * We'll register a HTTP interceptor to attach the "CSRF" header to each of
 * the outgoing requests issued by this application. The CSRF middleware
 * included with Laravel will automatically verify the header's value.
 */

Vue.http.interceptors.push((request, next) => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        request.headers.set('X-CSRF-TOKEN', csrfToken);
    }
    next();
});

/**
 * Chart.js for charts
 */

import 'chart.js';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from "laravel-echo"

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: 'your-pusher-key'
// });
