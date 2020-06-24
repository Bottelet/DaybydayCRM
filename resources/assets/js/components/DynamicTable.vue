<template>
    <div class="row">
        <transition name="appear">
        <confirm-modal v-if='confirmModal' :title="modalTitle" @cancel="closeConfirmModal()" @confirm='action()'></confirm-modal>
        </transition>
        <div class="col-xs-8" :class="!selectedRow ? 'col-xs-12' : 'col-xs-8'">
            <div class="dataTables_wrapper">
                <div id="tasks-table_filter" class="dataTables_filter">
                    <label>
                        <input type="search" style="float:right;" placeholder="Søg" v-model="search">
                    </label>
                </div>
                <table class="table table-hover dataTable">
                    <thead>
                    <tr>
                        <th v-for="(column, index) in columns" :key="index" @click="sort(column)"> {{trans(column)}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(lead, index) in filteredList" :key="index" @click="getRow(lead)">
                        <td>{{lead["title"]}}</td>
                        <td>{{lead["visible_deadline_date"]}}</td>
                        <td>{{getDaysSinceStart(lead)}}</td>
                        <td>{{lead["user"]["name"]}}</td>
                    </tr>
                    </tbody>
                </table>

            </div>
        </div>
        <div class="col-xs-4" v-if="selectedRow !== null">
            <!--INFO-->
            <div class="tablet">
                <div class="tablet__head tablet__head__color-brand">
                        <h3 class="tablet__head-title text-white">{{trans('Information')}}</h3>
                    <button id="close-sidebar" @click="closeSidebar">
                        <i class="fa fa-times text-right"></i>
                    </button>
                </div>
                    <div class="tablet__body">
                        <div class="tab-content">
                                <div class="k-scroll ps ps--active-y" data-scroll="true" style="overflow: hidden;" data-mobile-height="350">
                                    <div class="row">
                                        <div class="col-xs-3">
                                            <a :href="'/leads/' + selectedRow.external_id">
                                                <button class="btn btn-brand btn-full-width cta-btn">
                                                    <i class="ion ion-ios-redo cta-btn-icon"></i> <br>
                                                    {{trans('View lead')}}
                                                </button>
                                            </a>
                                        </div>
                                        <div class="col-xs-3 no-padding" >
                                            <button class="btn btn-brand btn-full-width cta-btn" @click="openConfirmModal(convertToQualified, 'Are you sure you want to convert the lead to qualified?')">
                                                <i class="fa fa-bell cta-btn-icon"></i> <br>
                                                {{trans('Convert to qualified')}}
                                            </button>
                                        </div>
                                        <div class="col-xs-3 no-padding">
                                            <button class="btn btn-brand btn-full-width cta-btn"  @click="openConfirmModal(covertToOrder, 'Are you sure you want to convert the lead to an order?')">
                                                <i class="fa fa-dollar cta-btn-icon"></i> <br>
                                                {{trans('Convert to order')}}
                                            </button>
                                        </div>
                                        <div class="col-xs-3 no-padding" style="z-index: 999">
                                            <button class="btn btn-brand btn-full-width cta-btn" @click="openConfirmModal(closeLead, 'Are you sure you want to close the lead?')">
                                                <i class="fa fa-close cta-btn-icon"></i> <br>
                                                {{trans('Close lead')}}
                                            </button>
                                        </div>
                                        <div class="col-xs-12 movedown">
                                            <p>{{selectedRow.title}}</p>
                                        </div>
                                        <div class="col-xs-3">
                                            {{trans('Deadline')}}
                                        </div>
                                        <div class="col-xs-9">
                                            <p>{{selectedRow.visible_deadline_date}} {{selectedRow.visible_deadline_time}}</p>
                                        </div>
                                        <div class="col-xs-3">
                                            {{trans('Created by')}}
                                        </div>
                                        <div class="col-xs-9">
                                            <p>{{selectedRow.creator.name}}</p>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            <!--Client-->
            <div class="tablet">
                <div class="tablet__head">
                    <div class="tablet__head-label">
                        <h3 class="tablet__head-title">{{trans('Client')}}</h3>
                    </div>
                </div>
                <div class="tablet__body">
                    <div class="row">
                        <div class="col-xs-12">
                            <p class="company-name">
                            {{selectedRow.client.company_name}}
                            </p>
                        </div>
                            <div class="col-xs-6"><p aria-hidden="true" data-toggle="tooltip"
                                                     :title="trans('Contact person name')">{{selectedRow.client.primary_contact.name}}</p></div>
                            <div class="col-xs-6"><p><a :href="'mailto:' + selectedRow.client.primary_contact.email">{{selectedRow.client.primary_contact.email}}</a></p></div>
                            <div class="col-xs-6"><p aria-hidden="true" data-toggle="tooltip"
                                                     :title="trans('Primary number')">{{selectedRow.client.primary_contact.primary_number}}</p></div>
                            <div class="col-xs-6"><p aria-hidden="true" data-toggle="tooltip"
                                                     :title="trans('Secondary number')">{{selectedRow.client.primary_contact.secondary_number}}</p></div>
                            <div class="col-xs-6"><p aria-hidden="true" data-toggle="tooltip"
                                                     :title="trans('Company type')">{{selectedRow.client.company_type}}</p></div>
                            <div class="col-xs-6"><p aria-hidden="true" data-toggle="tooltip"
                                                     :title="trans('Vat')">{{selectedRow.client.vat}}</p></div>
                            <div class="col-xs-12"><p aria-hidden="true" data-toggle="tooltip"
                                                      :title="trans('Address')">{{selectedRow.client.address}}, {{selectedRow.client.zipcode}}, {{selectedRow.client.city}}</p></div>
                    </div>
                </div>
            </div>
            <!--Assignee-->
            <div class="tablet">
                <div class="tablet__head">
                    <div class="tablet__head-label">
                        <h3 class="tablet__head-title">{{trans('Assignee')}}</h3>
                    </div>
                </div>
                <div class="tablet__body">
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="profilepic">
                                <img :src="selectedRow.user.avatar" class="profilepicsize">
                            </div>
                        </div>
                        <div class="col-sm-9">
                            <ul>
                                <li class="assignee-name">{{selectedRow.user.name}}</li>
                                <li v-if="isUsersOnePhoneNumberSet(selectedRow.user)">
                                    <i class="fa fa-phone" aria-hidden="true" style="padding-right: 1em"></i>
                                    {{selectedRow.user.primary_number}}
                                    {{selectedRow.user.primary_number != "" &&  selectedRow.user.secondary_number != "" ? '/' : ''}}
                                    {{selectedRow.user.secondary_number}}
                                </li>
                                <li><i class="fa fa-envelope-o" aria-hidden="true" style="padding-right: 1em"></i><a :href="'mailto:' + selectedRow.user.email">{{selectedRow.user.email}}</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
</template>
<script>
    import ConfirmModal from './ConfirmModal.vue'
    import moment from 'moment'

    export default {
        data() {
            return {
                leads: [],
                columns: ['title', 'deadline', 'days', 'assigned'],
                selectedRow: null,
                search: '',
                confirmModal: false,
                action: null,
                modalTitle: null,
                currentSort:'deadline',
                currentSortDir:'asc'
            }
        },
        methods: {
            getRow(row) {
                this.selectedRow = row;
            },
            isUsersOnePhoneNumberSet(user){
                if (user.primary_number || user.secondary_number) {
                    return true;
                }
                return false;
            },
            closeSidebar() {
                this.selectedRow = null;
            },
            closeLead() {
                axios
                    .post('/leads/updatestatus/' + this.selectedRow.external_id, {"closeLead": true})
                let index = this.leads.findIndex(x => x.id === this.selectedRow.id);
                this.$delete(this.leads, index);
                this.closeSidebar()
                this.closeConfirmModal()

            },
            convertToQualified(){
                axios
                    .post('/leads/covert-to-qualified/' + this.selectedRow.external_id, {"closeLead": true})
                let index = this.leads.findIndex(x => x.id === this.selectedRow.id);
                this.$delete(this.leads, index);
                this.closeSidebar()
                this.closeConfirmModal()
            },
            covertToOrder(){
                axios
                    .post('/leads/covert-to-order/' + this.selectedRow.external_id, {"closeLead": true})
                    .then(function (response) {
                        window.location.href = "/invoices/" + response.data
                    })
                let index = this.leads.findIndex(x => x.id === this.selectedRow.id);
                this.$delete(this.leads, index);
                this.closeSidebar()
                this.closeConfirmModal()
            },
            openConfirmModal(action, modalTitle) {
                this.confirmModal = true
                this.action = action
                this.modalTitle = modalTitle
            },
            closeConfirmModal() {
                this.confirmModal = false
            },
            getDaysSinceStart(lead) {
                let today = new Date();
                let created_at = lead.created_at
                return moment(created_at).from(moment(today));
            },
            sort:function(s) {
                //if s == current sort, reverse
                if(s === this.currentSort) {
                    this.currentSortDir = this.currentSortDir==='asc'?'desc':'asc';
                }
                this.currentSort = s;
            }
        },
        mounted () {
            axios
                .get('/leads/data')
                .then(response => (
                    this.leads = response.data
                ))
        },
        computed: {
            filteredList() {
                return this.leads.sort((a,b) => {
                    let modifier = 1;
                    if(this.currentSortDir === 'desc') modifier = -1;
                    if(a[this.currentSort] < b[this.currentSort]) return -1 * modifier;
                    if(a[this.currentSort] > b[this.currentSort]) return 1 * modifier;
                    return 0;
                }).filter(lead => {
                    return lead.title.toLowerCase().includes(this.search.toLowerCase())
                })
            }
        },
        components: {
            confirmModal: ConfirmModal
        }
    }
</script>
<style scoped>
    ul {
        padding-left: 0px;
    }
    ul li {
        list-style: none;
    }
    .assignee-name {
        font-size: 1.2em;
        font-weight: 500;
        padding-left: 1.7em;
    }
    .company-name {
        font-size: 1.1em;
        font-weight: 600;
    }
    #close-sidebar {
        display:inline-block;
        overflow: auto;
        white-space: nowrap;
        margin:0px auto;
        border: 0;
        padding: 0;
        background: transparent;
        font-size:1.6em;
        margin-right: 10px;
    }
    .cta-btn {
        font-size: 0.77em !important;
        min-height: 6em;
        white-space: normal !important;
    }
    .cta-btn:hover {
        color: #fefefe !important;
    }
    .cta-btn-icon {
        font-size: 1.8em;
    }

    .no-padding {
        padding-left: 0% !important;
    }
</style>
