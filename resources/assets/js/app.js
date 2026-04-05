import $ from 'jquery';
window.$ = window.jQuery = $;

/**n_xxx
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import ElementUI from 'element-ui';
import graphline from './components/Graphline.vue';
import doughnut from './components/Doughnut.vue';
import calendar from './components/Calendar.vue';
import message from './components/Message.vue';
import search from './components/Search.vue';
import dynamictable from './components/DynamicTable.vue';
import invoiceLineModal from './components/InvoiceLineModal.vue';
import passportclients from './components/passport/Clients.vue';
import passportauthorizedclients from './components/passport/AuthorizedClients.vue';
import passportpersonalaccesstokens from './components/passport/PersonalAccessTokens.vue';
import 'element-ui/lib/theme-chalk/index.css';
import VueCurrencyFilter from 'vue-currency-filter'
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
Vue.use(ElementUI);
Vue.use(VueCurrencyFilter)
//Vue.component('graphline', require('./components/Graphline.vue'));
$('.dropdown.keep-open').on({
    "shown.bs.dropdown": function () {
        this.closable = false;
    },
    "click": function () {
        this.closable = true;
    },
    "hide.bs.dropdown": function () {
        return this.closable;
    }
});


$("#collapse1").click(function () {
    $(".box-body1").toggleClass("hide");
});

//Sidebar menu
$(document).ready(function () {
    // Desktop Nav Collapse
    $('body').on('click', '#menu-toggle, .menu-txt-toggle', function () {
        $("#wrapper").toggleClass("myNavmenu-icons");
        $("#myNavmenu .panel .list-group-item").addClass("collapsed");
        $("#myNavmenu .collapse").removeClass("in");
        $('#myNavmenu i.sidebar-arrow').removeClass("arrow-down").addClass("arrow-side");
    });

    // Mobile Nav Toggle
    $('body').on('click', '#mobile-toggle', function (e) {
        e.stopPropagation();
        $("#wrapper").toggleClass("big-menu");
    });

    // Close mobile menu when clicking outside
    $(document).click(function (e) {
        if (!$(e.target).closest('#myNavmenu, #mobile-toggle').length) {
            $("#wrapper").removeClass("big-menu");
        }
    });
});

$(document).ready(function () {
    $('.dropdown-toggle').dropdown();
    $('.dropdown-toggle').click(function (e) {
        var href = $(this).attr('href');
        if (href && href !== '#' && !href.startsWith('#')) {
            var $parent = $(this).closest('.dropdown');
            if ($parent.length === 0) {
                $parent = $(this).parent();
            }

            if (!$parent.hasClass('open')) {
                // If the dropdown is not open, let Bootstrap handle opening it.
                return true;
            } else {
                // If it's already open, then navigate.
                window.location.href = href;
            }
        }
    });
    $(".list-group-item[data-toggle='collapse']").click(function (e) {
        var target = $(this).attr('data-target') || $(this).attr('href');
        if (target && target.startsWith('#')) {
            $(target).collapse('toggle');
        }
    });
});


$(document).ready(function () {
    $('.view-offer-btn, #view-original-offer').on('click', function (e) {
        var offerExternalId = $(this).data('offer-external_id')
        var vuecomp = Vue.extend(invoiceLineModal);
        var component = new vuecomp({
            propsData: {
                external_id: offerExternalId,
                type: "offer",
                editMode: false
            }
        }).$mount()
        $('.view-offer-inner').empty().append(component.$el)
        $('#view-offer').modal('show');
    });

    $('.edit-offer-btn').on('click', function (e) {
        var offerExternalId = $(this).data('offer-external_id')
        var vuecomp = Vue.extend(invoiceLineModal);
        var component = new vuecomp({
            propsData: {
                external_id: offerExternalId,
                type: "offer"
            }
        }).$mount()
        $('.view-offer-inner').empty().append(component.$el)
        $('#view-offer').modal('show');
    });

    
});



$(window).on('resize', function () {
    var win = $(this); //this = window
    if (win.width() >= 991) {
        $("body #wrapper").removeClass("big-menu");
        //$("body .navbar-default .navbar-toggle").trigger("click");
    }
});

$('.search-select')
    .dropdown({
        direction: 'upward'
    })
;

Vue.prototype.trans = (key) => {
    return _.get(window.trans, key, key);
};

var app = new Vue({
    el: '#wrapper',
    components: {
        graphline,
        doughnut,
        message,
        passportclients,
        passportauthorizedclients,
        passportpersonalaccesstokens,
        search,
        dynamictable,
        calendar,
        invoiceLineModal
    },
    //Used for global accessibilty to reload page on events
    methods: {
        reload: function () {
            location.reload();
        }
    }
});
