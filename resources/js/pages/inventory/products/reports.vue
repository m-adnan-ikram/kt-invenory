<template>

    <section class="section">
      <div class="section-body">
        <div class="row">
          <div class="col-12">
            <div class="card card-primary">
              <div class="card-header">
                <h4>Reports</h4> 
              </div>
              <div class="card-body">
                <div class="col-md-12">
                  <div class="">
                    <div class="card-body row">
                      <div class="col-md-3">
                        <b @click="currentTab = 'inventoryControlRegister'" class="cursor-pointer">
                          1. <i class="far fa-file"></i> <u>Inventory Control Register</u>
                        </b>
                      </div>
                      <div class="col-md-3">
                        <b @click="currentTab = 'inventoryGoodReceived'" class="cursor-pointer">
                          2. <i class="far fa-file"></i> <u>Inventory Good Received</u>
                        </b>
                      </div>
                      <div class="col-md-3">
                        <b @click="currentTab = 'inventoryGoodIssued'" class="cursor-pointer">
                          3. <i class="far fa-file"></i> <u>Inventory Good Issued</u>
                        </b>
                      </div>
                      <!-- <div class="col-md-3">
                        <b @click="currentTab = 'inventoryGoodReturns'" class="cursor-pointer">
                          4. <i class="far fa-file"></i> <u>Inventory Good Returns</u>
                        </b>
                      </div> -->
                    </div>
                  </div>
                </div>
                <!-- Inventory Control Register -->
                <div class="table-responsive card px-3 py-2" v-if="currentTab === 'inventoryControlRegister'">
                  <h5>Inventory Control Register</h5>
                  <table class="table table-striped table-hover table-bordered dataTable2">
                    <thead>
                      <tr>
                        <th colspan="3">Item Discription</th>
                        <th colspan="3">Opening</th>
                        <th colspan="3">Purchase</th>
                        <th colspan="3">Issuance</th>
                        <th>Balance</th> 
                      </tr>
                      <tr>
                        <th>Sr No.</th>
                        <th>Product Name</th>
                        <th>Rate</th>
                        <th>QTY</th>
                        <th>Total Value</th>
                        <th>Rate</th>
                        <th>Stock</th>
                        <th>Total Value</th>
                        <th>Rate</th>
                        <th>Issued</th>
                        <th>Total Cost of Issuance</th>
                        <th>Returned</th> 
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, index) in data" :key="index">
                        <td>{{ index + 1 }}</td>
                        <td>{{ item.name }}</td>
                        <td>{{ item.avg_price || '-' }}</td>

                        <!-- Opening -->
                        <td>-</td> <!-- QTY -->
                        <td>-</td> <!-- Total Value -->
                        <td>{{ item.avg_price || '-' }}</td> <!-- Rate -->

                        <!-- Purchase -->
                        <td>{{ item.purchase_qty }}</td>
                        <td>{{ item.purchase_value.toFixed(2) }}</td>

                        <!-- Issuance -->
                        <td>{{ item.avg_price }}</td>
                        <td>{{ item.issuance_qty }}</td>
                        <td>{{ item.issuance_value.toFixed(2) }}</td>
                        <td>-</td> <!-- Returned -->

                        <!-- Balance -->
                        <td>{{ item.balance_qty }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <!-- Inventory Good Received -->
                <div class="table-responsive card px-3 py-2" v-if="currentTab === 'inventoryGoodReceived'">
                <h5>Inventory Good Received Register</h5>
                <table class="table table-striped table-hover dataTable2">
                  <thead>
                    <tr>
                      <th>Sr No.</th>
                      <th>Date</th>
                      <th>GRN #</th>
                      <th>PO #</th>
                      <th>Supplier</th>
                      <th>Product Name</th>
                      <th>Rate</th>
                      <th>Received QTY</th>
                      <th>Tax</th>
                      <th>Delivery Charges</th>
                      <th>Discount</th>
                      <th>Total</th> 
                    </tr>
                  </thead>
                  <tbody>
                  <template v-for="(grn, index) in inward" :key="grn.id">
                    <tr v-for="(detail, idx) in grn.details" :key="detail.id">
                      <td>{{ index + 1 }}<span v-if="grn.details.length > 1">.{{ idx + 1 }}</span></td>
                      <td>{{ new Date(grn.created_at).toLocaleDateString() }}</td>
                      <td>GRN - {{ grn.id }}</td>
                      <td>PO - {{ grn.po_id }}</td>
                      <td>{{ grn.supplier?.name || '-' }}</td>
                      <td>{{ detail.product?.name || '-' }}</td>
                      <td>{{ parseFloat(detail.rate).toFixed(2) }}</td>
                      <td>{{ detail.received_quantity ?? detail.qty }}</td>
                      <td>{{ parseFloat(detail.tax ?? 0).toFixed(2) }}</td>
                      <td>{{ parseFloat(detail.delivery_charges ?? 0).toFixed(2) }}</td>
                      <td>{{ parseFloat(detail.discount ?? 0).toFixed(2) }}</td>
                      <td>{{ parseFloat(detail.net_amount ?? 0).toFixed(2) }}</td>
                    </tr>
                  </template>
                </tbody>
                </table>
                </div>
                 <!-- Inventory Good Issued -->
                  <div class="table-responsive card px-3 py-2" v-if="currentTab === 'inventoryGoodIssued'">
                    <h5>Inventory Good Issued Register</h5>
                    <table class="table table-striped table-hover dataTable2">
                      <thead>
                        <tr>
                          <th>Sr No.</th>
                          <th>Date</th>
                          <th>SIN</th>
                          <th>Order</th>
                          <th>Product Name</th>
                          <th>Rate</th>
                          <th>Issued QTY</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(item, index) in outward" :key="item.id">
                          <td>{{ index + 1 }}</td>
                          <td>{{ new Date(item.created_at).toLocaleDateString() }}</td>
                          <td>{{ item.id }}</td>
                          <td>{{ item.mr_id }}</td>
                          <td>
                            <!-- Loop through details -->
                            <div v-for="d in item.details" :key="d.id">
                              {{ d.product.name }}
                            </div>
                          </td>
                          <td>
                            <div v-for="d in item.details" :key="d.id">
                              {{ d.rate }}
                            </div>
                          </td>
                          <td>
                            <div v-for="d in item.details" :key="d.id">
                              {{ d.qty }}
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                <!-- Inventory Good Returns -->
                <!-- <div class="table-responsive card px-3 py-2" v-if="currentTab === 'inventoryGoodReturns'">
                  <h5>Inventory Good Returns</h5>
                  <table class="table table-striped table-hover">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Date </th>
                        <th>SRN #</th>
                        <th>Order #</th>
                        <th>Product Name</th>
                        <th>Rate</th>
                        <th>Issued QTY</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(product, index) in products" :key="product.id">
                        <td>{{ index + 1 }}</td>
  
                        <td v-if="editId === product.id">
                          <input v-model="product.name" class="form-control form-control-sm" />
                        </td>
                        <td v-else>{{ product.name }}</td>
  
                        <td v-if="editId === product.id">
                          <select v-model="product.category" class="form-control form-control-sm">
                            <option value="">Select</option>
                            <option v-for="cat in categoryOptions" :key="cat">{{ cat }}</option>
                          </select>
                        </td>
                        <td v-else>{{ product.category }}</td>
  
                        <td v-if="editId === product.id">
                          <select v-model="product.unit" class="form-control form-control-sm">
                            <option value="">Select</option>
                            <option v-for="unit in unitOptions" :key="unit">{{ unit }}</option>
                          </select>
                        </td>
                        <td v-else>{{ product.unit }}</td>
  
                        <td v-if="editId === product.id">
                          <input v-model="product.qty" disabled class="form-control form-control-sm" />
                        </td>
                        <td v-else>{{ product.qty }}</td>

                        <td v-if="editId === product.id">
                          <input v-model="product.avg_price" disabled class="form-control form-control-sm" />
                        </td>
                        <td v-else>{{ product.avg_price }}</td>

                        <td>
                          <button v-if="editId === product.id" class="btn btn-success btn-sm" @click="saveProduct(product)">
                            Save
                          </button>
                          <button v-else class="btn btn-primary btn-sm mx-1" @click="editProduct(product)">
                            <i class="far fa-edit"></i>
                          </button>
                          <button class="btn btn-danger btn-sm mx-1" data-toggle="modal" data-target="#deleteConfirmModal" @click="confirmDelete(product.id)">
                            <i class="fas fa-trash"></i>
                          </button>
                          <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#productDetailModal">
                            <i class="fas fa-eye"></i>
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div> -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </template>
  
  <script>
   export default{
  data() {
    return {
      currentTab: 'inventoryControlRegister',
      products: [],
      categoryOptions: [],
      unitOptions: [],
      editId: null,
      inward: [],
      outward: [],
      data: [],
    }
   },
   mounted() {
    this.fetchreceived();  
    this.fetchissued();    
    this.fetchproductControl();    
    Promise.all([this.fetchproductControl(), this.fetchreceived(), this.fetchissued()]).then(() => {
      this.$nextTick(() => {
        this.initDataTable();
      });
    });  
    },
   watch: {
      currentTab() {
        this.$nextTick(() => {
          setTimeout(() => {
            this.initDataTable();
          }, 200);
        });
      }
    },
    methods: {
    async fetchproductControl() { 
      const response = await this.callApi('post', 'reports/product-control-register');
      this.data = response.data.data;
      return response;
    },
    async fetchreceived() { 
        const response = await this.callApi('post', 'reports/received');
        this.inward = response.data.inward;
        return response;
    },
    async fetchissued() { 
        const response = await this.callApi('post', 'reports/issued');
        this.outward = response.data.outward;
        return response;
    },
    initDataTable() {
    this.$nextTick(() => {
      setTimeout(() => {
        // Destroy all existing DataTable instances
        $('.dataTable2').each(function () {
          if ($.fn.DataTable.isDataTable(this)) {
            $(this).DataTable().destroy();
          }
        });

        // Initialize the table that is now visible
        let visibleTable = $('.dataTable2:visible');
        if (visibleTable.length) {
          visibleTable.DataTable({
            pageLength: 10, // or whatever default you want
            destroy: true,
          });
        }
      }, 500); // Increased timeout to allow Vue to fully render
    });
}


  }
}
  </script>
    <style scoped>
    .cursor-pointer {
      cursor: pointer; 
      color: #6777ef;
      font-size: 16px;
    }
  </style>