<template>
<div>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="invoice-modal-title modal-title" id="myModalLabel">
            {{this.type}} management
        </h4>
        <button type="button" class="btn btn-brand pull-right" @click="addNewLine()" style="margin:5px 15px;">Add new line</button>
    </div>
        <div class="modal-body">
            <div class="line-wrapper" v-for="(line, lineIndex) in lines" :key="lineIndex" style="margin: 15px 0px;">
                <i class="fa fa-lg pull-right fa-trash" @click="removeLine(lineIndex)" style="cursor:pointer; padding:10px;"></i>
                <i class="fa fa-lg pull-right" v-bind:class="[line.show ? 'fa-arrow-up' : 'fa-arrow-down ']" @click="toggleLine(line)" style="cursor:pointer; padding:10px;"></i>
                <span class="pull-right" v-if="line.errors.length" style="color:red; font-size:2.2em; font-style:bold;">!</span>
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
                        <label for="product" class="control-label thin-weight">{{trans('Product')}}</label>
                        <select  name="product" class="product" :id="lineIndex + '_product'" 
                        data-container="body" 
                        data-live-search="true" 
                        data-style-base="form-control"
                        data-style=""
                        data-width="100%"
                        @change="fillWithProduct($event, line, lineIndex)">
                                <option  value="none" default></option>
                                <option  v-for="(product, index) in products" :value="index" :key="product.external_id"> {{product.name}}</option>
                        </select>
                    </div>
                    <div class="form-inline row">
                    <div class="form-group col-sm-6">
                        <label for="title" class="control-label thin-weight">{{trans('Title')}}</label>
                        <input v-model="line.title" type="text" name="title" class="form-control" placeholder="Insert task title (will be shown on invoice">
                    </div>

                    <div class="form-group col-sm-6">
                        <label for="comment" class="control-label thin-weight">{{trans('Description')}}</label>
                        <input v-model="line.comment" type="text" name="comment" class="form-control" placeholder="A short description, as to what is being billed"> 
                    </div>
                    </div>

                    <div class="form-group">
                        <label for="type" class="control-label thin-weight">{{trans('Type')}}</label>
                        <select v-model="line.type" name="type" :id="lineIndex  + '_type'" class="type form-control">
                            <option value="pieces">{{trans('pieces')}}</option>
                            <option value="hours">{{trans('hours')}}</option>
                            <option value="days">{{trans('days')}}</option>
                            <option value="session">{{trans('session')}}</option>
                            <option value="sqm">{{trans('sqm')}}</option>
                            <option value="meters">{{trans('meters')}}</option>
                            <option value="kilometer">{{trans('kilometer')}}</option>
                            <option value="kg">{{trans('kg')}}</option>
                            <option value="package">{{trans('package')}}</option>
                            <option value="boxes">{{trans('boxes')}}</option>
                        </select>
                    </div>

                    <div class="form-inline row">
                        <div class="form-group col-sm-3">
                            <label for="price" class="control-label thin-weight">{{trans('Price')}}</label>
                            <input v-model="line.price" type="number" name="price" step=".01" class="form-control" placeholder="300">
                        </div>

                        <div class="form-group col-sm-3">
                            <label for="quantity" class="control-label thin-weight">{{trans('Quantity')}}</label>
                            <input v-model="line.quantity" type="number" name="quantity" class="form-control" value="1">
                        </div>
                        <div class="form-group col-sm-6" style="padding-top: 15px;">
                            <div class="col-sm-4">
                                <p>Total</p>
                                {{getTotalLinePrice(line)| currency({ 
                                    symbol: moneyFormat.symbol,
                                    thousandsSeparator: moneyFormat.thousandSeparator,
                                    fractionCount: moneyFormat.precision,
                                    fractionSeparator: moneyFormat.decimalSeparator,
                                    symbolPosition: moneyFormat.symbolPlacement == "after" ? 'back' : 'front',
                                    }) }}
                            </div>
                            <div class="col-sm-4">
                                <p>Sub Total</p>
                                {{getSubTotalLinePrice(line) | currency({ 
                                    symbol: moneyFormat.symbol,
                                    thousandsSeparator: moneyFormat.thousandSeparator,
                                    fractionCount: moneyFormat.precision,
                                    fractionSeparator: moneyFormat.decimalSeparator,
                                    symbolPosition: moneyFormat.symbolPlacement == "after" ? 'back' : 'front',
                                    }) }}
                            </div>
                            <div class="col-sm-4">
                                <p>Vat</p>
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
                    
                        <p v-for="(error, index) in line.errors" :key="index" class="text-danger">
                            {{error}}
                        </p>
                    
            </div>
             
        </div>
        <hr >
        <div class="row form-inline">
            <div class="col-lg-6 form-group">
                <p>Total</p>
            </div>
            <div class="form-group col-sm-6">
                <div class="col-sm-4"> 
                    <p style="font-weight:bold;">Total</p>
                    {{getTotalOfAllLines()| currency({ 
                        symbol: moneyFormat.symbol,
                        thousandsSeparator: moneyFormat.thousandSeparator,
                        fractionCount: moneyFormat.precision,
                        fractionSeparator: moneyFormat.decimalSeparator,
                        symbolPosition: moneyFormat.symbolPlacement == "after" ? 'back' : 'front',
                        }) }}
                </div>
                <div class="col-sm-4">
                <p style="font-weight:bold;">Sub Total</p>
                    {{getSubTotalOfAllLines()| currency({ 
                        symbol: moneyFormat.symbol,
                        thousandsSeparator: moneyFormat.thousandSeparator,
                        fractionCount: moneyFormat.precision,
                        fractionSeparator: moneyFormat.decimalSeparator,
                        symbolPosition: moneyFormat.symbolPlacement == "after" ? 'back' : 'front',
                        }) }}
              
                </div>
                <div class="col-sm-4">
                    <p style="font-weight:bold;">Vat</p>
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
            <button type="button" class="btn btn-default col-lg-6" data-dismiss="modal">Close</button>
            <div class="col-lg-6">
                <button type="button" class="btn btn-brand form-control closebtn" @click="submitForm()">Create</button>
            </div>
        </div>
</div>
</template>

<script>
export default {
  props: {
    type: { 
      type: String,
      required: true
    },
    resource: { 
      type: Object,
      required: true
    },
  },
  methods: {
      fillWithProduct(event, line, lineIndex) {
            const product = this.products[event.target.value]
            
            line.title = product.name
            line.comment = product.description
            line.type = product.default_type
            line.price = product.price
            line.product = product.external_id

            //Hack to change selectpicker value
            this.$nextTick(function(){ $("#" + lineIndex + "_type").selectpicker('refresh'); });
            
      },
      addNewLine() {
            var self = this;
  
            this.lines.map(function(line, index) {
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

          this.lines.map(function(line, index) {
                var pIndex = self.products.findIndex( p => p.external_id == line.product )
                $("#" + index + "_product").val(pIndex).selectpicker('refresh');
            })

       this.$nextTick(function(){ $("#" + this.lines.length -1 + "_product").val("").selectpicker('refresh'); });
       this.$nextTick(function(){ $("#0_product").val("").selectpicker('refresh'); });
            
        
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
          console.log(error)
          if(!error) {
            axios
                .post('/invoice/create/' + this.type + '/' + this.resource.external_id, this.lines)
                .then(res => {
                    console.log(res)
                    
                }).catch(err => {
                this.newAppointmentErrors = err.response.data.errors
            })
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
        moneyFormat: {}
    }
    },
    created() {
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
</style>