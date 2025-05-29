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
                      <div class="col-md-3">
                        <b @click="currentTab = 'inventoryGoodReturns'" class="cursor-pointer">
                          4. <i class="far fa-file"></i> <u>Inventory Good Returns</u>
                        </b>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Inventory Control Register -->
                <div class="table-responsive card px-3 py-2" v-if="currentTab === 'inventoryControlRegister'">
                  <h5>Inventory Control Register</h5>
                  <table class="table table-striped table-hover table-bordered">
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
                        <th>SKU</th>
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
                </div>
                <!-- Inventory Good Received -->
                <div class="table-responsive card px-3 py-2" v-if="currentTab === 'inventoryGoodReceived'">
                <h5>Inventory Good Received Register</h5>
                <table class="table table-striped table-hover dataTable1">
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
                      <td>
                        {{
                          (
                            (parseFloat(detail.rate) || 0) * (detail.received_quantity) +
                            (parseFloat(detail.tax) || 0) +
                            (parseFloat(detail.delivery_charges) || 0) -
                            (parseFloat(detail.discount) || 0)
                          ).toFixed(2)
                        }}
                      </td>
                       
                    </tr>
                  </template>
                </tbody>
                </table>
                </div>
                <!-- Inventory Good Issued -->
                <div class="table-responsive card px-3 py-2" v-if="currentTab === 'inventoryGoodIssued'">
                  <h5>Inventory Good Issued Register</h5>
                  <table class="table table-striped table-hover">
                    <thead>
                      <tr>
                        <th>Sr No.</th>
                        <th>Date </th>
                        <th>SIN</th>
                        <th>Order</th>
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
                </div>
                <!-- Inventory Good Returns -->
                <div class="table-responsive card px-3 py-2" v-if="currentTab === 'inventoryGoodReturns'">
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
                </div>
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
      currentTab: 'inventoryGoodReceived',
      products: [],
      categoryOptions: [],
      unitOptions: [],
      editId: null,
      inward: [],
    }
  },
  mounted() {
    this.fetchreceived();  
  },
  watch: {
    activeTab(newTab) {
      this.$nextTick(() => {
        if ($.fn.DataTable.isDataTable('.dataTable1')) {
          $('.dataTable1').DataTable().destroy();
        }
        $('.dataTable1').DataTable();
      });

        },
  },
  methods: {
    async fetchreceived() {
      try {
        const response = await this.callApi('post', 'reports/received');
        
        // FIX: The API returns the array directly, not an object with { inward: [...] }
        this.inward = response.data.inward; // ✅ set it directly
        console.log("Fetched Inwards:", this.inward);
        this.$nextTick(() => {
          $('.dataTable1').DataTable();
        });
      } catch (error) {
        console.error("Error fetching inwards:", error);
      }
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