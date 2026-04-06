<template>
<div>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="invoice-modal-title modal-title" id="myModalLabel">
            {{trans(this.type)}} {{trans('management')}}
        </h4>
        <button type="button" class="btn btn-brand pull-right"  v-if="!readOnly" @click="addNewLine()" style="margin:5px 15px;">{{trans('Add new line')}}</button>
    </div>
        <div class="modal-body">
            <div class="line-wrapper" v-for="(line, lineIndex) in lines" :key="lineIndex" style="margin: 15px 0px;">
                <hr v-if="lineIndex != 0 && line.show != false" style="background-color: #b3c0ff"/>
                <i class="fa fa-lg pull-right fa-trash"  v-if="!readOnly" @click="removeLine(lineIndex)" style="cursor:pointer; padding:10px;"></i>
                <i class="fa fa-lg pull-right"  v-if="!readOnly" v-bind:class="[line.show ? 'fa-arrow-up' : 'fa-arrow-down ']" @click="toggleLine(line)" style="cursor:pointer; padding:10px;"></i>
                <span class="pull-right" v-if="line.errors && line.errors.length" style="color:red; font-size:2.2em; font-style:bold;">!</span>
                <div class="line-summary row" @click="toggleLine(line)" v-show="!line.show" style="background: #f7f7f7; padding: 10px;">
                    <div class="col-lg-3">{{line.title}}</div>
                    <div class="col-lg-3">{{line.price | currency({ 
                                    symbol: moneyFormat.symbol,
                                    thousandsSeparator: moneyFormat.thousandSeparator,
                                    fractionCount: moneyFormat.precision,
                                    fractionSeparator: moneyFormat.decimalSeparator,
                                    symbolPosition: moneyFormat.symbolPlacement == "after" ? 'back' : 'front',
                                    })}}</div>
                    <div class="col-lg-2">{{line.quantity}}</div>
                    <div class="col-lg-2">   {{getTotalLinePrice(line) | currency({ 
                                    symbol: moneyFormat.symbol,
                                    thousandsSeparator: moneyFormat.thousandSeparator,
                                    fractionCount: moneyFormat.precision,
                                    fractionSeparator: moneyFormat.decimalSeparator,
                                    symbolPosition: moneyFormat.symbolPlacement == "after" ? 'back' : 'front',
                                    }) }}</div>
                </div>
                <div class="line-information" v-show="line.show">
                    <div class="form-group">
                        <label v-if="products.length && !readOnly" for="product" class="control-label thin-weight">{{trans('Product')}}</label>
                        <v-select 
                        :options="products" 
                        v-model="line.product_name"
                        v-if="products.length && !readOnly"
                        label="name" 
                        v-on:input="product => fillWithProduct(product, line, lineIndex)"></v-select>
                    </div>
                    <div class="form-inline row">
                    <div class="form-group col-sm-6">
                        <label for="title" class="control-label thin-weight">{{trans('Title')}}</label>
                        <input v-model="line.title" type="text" name="title" :disabled="readOnly" class="form-control" placeholder="Insert task title (will be shown on invoice">
                    </div>

                    <div class="form-group col-sm-6">
                        <label for="comment" class="control-label thin-weight">{{trans('Description')}}</label>
                        <input v-model="line.comment" type="text" name="comment" :disabled="readOnly" class="form-control" placeholder="A short description, as to what is being billed"> 
                    </div>
                    </div>

                    <div class="form-group">
                        <label for="type" class="control-label thin-weight">{{trans('Type')}}</label>
                        <select v-model="line.type" name="type" :disabled="readOnly" :id="lineIndex  + '_type'" class="type form-control">
                            <option :selected= "line.type == 'pieces'" value="pieces">{{trans('pieces')}}</option>
                            <option :selected= "line.type == 'hours'" value="hours">{{trans('hours')}}</option>
                            <option :selected= "line.type == 'days'" value="days">{{trans('days')}}</option>
                            <option :selected= "line.type == 'session'" value="session">{{trans('session')}}</option>
                            <option :selected= "line.type == 'sqm'" value="sqm">{{trans('sqm')}}</option>
                            <option :selected= "line.type == 'meters'" value="meters">{{trans('meters')}}</option>
                            <option :selected= "line.type == 'kilometer'" value="kilometer">{{trans('kilometer')}}</option>
                            <option :selected= "line.type == 'kg'" value="kg">{{trans('kg')}}</option>
                            <option :selected= "line.type == 'package'" value="package">{{trans('package')}}</option>
                            <option :selected= "line.type == 'boxes'" value="boxes">{{trans('boxes')}}</option>
                        </select>
                    </div>

                    <div class="form-inline row">
                        <div class="form-group col-sm-3">
                            <label for="price" class="control-label thin-weight">{{trans('Price')}}</label>
                            <input v-model="line.price" type="number" name="price" step=".01" :disabled="readOnly" class="form-control" placeholder="300">
                        </div>

                        <div class="form-group col-sm-3">
                            <label for="quantity" class="control-label thin-weight">{{trans('Quantity')}}</label>
                            <input v-model="line.quantity" type="number" name="quantity" :disabled="readOnly" class="form-control" value="1">
                        </div>
                        <div class="form-group col-sm-6" style="padding-top: 15px;">
                            <div class="col-sm-4">
                                <p>{{trans('Total')}}</p>
                                {{getTotalLinePrice(line)| currency({ 
                                    symbol: moneyFormat.symbol,
                                    thousandsSeparator: moneyFormat.thousandSeparator,
                                    fractionCount: moneyFormat.precision,
                                    fractionSeparator: moneyFormat.decimalSeparator,
                                    symbolPosition: moneyFormat.symbolPlacement == "after" ? 'back' : 'front',
                                    }) }}
                            </div>
                            <div class="col-sm-4">
                                <p>{{trans('Sub Total')}}</p>
                                {{getSubTotalLinePrice(line) | currency({ 
                                    symbol: moneyFormat.symbol,
                                    thousandsSeparator: moneyFormat.thousandSeparator,
                                    fractionCount: moneyFormat.precision,
                                    fractionSeparator: moneyFormat.decimalSeparator,
                                    symbolPosition: moneyFormat.symbolPlacement == "after" ? 'back' : 'front',
                                    }) }}
                            </div>
                            <div class="col-sm-4">
                                <p>{{trans('Tax')}}</p>
                                {{getLineVat(line)| currency({ 
                                    symbol: moneyFormat.symbol,
                                    thousandsSeparator: moneyFormat.thousandSeparator,
                                    fractionCount: moneyFormat.precision,
                                    fractionSeparator: moneyFormat.decimalSeparator,
                                    symbolPosition: moneyFormat.symbolPlacement == "after" ? 'back' : 'front',
                                    }) }}
                            </div>
                        </div>
                    </div>
                    <hr v-if="readOnly">
                        <p v-for="(error, index) in line.errors" :key="index" class="text-danger">
                            {{error}}
                        </p>
                    
            </div>
             
        </div>
        <hr >
        <div class="row form-inline">
            <div class="col-lg-6 form-group">
                <p>{{trans('Total')}}</p>
            </div>
            <div class="form-group col-sm-6">
                <div class="col-sm-4"> 
                    <p style="font-weight:bold;">{{trans('Total')}}</p>
                    {{getTotalOfAllLines()| currency({ 
                        symbol: moneyFormat.symbol,
                        thousandsSeparator: moneyFormat.thousandSeparator,
                        fractionCount: moneyFormat.precision,
                        fractionSeparator: moneyFormat.decimalSeparator,
                        symbolPosition: moneyFormat.symbolPlacement == "after" ? 'back' : 'front',
                        }) }}
                </div>
                <div class="col-sm-4">
                <p style="font-weight:bold;">{{trans('Sub Total')}}</p>
                    {{getSubTotalOfAllLines()| currency({ 
                        symbol: moneyFormat.symbol,
                        thousandsSeparator: moneyFormat.thousandSeparator,
                        fractionCount: moneyFormat.precision,
                        fractionSeparator: moneyFormat.decimalSeparator,
                        symbolPosition: moneyFormat.symbolPlacement == "after" ? 'back' : 'front',
                        }) }}
              
                </div>
                <div class="col-sm-4">
                    <p style="font-weight:bold;">{{trans('Tax')}}</p>
                    {{getVatOfAllLines()| currency({ 
                        symbol: moneyFormat.symbol,
                        thousandsSeparator: moneyFormat.thousandSeparator,
                        fractionCount: moneyFormat.precision,
                        fractionSeparator: moneyFormat.decimalSeparator,
                        symbolPosition: moneyFormat.symbolPlacement == "after" ? 'back' : 'front',
                        }) }}
                </div>

            </div>
        </div>
        
        </div>
       
        <div class="modal-footer">
            <button type="button" class="btn btn-default col-lg-6" data-dismiss="modal">{{trans('Close')}}</button>
            <div class="col-lg-6" v-if="!readOnly">
                <button type="button" class="btn btn-brand form-control closebtn" @click="submitForm()">{{isEditable ? trans('Update') : trans('Create')}}</button>
            </div>
        </div>
