<template>
    <div class="row">

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
        <div class="col-xs-4" >
            <lead-sidebar 
            :lead="selectedRow"
            :hidden="selectedRow"
            v-on:closed-lead="leadStatusChange"
            v-on:opened-lead="leadStatusChange"
            v-on:deleted-lead="removeRow($event.external_id)"
            v-on:closed-sidebar="selectedRow = null"
            />
        </div>
    </div>
</template>
<script>
    import LeadSidebar from './LeadSidebar.vue'
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
            },
            removeRow(external_id) {
                let index = this.leads.findIndex(x => x.external_id === external_id);
                this.$delete(this.leads, index);
            },
            leadStatusChange(lead) {
                console.log(lead)
                let index = this.leads.findIndex(x => x.external_id === lead.external_id);
                
                this.leads[index].status.title = lead.newStatus;
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
            LeadSidebar,
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
