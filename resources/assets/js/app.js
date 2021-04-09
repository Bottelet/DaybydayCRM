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
import createAppointment from './components/AppointmentCreate.vue';
import message from './components/Message.vue';
import search from './components/Search.vue';
import dynamictable from './components/DynamicTable.vue';
import invoiceLineModal from './components/InvoiceLineModal.vue';
import passportclients from './components/passport/Clients.vue';
import passportauthorizedclients from './components/passport/AuthorizedClients.vue';
import passportpersonalaccesstokens from './components/passport/PersonalAccessTokens.vue';
import 'element-ui/lib/theme-default/index.css';
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
$("#menu-toggle").click(function (e) {
    e.preventDefault();
    $("#wrapper").toggleClass("toggled");
    $(".sidebar-brand").toggleClass("shownone");
});

$(document).ready(function () {
    $('.dropdown-toggle').dropdown();
    $(".list-group-item").click(function () {
        if ($('.list-group-item').hasClass('collapsed')) {
            $(this).find('.sidebar-arrow').toggleClass("arrow-up").toggleClass("arrow-down");
        } else {
            $(this).find('.sidebar-arrow').toggleClass("arrow-down").toggleClass("arrow-up");
        }
    });
});


$(document).ready(function () {
    $('body').on('click', '.menu-txt-toggle', function () {
        $("body #wrapper").toggleClass("myNavmenu-icons");
        $("#myNavmenu .panel .list-group-item").addClass("collapsed");
        $("#myNavmenu .collapse").removeClass("in");
        $('#myNavmenu i.ion-chevron-up').removeClass("arrow-down").addClass("arrow-up");
    });
    $('body').on('click', '#myNavmenu .list-group-item', function () {
        $("body #wrapper").removeClass("myNavmenu-icons");
    });

    $("html").click(function (evt) {
        var target = $(evt.target);
        if (target.hasClass("mobile-toggle")) {
            //if($('body #wrapper').hasClass('myNavmenu-icons')) {  } else {
            setTimeout(function () {
                $("body #wrapper").toggleClass("big-menu");
            }, 0);
        } else {

            if (target.id == "myNavmenu")
                return;
            //For descendants of #myNavmenu being clicked, remove this check if you do not want to put constraint on descendants.
            if ($(target).closest('#myNavmenu').length)
                return;
            if ($(target).closest('#mobile-toggle').length) {
                //Do processing of click event here for every element except with id #myNavmenu
                $("body #wrapper").toggleClass("big-menu");
            } else {
                $("body #wrapper").removeClass("big-menu");
            }
        }
    });

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
        createAppointment,
        invoiceLineModal
    },
    //Used for global accessibilty to reload page on events
    methods: {
        reload: function () {
            location.reload();
        }
    }
});
