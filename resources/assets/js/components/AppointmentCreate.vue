<template>
<div>
    <button class="btn back-icon" :style="buttonStyle" data-toggle="modal" data-target="#appointmentCreateModal"><i class="fa fa-plus"></i></button>

    <!-- Modal -->
    <div id="appointmentCreateModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{trans('Create new appointment')}}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-xs-12">
                            <label for="title" class="col-sm-1 thin-weight control-label">{{trans('Title')}}</label>

                            <div class="form-group col-sm-6">
                                <input type="text" id="title" name="title"  class="form-control" v-model="newAppointment.title">
                            </div>
                        </div>
                        <div class="form-group  col-xs-12">
                            <label for="user-select" class="col-sm-1 thin-weight control-label">{{trans('User')}}</label>
                            <div class="form-group col-sm-6">
                                <select name="user"
                                        id="user-select"
                                        class="form-control bootstrap-select assignee-selectpicker dropdown-user-selecter no-border"
                                        data-live-search="true"
                                        data-style="btn btn-sm dropdown-toggle btn-light"
                                        data-container="body"
                                        v-model="newAppointment.user">
                                    <option v-for="user in users" :data-tokens="user.external_id"
                                            :value="user.external_id">{{user.name}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-inline col-xs-12">
                            <label for="start_date" class="col-sm-1 thin-weight control-label">{{trans('Start')}}</label>

                            <div class="form-group col-sm-4">
                                <input type="text" id="start_date" name="start_date"  class="form-control" v-model="newAppointment.start_date">
                            </div>
                            <div class="form-group col-sm-3">
                                <input type="text" id="start_time" name="start_time" class="form-control" v-model="newAppointment.start_time">
                            </div>
                        </div>
                        <div class="form-inline col-xs-12">
                            <label for="start_date" class="col-sm-1 thin-weight control-label">{{trans('End')}}</label>

                            <div class="form-group col-sm-4">
                                <input type="text" id="end_date" name="end_date"  class="form-control" v-model="newAppointment.end_date">
                            </div>
                            <div class="form-group col-sm-3">
                                <input type="text" id="end_time" name="end_time" class="form-control" v-model="newAppointment.end_time">
                            </div>
                        </div>
                        <div class="form-inline col-xs-12">
                            <label for="color" class="col-sm-1 thin-weight control-label">{{trans('Color')}}</label>

                            <div class="form-group col-sm-4">
                                <select name="color" id="color" class="form-control" v-model="newAppointment.color" :style="{'background-color': this.newAppointment.color}" style="text-transform: capitalize;">
                                    <option v-for="(color, key) in colorOptions" :value="color" class="form-control" :style="{'background-color': color }" style="text-transform: capitalize;">
                                        {{ trans(key)}}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-danger" v-for="error in newAppointmentErrors">
                        {{error[0]}}
                    </div>

                    <div class="alert alert-warning" v-for="warning in timeAndDateWarnings">
                        {{trans(warning[0])}}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-brand" @click="createNewAppointment()">Create</button>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
    import moment from 'moment';
    import Message from './Message.vue'
    import CreateAppointment from './AppointmentCreate.vue'

    export default {
        props: {
          sourceType: {
              type: String,
              required: false,
              default: null
          },
          sourceExternalId: {
              type: String,
              required: false,
              default: null
          },
          clientExternalId: {
              type: String,
              required: false,
              default: null
          },
        buttonStyle: {
              type: String,
              required: false,
              default: 'font-size:20px; color: #fff; background-color: #536be2; margin-right:3em; margin-left:2em; border-radius:10%;'
        }
        },
        data() {
            return {
                showCreateAppointmentModal:true,
                newAppointment: {
                    title: null,
                    start_date: null,
                    start_time: null,
                    end_date: null,
                    end_time: null,
                    user: null,
                    color: "#d6e6ff"
                },
                newAppointmentErrors: [],
                colorOptions: {
                    blue: "#d6e6ff",
                    red: "#ffd6d6",
                    green: "#d6ffdc",
                    yellow: "#fff4d6",
                },
                usersLoaded: null,
                dateFormatsLoaded:null,
                dateFormats: {},
                users: [],
                timeAndDateWarnings: [],
            }
        },
        created() {
            this.dateFormatsLoaded = axios.get('/settings/date-formats').then((res) => {
                this.dateFormats = res.data;

                this.newAppointment.start_time = moment().format(res.data.momentjs_time)
                this.newAppointment.start_date = moment().format(res.data.carbon_date_with_text.toUpperCase())
                this.newAppointment.end_time = moment().add(1, 'hour').format(res.data.momentjs_time)
                this.newAppointment.end_date = moment().format(res.data.carbon_date_with_text.toUpperCase())
            });
            this.usersLoaded = axios.get('/users/calendar-users').then((res) => {
                this.users = res.data;
                this.newAppointment.user = res.data[0].external_id;
            });
        },
        async mounted() {
            await this.usersLoaded;
            await this.dateFormatsLoaded;

            $('#start_date').pickadate({
                hiddenName:true,
                format: this.dateFormats.carbon_date_with_text,
                formatSubmit: 'yyyy/mm/dd',
                closeOnClear: false,
                onSet: (context) => this.updateNewAppointmentDate('start_date', moment(context.select).format(this.dateFormats.carbon_date_with_text.toUpperCase())),
            });
            $('#start_time').pickatime({
                format: this.dateFormats.frontend_time,
                formatSubmit: 'HH:i',
                hiddenName: true,
                onSet: (context) => this.updateNewAppointmentTime('start_time', moment.duration(context.select,'seconds')),
            });
            $('#end_date').pickadate({
                hiddenName:true,
                format: this.dateFormats.carbon_date_with_text,
                formatSubmit: 'yyyy/mm/dd',
                closeOnClear: false,
                onSet: (context) => this.updateNewAppointmentDate('end_date', moment(context.select).format(this.dateFormats.carbon_date_with_text.toUpperCase())),
            });
            $('#end_time').pickatime({
                format: this.dateFormats.frontend_time,
                formatSubmit: 'HH:i',
                hiddenName: true,
                onSet: (context) => this.updateNewAppointmentTime('end_time', moment.duration(context.select,'seconds')),
            });
            $('#user-select').selectpicker('refresh');
        },
        methods: {
            updateNewAppointmentDate(field, newValue) {
                this.newAppointment[field] = newValue;
                this.validateTimeAndDate()
            },
            updateNewAppointmentTime(field, newValue) {
                var format = 'mm:ss';
                if(this.dateFormats.momentjs_time.endsWith('a')) {
                    format = 'mm:ss a';
                }

                let time = moment(newValue._data).format(format);
                this.newAppointment[field] = time;
                this.validateTimeAndDate()
            },
            validateTimeAndDate() {
                let start = moment(this.newAppointment.start_date + ' ' + this.newAppointment.start_time + ':00', this.dateFormats.carbon_date_with_text.toUpperCase() +  " " + this.dateFormats.momentjs_time);
                let end = moment(this.newAppointment.end_date + ' ' + this.newAppointment.end_time + ':00', this.dateFormats.carbon_date_with_text.toUpperCase() +  " " + this.dateFormats.momentjs_time);
                if (start.isSameOrAfter(end)) {
                    this.timeAndDateWarnings = {"warnings": ["Not possible to set end before start"]};
                    this.newAppointment.end_time = start.add(1, 'hour').format(this.dateFormats.momentjs_time);
                }
                if (start.isAfter(end)) {
                    this.timeAndDateWarnings = {"warnings": ["Not possible to set end before start"]};
                    this.newAppointment.end_date = this.newAppointment.start_date;
                }
            },
            createNewAppointment() {
                //Always post as YYYY/MM/DD
                let appointmentPost = JSON.parse(JSON.stringify(this.newAppointment));
                appointmentPost.start_date = moment(this.newAppointment.start_date, this.dateFormats.carbon_date_with_text.toUpperCase()).format('YYYY/MM/DD');
                appointmentPost.end_date = moment(this.newAppointment.end_date, this.dateFormats.carbon_date_with_text.toUpperCase()).format('YYYY/MM/DD');
                appointmentPost.source_external_id = this.sourceExternalId;
                appointmentPost.source_type = this.sourceType;
                appointmentPost.client_external_id = this.clientExternalId;

                axios
                    .post('/appointments', appointmentPost)
                    .then(res => {
                        this.setDefaultNewAppointment()
                        this.$emit('created-appointment', res)
                    }).catch(err => {
                    this.newAppointmentErrors = err.response.data.errors
                })

            },
            setDefaultNewAppointment() {
                this.newAppointment.title = null;
                this.newAppointment.start_time = moment().format(this.dateFormats.momentjs_time)
                this.newAppointment.start_date = moment().format(this.dateFormats.carbon_date_with_text.toUpperCase())
                this.newAppointment.end_time = moment().add(1, 'hour').format(this.dateFormats.momentjs_time)
                this.newAppointment.end_date = moment().format(this.dateFormats.carbon_date_with_text.toUpperCase())

                this.newAppointmentErrors = [];
                this.timeAndDateWarnings = [];
            }
        },
        components: {
            message: Message,
            createAppointment: CreateAppointment
        }
    }
</script>
<style>

    .picker, .picker__holder {
        width: 128%;
    }
    .picker--time .picker__holder {
        width: 30%;
    }
    .picker--time {
        min-width: 0px;
        max-width: 0px;
    }

</style>
