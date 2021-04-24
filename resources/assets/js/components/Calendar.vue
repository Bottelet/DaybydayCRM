<template>
    <div class="wrapper">
        <div class="utility-bar">
            <ul class="time-dropwdown">
                <li class="float-left"><a href="/dashboard"><button class="btn btn-clean back-button"><i class="fa fa-chevron-circle-left"></i></button></a></li>
                <li class="float-left" style="float:right">
                    <create-appointment v-on:created-appointment="createNewAppointment"></create-appointment>
                </li>
                <li class="float-left" style="float:right">
                    <div class="dropdown">
                        <button class="btn btn-brand dropdown-toggle toggle-design" type="button" data-toggle="dropdown">
                            <span v-if="scale=='fourteen_days'">{{trans('14 days')}}</span>
                            <span v-else>{{trans(scale)}}</span>
                            <span class="caret" style="float: right; margin: 8px 0;"></span></button>
                        <ul class="dropdown-menu">
                            <li><a href="#" @click="setScale('day')">{{trans('Day')}}</a></li>
                            <li><a href="#" @click="setScale('week')">{{trans('Week')}}</a></li>
                            <li><a href="#" @click="setScale('fourteen_days')">{{trans('14 days')}}</a></li>
                            <li class="divider"></li>
                            <li>
                                <a href="#" @click="toggleWeekend">
                                    <i class="fa fa-check" v-if="showWeekends" style="margin-right: 10px; font-size: 11px; margin-bottom: 5px;"></i>
                                    {{trans('Show weekends')}}
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="float-left" style="float:right; margin-right: 2em;">
                    <span style="vertical-align: sub;">{{trans('Business hours only')}}</span>
                    <label class="switch">
                        <input type="checkbox" v-model="showBusinessHours" @change="toggleBusinessHours">
                        <span class="slider round"></span>
                    </label>
                </li>
            </ul>
        </div>
        <div id="accessDeniedError" class="alert alert-danger" style="display:none; opacity: 0;">
            <strong>{{trans('Access Denied')}}</strong> {{trans('You do not have the correct permissions for this action')}}
        </div>

        <div id="visualization"></div>
    </div>
</template>

