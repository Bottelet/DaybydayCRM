/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import ElementUI from 'element-ui';
import graphline from './components/Graphline.vue';
import doughnut from './components/Doughnut.vue';
import message from './components/Message.vue';
import 'element-ui/lib/theme-default/index.css';
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.use(ElementUI);

//Vue.component('graphline', require('./components/Graphline.vue'));


$("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
        $(".sidebar-brand").toggleClass("shownone");
    });
        
$(document).ready( function() {
  $('.dropdown-toggle').dropdown();
    $(".list-group-item").click(function(){
        console.log("test");

        if ($('.list-group-item').hasClass('collapsed')) {
            console.log("some text");
            $(this).find('.sidebar-arrow').toggleClass("arrow-up").toggleClass("arrow-down");
        } else {
            $(this).find('.sidebar-arrow').toggleClass("arrow-down").toggleClass("arrow-up");
        }
    });
});

$('.dropdown.keep-open').on({
    "shown.bs.dropdown": function() { this.closable = false; },
    "click":             function() { this.closable = true; },
    "hide.bs.dropdown":  function() { return this.closable; }
});


$("#collapse1").click(function() {
	$(".box-body1").toggleClass("hide");
});


$('.search-select')
  .dropdown({
    direction: 'upward'
  })
;
var app = new Vue({
    el: '#wrapper',
    components: {
      graphline,
      doughnut,
      message
    }
});
