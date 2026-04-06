<template>
    <div>
            <div class="input-group input-wrapper">
                <div class="input-group search-wrapper" v-bind:class="{activewrapper: isShowing}"  >
                    <div class="input-group-btn search-panel">
                        <button type="button" class="btn search-type-picker" data-toggle="dropdown">
                            <span id="search_concept">{{trans(searchType) | capitalize }}</span> <span class="caret"></span>
                        </button>

                        <ul class="dropdown-menu search-type-list" role="menu">
                            <li><a @click="setSearchType('clients')" href="#">{{trans('Clients')}}</a></li>
                            <li><a @click="setSearchType('tasks')" href="#">{{trans('Tasks')}}</a></li>
                            <li><a @click="setSearchType('leads')" href="#">{{trans('Leads')}}</a></li>
                            <li><a @click="setSearchType('projects')" href="#">{{trans('Projects')}}</a></li>
                        </ul>
                    </div>
                    <div class="frame">
                        <input type="hidden" name="search_param" value="all" id="search_param">
                        <input type="text" class="search-input" name="x" v-bind:placeholder="trans('Search term')" @keyup="search" v-model="searchQuery">

                        <div class="results" v-if="searchQueryLength >= 3">
                            <ul class="results-ul" v-if="results.length">
                                <li v-for="result in results" class="result-li">
                                    <a :href="result._source.link" class="result-a">
                                        <span class="result-span">
                                            {{result._source.display_value}}
                                        </span>
                                    </a>
                                </li>
                            </ul>
                            <div v-else-if="searchQueryLength >= 3">
                                <p>No Results</p>
                            </div>
                        </div>
                    </div>
                </div>

                <span class="input-group-btn">
                    <button class="btn search-button" type="button" v-on:click="isShowing = !isShowing"><span class="ion ion-ios-search search-icon"></span></button>
                </span>

            </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                searchQuery: '',
                results: [],
                isShowing: false,
                searchType: 'clients',
            }
        },
        methods: {
            search() {
                if (this.searchQueryLength < 3) {
                    return [];
                }
                var self = this;
                axios.get('/search/' + this.searchQuery + '/' + this.searchType , {
                    searchQuery: self.searchQuery
                }).then(function (response) {
                    self.results = response.data.hits.hits
                });
            },
            setSearchType(val) {
                this.searchType = val;
            }
        },
        filters: {
            capitalize: function (value) {
                if (!value) return ''
                value = value.toString()
                return value.charAt(0).toUpperCase() + value.slice(1)
            }
        },
        computed: {
            searchQueryLength: function () {
                return this.searchQuery.length;
            }
        }
    }
</script>

<style>
    .results-ul {
        list-style: none;
        padding-left: 15px;
    }
    .result-li {
        color: #337ab7;
        text-decoration: none;
    }
    .result-span {
        color: #646c9a;
        font-size: 1.13em;
        line-height: 2.2em;
    }
    .result-span:hover {
        color: #23527c;
    }
    .result-a:hover {
        color: #23527c;
        text-decoration: none;
    }
    .frame {
        position: relative;
    }
    .search-input {
        height: 50px;
        padding-left: 25px;
        font-size: 1.2em;
        border-radius: 0px;
        border: none;
        border-left: 1px #dedede solid;
    }
    .search-input:focus {
        outline: none;
    }
    .search-icon {
        font-size:2em;
    }
    .search-type-picker {
        padding: 14px 50px;
        background: none;
        border: none;
    }

    .search-type-picker:focus, .search-type-picker:active {
        color: inherit;
        background-color: transparent;
        border: none;
        outline: none !important;
        box-shadow: none !important;
    }

    .search-type-picker:hover{
        color: inherit;
        background-color: transparent;
        border: none;
        outline: none;
        box-shadow: none;
    }

    .search-panel:focus {
        outline: none;
    }

    .results {
        position: absolute;
        border: 1px solid rgba(0, 0, 0, 0.15);
        display:block;
        padding: 5px 5px;
        background: white;
        margin-top: 5px;
        width:100%;
    }

    .search-type-list {
        box-shadow: 0px 8px 20px 0px rgba(0, 0, 0, 0.15);
        border: 0;
        background: #fff;
        padding: 20px 30px;
        margin-top: 2px;
        border-radius: 4px;
    }

    .result-wrapper {
        padding: 14px 11px;
        margin-top: 33px;
        z-index: 1000;
        width: 100%;

    }
    .input-wrapper {
        width: 30%;
        float: right;
    }

    .activewrapper {
        opacity: 1 !important;
        z-index: 10 !important;
        left: 0 !important;
        transition: opacity 0.3s, left 0.5s;
    }

    .search-wrapper {
        opacity: 0;
        z-index: -9999;
        transition: opacity 0.5s, left 0.5s;
        left: 100px;
        border: 1px #efefef solid;
    }
    .search-button {
        background-color: transparent;
        padding: 8px 21px 13px 20px;
        border-radius: 0px;
        border: none;
        color: #337ab7;
    }
    .search-button:focus, .search-button:active {
        color: #23527c;
        background-color: #f0f3ff;
        border: none;
        outline: none !important;
        box-shadow: none !important;
    }

    .search-button:hover{
        color: #23527c;
        background-color: #f0f3ff;
        border: none;
        outline: none;
        box-shadow: none;
        transition: 0.3s;
    }

</style>