<script>
    import { Timeline } from 'vis-timeline/standalone';
    import moment from 'moment';
    import Message from './Message.vue'
    import CreateAppointment from './AppointmentCreate.vue'

    export default {
        data() {
            return {
                events:
                    [
                        "click",
                        "contextmenu",
                        "doubleClick",
                        "drop",
                        "groupDragged",
                        "changed",
                        "rangechange",
                        "rangechanged",
                        "select",
                        "timechange",
                        "timechanged",
                        "onMove"
                    ],
                scale: 'day',
                showWeekends:true,
                showBusinessHours:true,
                accessDenied: false,
                items: [],
                groups: [],
                options: {
                    orientation: "top",
                    itemsAlwaysDraggable:true,
                    timeAxis: {scale: 'hour', step: 1, },
                    minHeight:'500px',
                    zoomMax: 280000000,
                    zoomMin: 28000000,
                    start: moment().startOf('day'),
                    end: moment().add(1, 'days').startOf('day'),
                    hiddenDates: [],
                    editable: {
                        add: false,         // add new items by double tapping
                        updateTime: true,  // drag items horizontally
                        updateGroup: true, // drag items from one group to another
                        remove: true,       // delete an item by tapping the delete button top right
                        overrideItems: false  // allow these options to override item.editable
                    },
                    format: {
                        minorLabels: {
                            hour: 'HH:mm',
                            weekday: 'ddd D',
                            day: 'D',
                            week: 'w',
                            month: 'MMM',
                            year: 'YYYY'
                        },
                        majorLabels: {
                            hour: 'ddd D MMMM', //Orientation
                            weekday: 'MMMM YYYY',
                            day: 'MMMM YYYY',
                            week: 'MMMM YYYY',
                            month: 'YYYY',
                            year: ''
                        }
                    },
                    onMove: (item, callback) => {
                        if (item.content != null) {
                            axios
                                .post('/appointments/update/' + item.id, item)
                                .then(res => {
                                    let index = this.items.findIndex(x => x.id === item.id)
                                    this.items[index].start = item.start;
                                    this.items[index].end = item.end;
                                    this.timeline.setItems(this.items);
                                }).catch(err => {
                                //Hack to show sort of nice 403 error
                                if(err.response.status === 403) {
                                    document.getElementById("accessDeniedError").style.transition = "all 2s";
                                    document.getElementById("accessDeniedError").style.display = "block";
                                    document.getElementById("accessDeniedError").style.opacity = "100";
                                    setTimeout(function(){
                                        setTimeout(function(){
                                            document.getElementById("accessDeniedError").style.display = "none";
                                        }, 1000);
                                        document.getElementById("accessDeniedError").style.opacity = "0";

                                    }, 2000);
                                }
                            })
                        }
                        else {
                            callback(null); // cancel updating the item
                        }
                    },
                    onRemove: (item, callback) => {
                        if (item.content != null) {
                            axios
                                .delete('/appointments/' + item.id)
                            .then(res => {
                                let index = this.items.findIndex(x => x.id === item.id)
                                this.items.splice(index, 1)
                                this.timeline.setItems(this.items);
                            }).catch(err => {
                                //Hack to show sort of nice 403 error
                                if(err.response.status === 403) {
                                    document.getElementById("accessDeniedError").style.transition = "all 2s";
                                    document.getElementById("accessDeniedError").style.display = "block";
                                    document.getElementById("accessDeniedError").style.opacity = "100";
                                    setTimeout(function(){
                                        setTimeout(function(){
                                            document.getElementById("accessDeniedError").style.display = "none";
                                        }, 1000);
                                        document.getElementById("accessDeniedError").style.opacity = "0";

                                    }, 2000);
                                }

                            })
                        }
                        else {
                            callback(null); // cancel updating the item
                        }
                    }
                },
                scaleOptions: {
                  day: {
                      zoomMax: 280000000,
                      zoomMin: 28000000,
                      start: moment().startOf('day'),
                      end: moment().add(1, 'days').startOf('day'),
                  },
                  week: {
                      zoomMax: 700000000,
                      zoomMin: 300000000,
                      start: moment().startOf('day'),
                      end: moment().add(8, 'days').startOf('day'),
                  },
                  fourteen_days: {
                      zoomMax: 1300000000,
                      zoomMin: 300000000,
                      start: moment().startOf('day').startOf('day'),
                      end: moment().add(15, 'days').startOf('day'),
                  }
                },
                itemsLoaded: null,
                usersLoaded: null,
                businessHoursLoaded:null,
                dateFormatsLoaded:null,
                timeline: null,
                business_hours: {
                    open: null,
                    close: null
                },
                dateFormats: {},
                timeAndDateWarnings: [],
            }
        },
        created() {
            this.dateFormatsLoaded = axios.get('/settings/date-formats').then((res) => {
                this.dateFormats = res.data;
                this.options.format.majorLabels.hour = res.data.momentjs_day_and_date_with_text;
                this.options.format.minorLabels.hour = res.data.momentjs_time;
            });
            this.usersLoaded = axios.get('/users/calendar-users').then((res) => {
                res.data.forEach((user) => {
                    user.absences.forEach((absence) => {
                        let absenceEntry = {};
                        absenceEntry.id = absence.id;
                        absenceEntry.group = user.external_id;
                        absenceEntry.content = "<span style='font-size:9px; margin:0; padding: 0 0 0 0; color:#eee;'>"+ moment(absence.start_at).format(this.dateFormats.momentjs_day_and_date_with_text + " " + this.dateFormats.momentjs_time) + " - " + moment(absence.end_at).format(this.dateFormats.momentjs_day_and_date_with_text + " " + this.dateFormats.momentjs_time) +"</span> <p style='margin:0; padding: 0; line-height: 0.8; font-size:16ox; color:#eee; font-size:12px;'>" +  "Not available"  + "</p>";
                        absenceEntry.start = absence.start_at;
                        absenceEntry.end = absence.end_at;
                        absenceEntry.type = "background";
                        absenceEntry.style = "background-color:#757575";
                        this.items.push(absenceEntry);
                    });
                    let group = {};
                    group.id = user.external_id;
                    group.content = "<div style='float:left; margin-right: 10px;'><img src='" + user.avatar + "' style='border-radius:50%;' width='60em'></div>" +
                        "<div style='float:right;'><p style='margin: 0'>" + this.sanitizeHTML(user.name) + "</p>" +
                        "<span style='font-size:11px; font-weight: 300; font-color:#eee;'>" + this.sanitizeHTML(user.department[0].name) +  "<span></div>";
                    group.style = "width: 240px";
                    this.groups.push(group);
                })
            });
            this.itemsLoaded = axios.get('/appointments/data').then((res) => {
                res.data.forEach((appointment) => {
                    let item = {};
                    item.id = appointment.external_id;
                    item.group = appointment.user.external_id;
                    item.content = "<span style='font-size:9px; margin:0; padding: 0 0 0 0'>"+ appointment.start_at +"</span> <p style='margin:0; padding: 0; line-height: 0.8;'>" + this.sanitizeHTML(appointment.title) + "</p>";
                    item.start = appointment.start_at;
                    item.end = appointment.end_at;
                    item.style = "background-color:" + appointment.color;
                    this.items.push(item);
                })
            });
            this.businessHoursLoaded = axios.get('/settings/business-hours').then((res) => {
                this.business_hours.open = res.data.open;
                this.business_hours.close = res.data.close;
                this.options.hiddenDates.push({
                    tag:'hide_closed_hours',
                    start: '2020-03-04 ' + this.business_hours.close,
                    end: '2020-03-05 ' + this.business_hours.open,
                    repeat: 'daily',
                })
            });

        },
        async mounted() {
            await this.itemsLoaded;
            await this.usersLoaded;
            await this.businessHoursLoaded;
            await this.dateFormatsLoaded;

            // Create a Timeline
            let timeline = new Timeline(document.getElementById('visualization'));
            timeline.setOptions(this.options);
            timeline.setGroups(this.groups);
            timeline.setItems(this.items);
            this.timeline = timeline;
        },
        methods: {
            setScale(val) {
                this.scale = val;
                this.options.start = this.scaleOptions[val].start;
                this.options.end = this.scaleOptions[val].end;
                this.options.zoomMin = this.scaleOptions[val].zoomMin;
                this.options.zoomMax = this.scaleOptions[val].zoomMax;
                this.timeline.setOptions(this.options);
            },
            toggleWeekend() {
                this.showWeekends = !this.showWeekends
                if (this.showWeekends) {
                    let index = this.options.hiddenDates.findIndex(x => x.tag === "hide_weekends");
                    this.options.hiddenDates.splice(index, 1 );
                } else {
                    this.options.hiddenDates.push({
                        tag:'hide_weekends',
                        start: "2019-07-27",
                        end: "2019-07-29",
                        repeat: "weekly",
                    })
                }
                this.timeline.setOptions(this.options);
            },
            toggleBusinessHours() {
                if(this.showBusinessHours) {
                this.options.hiddenDates.push({
                    tag:'hide_closed_hours',
                    start: '2020-03-04 ' + this.business_hours.close,
                    end: '2020-03-05 ' + this.business_hours.open,
                    repeat: 'daily',
                  })
                } else {
                    let index = this.options.hiddenDates.findIndex(x => x.tag === "hide_closed_hours");
                    this.options.hiddenDates.splice(index, 1 );
                }
                this.timeline.setOptions(this.options);
            },
            createNewAppointment(event) {
                var appointment = {};
                appointment.id = event.data.external_id;
                appointment.group = event.data.user_external_id;
                appointment.content = "<span style='font-size:9px; margin:0; padding: 0 0 0 0'>" + event.data.start_at + "</span> <p style='margin:0; padding: 0; line-height: 0.8;'>" + this.sanitizeHTML(event.data.title) + "</p>";
                appointment.start = event.data.start_at;
                appointment.end = event.data.end_at;
                appointment.style = "background-color:" + event.data.color;
                this.items.push(appointment);
                this.timeline.setItems(this.items);
            },
            sanitizeHTML(text) {
                var element = document.createElement('div');
                element.innerText = text;
                return element.innerHTML;
            }
        },
        components: {
            message: Message,
            createAppointment: CreateAppointment
        }
    }