</div>
</template>

<script>
import Vue from 'vue'
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css';
export default {
  props: {
    type: { 
      type: String,
      required: true
    },
    resource: { 
      type: Object,
      required: false
    },
    external_id: {
      type: String,
      required: false
    },
    editMode: {
      type: Boolean,
      default: true
    }
  },
  computed: {
      readOnly() {
          if (this.external_id && !this.editMode) {
              return true;
          }

          return false;
      },
      isEditable() {
          return this.external_id && this.editMode;
      },
      pickedType() {
          return this.type
      }
  },
  components: {
      vSelect: vSelect
  },
  methods: {
      fillWithProduct(product, line, lineIndex) {       
            line.title = product.name
            line.comment = product.description
            line.type = product.default_type
            line.price = product.divided_price
            line.product = product.external_id
            line.product_name = line.product_name 

            //Hack to change selectpicker value
            this.$nextTick(function(){ $("#" + lineIndex + "_type").selectpicker('refresh'); });
      },
      queryParameter() {
          const urlParams = new URLSearchParams(window.location.search);
          this.external_id_query = urlParams.get('offer-external-id')

          return urlParams.get('offer-external-id');
      },
      addNewLine() {
            var self = this;
  
            this.lines.map(function(line) {
                line.show = false;
            })

            this.lines.unshift({
                title: "",
                comment: "",
                price: 0,
                type: "",
                quantity: 1,
                show: true,
                product: "",
                errors: []
            })

          this.lines.map(function(line) {
                self.products.findIndex( p => p.external_id == line.product )
            })           
      },
      toggleLine(line) {
          line.show = !line.show
      },
      removeLine(lineIndex) {
          this.lines.splice(lineIndex, 1)
      },
      getTotalLinePrice(line) {
          const price = line.price * line.quantity;
          return price;
      },
      getSubTotalLinePrice(line) {
          const price = this.getTotalLinePrice(line)
          return price / this.moneyFormat.vatPercentage
      },
      getLineVat(line) {
          const price = this.getSubTotalLinePrice(line);
          return price * this.moneyFormat.vatRate
      },
      getTotalOfAllLines() {
          let price = 0
          this.lines.map(function (line) {
            price += line.price * line.quantity
          })
          return price
      },
      getSubTotalOfAllLines() {
          const price = this.getTotalOfAllLines();
          return price / this.moneyFormat.vatPercentage
      },
      getVatOfAllLines() {
          const price = this.getSubTotalOfAllLines();
          return price * this.moneyFormat.vatRate
      },
      submitForm() {
          let error = false;
          if(!this.lines.length) {
              return;
          }
          this.lines.forEach(line => {
              line.errors = [];
              if (!line.title) {
                  error = true;
                  line.errors.push("Title is required");
              }
              if (!line.price) {
                  error = true;
                  line.errors.push("Price is required");
              }
              if (!line.quantity) {
                  error = true;
                  line.errors.push("Quantity is required");
              }
              if (!line.type) {
                  error = true;
                  line.errors.push("Type is required");
              }
          });
         
          if(!error) {
              if (this.isEditable) {
                axios
                .post('/offer/' + this.external_id + '/update', this.lines)
                .then(res => {
                    location.reload();
                }).catch(err => {
                this.newAppointmentErrors = err.response.data.errors
            })
              } else {
                axios
                    .post('/invoice/create/' + this.type + '/' + this.resource.external_id, this.lines)
                    .then(res => {
                        location.reload();
                    }).catch(err => {
                    this.newAppointmentErrors = err.response.data.errors
                })
            }
          }
      }
  },
  data () {
    return {
        products: [],
        lines: [
            {
                title: "",
                comment: "",
                price: 0,
                type: "",
                quantity: 1,
                show: true,
                product: "",
                errors: []
            }
        ],
        moneyFormat: {},
        external_id_query: "",
    }
    },
    created() {
        if(this.readOnly || this.isEditable) {
            this.queryParameter();
            const useable_id = this.external_id ? this.external_id : this.external_id_query;
            axios.get('/offer/' + useable_id + "/invoice-lines/json").then((res) => {
                  this.lines = [];
                  res.data.forEach(line => {
                    var invoiceLine = {}
      
                    invoiceLine.show = true;
                    invoiceLine.title = line.title
                    invoiceLine.product = line.product ? line.product.external_id : null;
                    invoiceLine.product_name = line.product;
                    invoiceLine.errors = [];
                    invoiceLine.price = line.price / 100
                    invoiceLine.type = line.type
                    invoiceLine.comment = line.comment
                    invoiceLine.quantity = line.quantity
                    this.lines.push(invoiceLine)
                })
            })
        }
        axios.get('/products/data').then((res) => {
                this.products = res.data;
            });
        axios.get('/money-format').then((res) => {
            this.moneyFormat = res.data;
        });
    },
    
}
</script>

<style scoped>
.invoice-modal-title {
    text-transform: capitalize;
}
.form-control[disabled] {
    background-color: #f1f1f1;
}
</style>