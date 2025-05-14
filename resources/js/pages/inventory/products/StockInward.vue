<template>
    <section class="section">
      <div class="section-body">
        <div class="row">
          <div class="col-12 mt-4">
            <ul class="nav nav-tabs mb-3">
              <li class="nav-item ml-2">
                <a class="nav-link" :class="{ active: activeTab === 'inward' }" href="#" @click.prevent="activeTab = 'inward'">
                  <b>Stock Inwards</b>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" :class="{ active: activeTab === 'pos' }" href="#" @click.prevent="activeTab = 'pos'">
                  <b>
                    Purchase Orders - POs
                    <span class="badge badge-primary">{{ pos.length }}</span>
                  </b>
                </a>
              </li>
            </ul>
        </div>
        <!-- Inward Table -->
        <div class="col-12"  v-if="activeTab === 'inward'">
            <div class="card card-primary">
              <div class="card-header">
                <h4>Stock Inward</h4> 
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-hover">
                    <thead>
                      <tr>
                        <th>Sr No.</th>
                        <th>PO #</th>  
                        <th>Date</th>
                        <th>Supplier</th> 
                        <th>Received By</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(po, index) in inwards" :key="po.id">
                      <td>{{ index + 1 }}</td>
                      <td>PO - {{ po.po_no }}</td> 
                      <td>{{ po.date }}</td>
                      <td>{{ po.supplier?.name || '-' }}</td>
                      <td>{{ po.received_by || '-' }}</td>
                      <td>
                        <span v-if="po.status == '2'" class="badge badge-success">Received</span> 
                        <span v-else class="badge badge-warning">Pending</span>
                      </td>
                      <td>
                        <button v-if="po.status == '1'"
                                class="btn btn-success btn-sm mx-1" 
                                :data-target="'#' + formID" 
                                data-toggle="modal" 
                                @click="clearForm">
                          <i class="fas fa-plus"></i>
                        </button>  
                        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewMRModal" @click="viewMR(po.id)">
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
          <!-- POs Table -->
          <div class="col-12" v-if="activeTab === 'pos'">
              <div class="card card-primary">
                <div class="card-header">
                  <h4>Purchase Orders - PO</h4>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-striped table-hover">
                      <thead>
                        <tr>
                          <th>Sr No.</th>
                          <th>PO #</th>
                          <th>PRN #</th>
                          <th>MR #</th>
                          <th>Request By</th>
                          <th>BID #</th>
                          <th>Date</th>
                          <th>Grand Total</th>
                          <th>Supplier Name</th>
                          <th>Status</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <template v-for="(po, index) in pos" :key="po.id">
                          <tr>
                            <td>{{ index + 1 }}</td>
                            <td>PO - {{ po.id }}</td>
                            <td>{{ po.prn_id }}</td>
                            <td>{{ po.mr_id }}</td>
                            <td>{{ po.mr.requested_by_user.name }}</td>
                            <td>BID - {{ po.bid_id }}</td>
                            <td>{{ new Date(po.created_at).toLocaleString() }}</td>
                            <th>{{ po.total }} PKR</th>
                            <td>{{ po.supplier.name }} PKR</td>
                            <td>
                              <span v-if="po.status == '2'" class="badge badge-success">Approved</span>
                              <span v-else-if="po.status == '1'" class="badge badge-warning">Processing</span>
                              <span v-else class="badge badge-danger">Rejected</span>
                            </td>
                            <td>
                              <button class="btn btn-success btn-sm mr-1 px-2" @click="addInward(po.id)">
                                <i class="fas fa-plus"></i>
                              </button>
                              <button class="btn btn-info btn-sm" @click="viewPODetail(po.id)">
                                <i class="fas fa-eye"></i> View
                              </button>
                            </td>
                          </tr>
                        </template>
                        <tr v-if="pos.length === 0">
                          <td colspan="9" class="text-center">No Purchase Orders Found</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
        </div> 
         <!-- Add Inward Modal --> 
        <Add
          id="addInwardModal"   
          :heading="'Add New Inward'"
          :errors="validationErrors"
          :success="success"
          :formID="formID"
          :poDetails="selectedPO"
        >
          <!-- Products Form -->
          <div
            class="row"
            v-for="(product, index) in selectedPO.products"
            :key="index"
          > 
              <!-- Product Name (Disabled) -->
              <div class="form-group col-md-3">
                <label>Product Name</label>
                <input type="text" class="form-control" :value="product.product_name" disabled />
              </div>
              <!-- Receivable Quantity (Disabled with Dummy Value) -->
              <div class="form-group col-md-3">
                <label>Receivable Quantity <span class="text-danger">*</span></label>
                <input type="text" class="form-control" :value="product.qty" disabled />
              </div>
              <!-- Already Received Quantity (Disabled with Dummy Value) -->
              <div class="form-group col-md-3">
                <label>Already Received Quantity <span class="text-danger">*</span></label>
                <input type="text" class="form-control" :value="product.alreadey_received_qty" disabled />
              </div>
              <!-- Received Quantity (Editable) -->
              <div class="form-group col-md-3">
                <label>Received Quantity <span class="text-danger">*</span></label>
                <input
                  type="number"
                  class="form-control"
                  placeholder="Enter Received Quantity"
                  v-model="product.received_qty"
                />

                <small class="text-danger">You Can Receive just '<b>{{ product.qty }}</b>' against this PO.</small>
              </div> 
          </div>
          <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" @click="submitInward">Submit Inward</button>
        </div>
        </Add>
        <!-- POs Modal --> 
        <div class="modal fade" id="viewPOModal" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-xl" role="document" v-if="selectedPOs.length">
            <div class="modal-content">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title p-2">
                  POs Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" @click="selectedPOs = []">
                  <span>&times;</span>
                </button>
              </div>
              <div class="modal-body bg-light">
                <div
                  v-for="(po, idx) in selectedPOs"
                  :key="po.id"
                  class="mb-3 p-4 border rounded shadow-sm"
                >
                <h6 class="mb-3 font-weight-bold border-bottom pb-2 d-flex justify-content-between align-items-center">
                  <span>
                      PO #{{ po.id }} &nbsp;&nbsp; | &nbsp;&nbsp;
                      BID #: <span class="text-dark">{{ po.bid_id }}</span> &nbsp;&nbsp; | &nbsp;&nbsp;
                      PRN #: <span class="text-dark">{{ po.prn_id }}</span> &nbsp;&nbsp; | &nbsp;&nbsp;
                      MR #: <span class="text-dark">{{ po.mr_id }}</span>
                    </span>
                    <h6 class="text-dark">
                      Date :
                      {{ new Date(po.created_at).toLocaleString() }}
                    </h6>
                </h6>
                <div class="mb-2">
                    <h6 class="font-weight-bold border-bottom pb-1 mb-2">Supplier Information</h6>
                    <div class="row">
                      <div class="col-md-4 mb-1"><strong>Name:</strong> {{ po.supplier?.name }}</div>
                      <div class="col-md-4 mb-1"><strong>Contact:</strong> {{ po.supplier?.contact }}</div>
                      <div class="col-md-4 mb-1"><strong>CNIC:</strong> {{ po.supplier?.cnic }}</div>
                      <div class="col-md-12 mb-1"><strong>Address:</strong> {{ po.supplier?.address }}</div>
                    </div>
                  </div>
                  <table class="table table-bordered table-striped table-sm shadow-sm">
                  <thead class="thead text-center">
                    <tr>
                      <th class="py-2 px-3">#</th>
                      <th class="py-2 px-3">Product</th>
                      <th class="py-2 px-3">Qty</th>
                      <th class="py-2 px-3">Rate</th>
                      <th class="py-2 px-3">Subtotal</th>
                      <th class="py-2 px-3">Tax</th>
                      <th class="py-2 px-3">Delivery</th>
                      <th class="py-2 px-3">Discount</th>
                      <th class="py-2 px-3">Grand Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr 
                      v-for="(item, i) in po.po_details" 
                      :key="item.id" 
                      class="text-center align-middle"
                    >
                      <td class="py-2 px-3">{{ i + 1 }}</td>
                      <td class="py-2 px-3">{{ item.product?.name || 'N/A' }}</td>
                      <td class="py-2 px-3">{{ item.qty }}</td>
                      <td class="py-2 px-3">{{ parseFloat(item.rate).toFixed(2) }}</td>
                      <td class="py-2 px-3">{{ parseFloat(item.sub_total).toFixed(2) }}</td>
                      <td class="py-2 px-3">{{ parseFloat(item.tax).toFixed(2) }}</td>
                      <td class="py-2 px-3">{{ parseFloat(item.delivery).toFixed(2) }}</td>
                      <td class="py-2 px-3">{{ parseFloat(item.discount).toFixed(2) }}</td>
                      <td class="py-2 px-3 font-weight-bold text-success">{{ parseFloat(item.net_amount).toFixed(2) }}</td>
                    </tr>
                    <tr class="table-info font-weight-bold">
                      <td colspan="8" class="text-right py-2 px-3">PO Grand Total</td>
                      <td class="text-success py-2 px-3"><h6>{{ parseFloat(po.total).toFixed(2) }}</h6></td>
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
  import Add from '../../../components/Add.vue'; 
  
  export default {
    name: "POManager",
    components: {
      Add, 
    },
    data() {
      return { 
        formID: 'addPOForm',
        activeTab: 'inward', // default tab
        inwards: [],
        pos: [],    
        selectedPOs: [],
        selectedPO: {},
        data: {
          product_name: '',
          po_no: '',
          qty: '',
          received_qty: '',
          alreadey_received_qty: '', 
          date: '',  
          status: '',  
          supplier: '', 
          receivedBy: '',
        },
        inward: [
          { 
            id: '',
            product_name: '',
            po_id: '',
            qty: '',
            received_qty: '', 
            alreadey_received_qty: '', 
            date: '',
            status: '',  
            supplier: '',
            receivedBy: '',
          }, 
        ],
        poFormRows: [    // This is just for the form
        {
            id: Date.now(),
            product_name: '',
            po_no: '', 
            qty: '', 
            received_qty: '',
            alreadey_received_qty: '', 
            date: new Date().toLocaleDateString('en-GB'),
            status: '',  
            supplier: '',
            receivedBy: '',
        }
        ],
        isModalOpen: false,
            loading: false,
            validationErrors: [],
            success: '',
        };
        },
        mounted() {
          this.fetchPOAndInwards();
        },
        methods: {
        async fetchPOAndInwards() {
            try {
              const response = await this.callApi('post', 'inward');
              if (response.status == 200) {
                this.pos     = response.data.pos;
                this.inwards = response.data.inwards;
              } else {
                console.error("Error loading data", response.data.message);
              }
            } catch (error) {
              console.error("API error:", error);
            }
          },
          async viewPODetail(po_id) {
            try {
              // Fetch the PO data for the given PRN ID
              const response = await this.callApi('post', 'pos/show', { po_id });
              if (response.data.success) { 
                this.selectedPOs = response.data.pos; 
                setTimeout(() => {
                  $('#viewPOModal').modal('show');
                }, 100); // Delay in milliseconds (100ms)
              }
            } catch (error) {
              console.error('Error fetching PO data for PRN:', error);
            }
          }, 
          async addInward(po_id) {
          try {
            // Fetch PO with product details
            const response = await this.callApi('post', 'pos/getSingle', { po_id });
            if (response.data.success) {
              this.selectedPO = response.data.po; // Save PO with products
              // Open modal after DOM updates
              this.$nextTick(() => {
                $('#addInwardModal').modal('show');
              });
            }
          } catch (error) {
            console.error('Error fetching PO for inward:', error);
          }
        },
        async submitInward() {
    // Basic validation
    const products = this.selectedPO.products.map((p, i) => {
      const qty = parseFloat(p.received_qty);
      if (isNaN(qty) || qty < 0 || qty > p.qty) {
        alert(`Invalid received quantity for product ${p.product_name}`);
        throw new Error('Validation failed');
      }
      return {
        product_name: p.product_name,
        po_qty: p.qty,
        already_received: p.alreadey_received_qty,
        received_now: qty
      };
    });

    try {
      const payload = {
        po_id: this.selectedPO.id,
        items: products
      };

      const response = await this.callApi('post', 'inward/store', payload);

      if (response.data.success) {
        this.success = 'Inward added successfully!';
        this.validationErrors = {};
        $('#addInwardModal').modal('hide');
        // Optionally reset form here
      } else {
        this.success = null;
        this.validationErrors = response.data.errors || {};
      }
    } catch (err) {
      console.error('Error submitting inward:', err);
    }
  },
         clearForm() {
            this.data = {
            product_name: '',
            po_no: '',
            qty: '',
            received_qty: '',
            alreadey_received_qty: '', 
            date: '',
            status: '',  
            supplier: '',
            receivedBy: '',
            };
          },  
    }
  };
  </script>
  