</script>
<style lang="css">
    /* alternating column backgrounds */
    .vis-time-axis .vis-grid.vis-odd {
        background: #f5f5f5;
    }

    /* gray background in weekends, white text color */
    .vis-time-axis .vis-grid.vis-saturday,
    .vis-time-axis .vis-grid.vis-sunday {
        background: gray;
    }
    .vis-time-axis .vis-text.vis-saturday,
    .vis-time-axis .vis-text.vis-sunday {
        color: white;
    }
    .vis-item {
        border-color: #1371fe;
        color: #0155d3;
        border-left-width: 3px;
        border-left-style: solid;
        font-weight: 500;
        opacity: .8;
        font-size: 13px;
        height: 55px;
    }
    .vis-h0 .vis-h01 .vis-h15 .vis-h16 {
        color: blue !important;
        height: 100px;
        text-align: center;
    }

    .back-button {
        font-size: 47px;
        color: #536be2;
        line-height: 1px;
        margin-left:30px;
    }
    .utility-bar {
        width: 100%;
        background: #fff;
        min-height:8em;
        padding-top:2em;
    }

    .time-dropwdown{
        list-style-type: none;
        margin: 0;
        padding: 0;
        background-color: #fff;
    }

    .float-left {
        float: left;
    }


    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
        margin-top: 3px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }
    input:checked + .slider {
        background-color: #2196F3;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked + .slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    .toggle-design {
        min-width:100px;
        text-align:left;
        height: 41px;
        text-transform: capitalize;
    }

    .bootstrap-select .dropdown-toggle .filter-option {
        border: none;
    }


</style>
