<template>
  <div class="">
    <section class="section">
    <div class="section-body">
      <div class="row">
        <div class="col-12 mt-4">
          <ul class="nav nav-tabs mb-3">
            <li class="nav-item ml-2">
              <a class="nav-link" :class="{ active: activeTab === 'bid' }" href="#" @click.prevent="activeTab = 'bid'">
                <b>Bid Summaries</b>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" :class="{ active: activeTab === 'prn' }" href="#" @click.prevent="activeTab = 'prn'">
                <b>
                  Purchase Requisition Note - PRNs
                  <span class="badge badge-primary">{{ prns.length }}</span>
                </b>
              </a>
            </li>
          </ul>
        </div>

        <!-- BID TABLE -->
        <div class="col-12" v-if="activeTab === 'bid'">
          <div class="card card-primary">
            <div class="card-header">
              <h4>Bid Summaries</h4>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                  <thead>
                    <tr>
                      <th>Sr No.</th>
                      <th>BID #</th>
                      <th>MR #</th>
                      <th>PRN #</th>
                      <th>Date</th>
                      <th>Request By</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(bid, index) in bids" :key="bid.id">
                      <td>{{ index + 1 }}</td>
                      <td>BID - {{ bid.id || 'N/A' }}</td>
                      <td>MR - {{ bid.prn?.mr_id || 'N/A' }}</td>
                      <td>PRN - {{ bid.prn?.id || 'N/A' }}</td>
                      <td>{{ new Date(bid.created_at).toLocaleString() }}</td>
                      <td>{{ bid.prn?.mr?.requested_by_user?.name || 'N/A' }}</td>
                      <td>
                      <span v-if="bid.status == '2'" class="badge badge-success">Approved</span>
                      <span v-else-if="bid.status == '1'" class="badge badge-warning">Processing</span>
                     <span v-else class="badge badge-danger">Rejected</span>
                    </td>
                      <td>  
                        <button class="btn btn-info btn-sm"
                            @click="viewBidsByPRN(bid.prn?.id)">
                            <i class="fas fa-eye"></i>
                          </button>
                      </td>
                    </tr>
                    <tr v-if="bids.length == 0">
                      <td colspan="6" class="text-center">No Bid Summaries Found</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <!-- PRN TABLE -->
        <div class="col-12" v-if="activeTab === 'prn'">
          <div class="card card-primary">
            <div class="card-header">
              <h4>Purchase Requisition Note - PRNs</h4>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                  <thead>
                    <tr>
                      <th>Sr No.</th>
                      <th>MR #</th>
                      <th>PRN #</th>
                      <th>Date</th>
                      <th>Request By</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(prn, index) in prns" :key="prn.id">
                      <td>{{ index + 1 }}</td>
                      <td>MR - {{ prn.mr_id || 'N/A' }}</td>
                      <td>PRN - {{ prn.id || 'N/A' }}</td>
                      <td>{{ new Date(prn.created_at).toLocaleString() }}</td>
                      <td>{{ prn.mr?.requested_by_user?.name || 'N/A' }}</td>
                      <td>
                        <span v-if="prn.status == '1'" class="badge badge-warning">Processing</span>
                      </td>
                      <td>
                        <button 
                            class="btn btn-success btn-sm mx-1" 
                            :data-target="'#' + formID" 
                            data-toggle="modal" 
                            @click="openBidModal(prn)">
                            <i class="fas fa-check"></i>
                          </button>
                        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewPRNModal"
                         @click="viewPRN(prn.id)">
                          <i class="fas fa-eye"></i>
                        </button>
                      </td>
                    </tr>
                    <tr v-if="prns.length === 0">
                      <td colspan="6" class="text-center">No Purchase Requisition Note Found</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <!-- View PRN Detail Modal -->
        <div class="modal fade" id="viewPRNModal" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
              
              <!-- Modal Header -->
              <div class="modal-header py-3">
                <h5 class="modal-title">
                  <i class="fas fa-file-alt mr-2"></i>
                  Purchase Requisition Note Detail - PRN # {{ selectedPRN?.prn_no || selectedPRN?.id || 'N/A' }}
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                  <span>&times;</span>
                </button>
              </div>

              <!-- Modal Body -->
              <div class="modal-body">
                <div v-if="selectedPRN && selectedPRN.details && selectedPRN.details.length">
                  <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Quantity</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(detail, i) in selectedPRN.details" :key="detail.id || i">
                      <td>{{ i + 1 }}</td>
                      <td>{{ detail.product?.name || detail.product?.product_name || 'N/A' }}</td>
                      <td>{{ detail.quantity || detail.qty || 0 }}</td>
                    </tr>
                    </tbody>
                  </table>
                </div>
                <div v-else class="text-muted text-center">
                  No product data available for this PRN.
                </div>
              </div>

              <!-- Modal Footer -->
              <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                  <i class="fas fa-times"></i> Close
                </button>
              </div>

            </div>
          </div>
        </div>
        <!-- Add Bid -->
        <Add :heading="'Add New Bid Summary'" :errors="validationErrors" :success="success" :formID="formID">
          <!-- Section: Requested Products -->  
     
        <div v-for="(item, index) in bidFormRows" :key="item.id" class="border p-3 mb-3 rounded bg-light">
          <h6 class="mb-3 font-weight-bold">Bidder {{ index + 1 }}</h6>
          <div class="row">
            <!-- Supplier -->
            <div class="form-group col-md-4">
            <div class="d-flex justify-content-between">
              <label>Select Supplier</label>
              <button type="button" class="btn btn-primary p-0 m-0 px-2" data-toggle="modal" data-target="#addSupplier">
                Add New
              </button>
            </div>
            <!-- Supplier Dropdown -->
            <select v-model="item.supplier_id" class="form-control select2">
              <option value="">Select Supplier</option>
              <option v-for="sup in suppliers" :key="sup.id" :value="sup.id">
                {{ sup.name }}
              </option>
            </select>

          </div>


            <!-- Quotation Ref -->
            <div class="form-group col-md-4">
              <label>Quotation Ref <span class="text-danger">*</span></label>
              <input type="text" class="form-control" v-model="item.quotation_ref" placeholder="e.g. QUO-0012">
            </div>

            <!-- Quotation Date -->
            <div class="form-group col-md-4">
              <label>Quotation Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" v-model="item.quotation_date">
            </div>

            <!-- Financial Terms -->
            <div class="form-group col-md-3">
              <label>Trade Classification</label>
              <input type="text" class="form-control" v-model="item.trade_classification">
            </div>

            <div class="form-group col-md-3">
              <label>Advance (%)</label>
              <input type="number" class="form-control" v-model="item.advance_percent">
            </div>

            <div class="form-group col-md-3">
              <label>After Delivery (%)</label>
              <input type="number" class="form-control" v-model="item.after_delivery_percent">
            </div>

            <div class="form-group col-md-3">
              <label>Credit Days</label>
              <input type="number" class="form-control" v-model="item.credit_days">
            </div>

            <div class="form-group col-md-3">
              <label>Discount (Amount)</label>
              <input type="text" class="form-control" v-model="item.discount_amount">
            </div>

            <div class="form-group col-md-3">
              <label>Delivery Charges</label>
              <input type="text" class="form-control" v-model="item.delivery_charges">
            </div>

            <div class="form-group col-md-3">
              <label>Contact Person</label>
              <input type="text" class="form-control" v-model="item.contact_person">
            </div>

            <!-- Terms -->
            <div class="form-group col-md-12">
              <label>Terms & Conditions</label>
              <textarea class="form-control" v-model="item.terms" rows="3" placeholder="Mention payment or delivery terms here..."></textarea>
            </div>

            <!-- Products Quoted -->
            <div class="col-md-12 mt-3">
            <h6 class="font-weight-bold">Products Quoted</h6>
            <div v-for="(product, pIndex) in item.products" :key="pIndex" class="row mb-2">
              <!-- Product Dropdown -->
              <div class="form-group col-md-4"> 
                <div class="d-flex justify-content-between">
                  <label>Select Product</label>
                  <button class="btn btn-primary p-0 m-0 px-2" data-toggle="modal" data-target="#addProducts">Add New</button>
                </div>
                <!-- Category Dropdown -->
                <select v-model="product.product_id" class="form-control select2">
                  <option value="" selected disabled class="text-light">Select Product</option>
                  <option v-for="pro in products" :key="pro.id" :value="pro.id">
                    {{ pro.name }}
                  </option>
                </select>
              </div>

              <!-- Rate Input -->
              <div class="form-group col-md-5">
                <label>Rate</label>
                <input
                  type="number"
                  class="form-control"
                  placeholder="Rate"
                  v-model="product.rate"
                />
              </div>

              <!-- Remove Button -->
              <div class="form-group col-md-2 d-flex align-items-center">
                <button class="btn btn-danger btn-sm"
                @click="removeProduct(index, pIndex)"  
                v-if="item.products.length > 1">
                <i class="fa fa-minus"></i>
              </button>
              </div>
            </div>

            <!-- Add Product Button -->
            <button class="btn btn-info btn-sm" @click="addProduct(index)">
              <i class="fa fa-plus"></i> Add Product
            </button>
          </div>


            <!-- Add / Remove Bidders -->
            <div class="form-group col-md-12 d-flex justify-content-end mt-3">
              <button class="btn btn-info btn-sm" @click="addRow">
                <i class="fa fa-plus"></i> Add Bidder
              </button>
              <button class="btn btn-danger btn-sm ml-2" @click="removeRow(index)" v-if="bidFormRows.length > 1">
                <i class="fa fa-minus"></i> Remove
              </button>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <template v-slot:button>
          <button type="button" class="btn btn-primary" :disabled="loading" @click="createBid">
            {{ loading ? 'Submitting...' : 'Submit Bid Summary' }}
          </button>
        </template>
        </Add> 
        <!-- VIEW BID MODAL -->
        <div class="modal fade" id="viewBidModal" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Bid Summary for PRN</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
              </div>
              <div class="modal-body">
                <!-- Loop over each bid -->
                <div v-for="(bid, bIndex) in groupedBids" :key="bIndex" class="mb-4 border-bottom pb-3">
                  <h5 class="text-warning">Bid #{{ bid.id }}</h5>
                  <div class="row p-3" style="background-color: #eaeff2;">
                    <div class="col-md-12"><h6><strong>Supplier:</strong> {{ bid.supplier?.name }}</h6></div>
                    <div class="col-md-4"><strong>Date:</strong> {{ new Date(bid.created_at).toLocaleString() }}</div>
                    <div class="col-md-4"><strong>MR #:</strong> MR - {{ bid.prn?.mr_id || 'N/A' }}</div>
                    <div class="col-md-4"><strong>PRN #:</strong> PRN - {{ bid.prn?.id || 'N/A' }}</div>
                    <div class="col-md-12"><strong>Requested By:</strong> {{ bid.prn?.mr?.requested_by_user?.name || 'N/A' }}</div>

                    <table class="table table-bordered mt-2">
                      <thead class="thead-light">
                        <tr>
                          <th>#</th>
                          <th>Product</th>
                          <th>Qty</th>
                          <th>Rate</th>
                          <th>Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(detail, dIndex) in bid.details" :key="dIndex">
                          <td>{{ dIndex + 1 }}</td>
                          <td>{{ detail.product?.name || 'N/A' }}</td>
                          <td>{{ detail.qty }}</td>
                          <td>{{ detail.rate }}</td>
                          <td>{{ detail.total }}</td>
                        </tr>
                      </tbody>
                    </table>

                    <div class="col-md-3"><strong>Subtotal:</strong> {{ getSubtotal(bid.details).toFixed(2) }}</div>
                    <div class="col-md-3"><strong>Advance %:</strong> {{ bid.advance }}%</div>
                    <div class="col-md-3"><strong>After Delivery %:</strong> {{ bid.after_delivery }}%</div>
                    <div class="col-md-3"><strong>Credit Days:</strong> {{ bid.credit_days }}</div>
                    <div class="col-md-3"><strong>Discount:</strong> {{ bid.discount }}</div>
                    <div class="col-md-3"><strong>Delivery Charges:</strong> {{ bid.delivery_charges }}</div>
                    <div class="col-md-12"><strong>Contact Person:</strong> {{ bid.contact_person }}</div>
                    <div class="col-md-12"><strong>Terms & Conditions:</strong><br>{{ bid.terms_condition }}</div>
                    <div class="col-md-3 ml-auto mt-2"><h6><strong>Grand Total:</strong> {{ calculateGrandTotal(bid) }}</h6></div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
        
        <AddSupplierModal></AddSupplierModal>
        <AddProductModal></AddProductModal>
      </div>
    </div>

  </section>
  </div>
</template>

<script>
import Add from '../../../components/Add.vue';
import AddSupplierModal from '../modal/addSupplierModal.vue';
import AddProductModal from '../modal/addProductsModal.vue';
import Swal from 'sweetalert2';

export default {
  name: "BidSummaryManager",
  components: {
    Add,
    AddSupplierModal,
    AddProductModal,
  },
  data() {
    return { 
      groupedBids: [],
      selectedBids: null, // this must match the modal binding
      activeTab: 'bid',
      prnID:'',
      bids: [],
      prns: [],
      selectedPRN: null, // holds the MR details for the modal
      products: [],
      suppliers: [],
      prnRequests: 0,
      formID: 'bidFormModal', 
      validationErrors: [],
      success: '',
      loading: false,
      isModalOpen: false,
      supplier: {
          supplier_id: ''
      },
      data: {
        product_name: '',
        prn_no: '',
        mr_no: '',
        qty: '',
        price: '',
        date: '',
        sub_total: '',
        tax_amount: '',
        delivery_amount: '',
        discount: '',
        net_amount: '',
        status: '',
        supplier: '',
        requestBy: '',
      },
      selectedPRN: '',
      bid_number: '',
      bid_date: '',
      bidFormRows: [
      {
        supplier_id: '',
        quotation_ref: '',
        quotation_date: '',
        trade_classification: '',
        advance_percent: '',
        after_delivery_percent: '',
        credit_days: '',
        discount_amount: '',
        delivery_charges: '',
        contact_person: '',
        terms: '',
        products: [
          {
            product_id: '',
            rate: '',
            quantity: 1
          }
        ]
      }
    ],
    };
  },
  mounted() {
    this.loadTinyMCE();
    this.fetchBid_PRN();
    const script = document.createElement('script');
    script.src = "https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js";
    script.referrerPolicy = "origin";
    document.head.appendChild(script);
  },
  methods: {
    async fetchBid_PRN() {
      try {
        const response = await this.callApi('post', 'bid-summaries');
        if (response.data.success) {
          this.bids = response.data.bids;
          this.prns = response.data.prns;
          this.products    = response.data.products || [];
          this.suppliers   = response.data.suppliers || [];
          this.prnRequests = this.prns.length;
        }
      } catch (error) {
        console.error('Failed to fetch bids and PRNs:', error);
      }
     },
    viewPRN(id) {
     this.selectedPRN = this.prns.find(prn => prn.id === id);
     }, 
    addRow() {
      this.bidFormRows.push({
        id: Date.now(),
        supplier: '',
        quotation_ref: '',
        quotation_date: '',
        trade_classification: '',
        advance_percent: '',
        after_delivery_percent: '',
        credit_days: '',
        discount_amount: '',
        delivery_charges: '',
        contact_person: '',
        terms: '',
        products: [
          {
            product_name: '',
            rate: ''
          }
        ]
      });
     },
    removeRow(index) {
      this.bidFormRows.splice(index, 1);
     },
    addProduct(bidIndex) {
      this.bidFormRows[bidIndex].products.push({
        product_name: '',
        rate: ''
      });
     },
    removeProduct(bidIndex, productIndex) {
      this.bidFormRows[bidIndex].products.splice(productIndex, 1);
     },
    async createBid() {
      
  const payload = {
    prn_id: this.selectedPRN?.id,
    mr_id : this.selectedPRN?.mr_id,

    suppliers: this.bidFormRows.map(row => ({
      supplier_id: row.supplier_id,
      quotation_ref: row.quotation_ref,
      quotation_date: row.quotation_date,
      trade_classification: row.trade_classification,
      advance_percent: row.advance_percent,
      after_delivery_percent: row.after_delivery_percent,
      credit_days: row.credit_days,
      discount_amount: row.discount_amount,
      delivery_charges: row.delivery_charges,
      contact_person: row.contact_person,
      terms_condition: row.terms,
      products: row.products.map(product => ({
        product_id: product.product_id,
        rate: product.rate,
        quantity: product.quantity || 1
      }))
    }))
  };

  console.log("Payload being submitted:", payload); // Check the payload

  try {
    const response = await this.callApi('post', 'bid-summaries/store', payload);

    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Bid Summary submitted successfully!'
    });

    this.clearForm();
  } catch (error) {
    console.error('Bid submission failed:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error?.response?.data?.message || 'Submission failed.'
    });
  }
     },
    openBidModal(prn) {
    this.selectedPRN = prn; // ✅ Set selected PRN before opening modal
    this.clearForm();       // Clears bid form rows only
     },
    clearForm() {
        this.validationErrors = [];
        this.success = '';
        this.bidFormRows = [
          {
            id: Date.now(),
            supplier_id: '',
            quotation_ref: '',
            quotation_date: '',
            trade_classification: '',
            advance_percent: '',
            after_delivery_percent: '',
            credit_days: '',
            discount_amount: '',
            delivery_charges: '',
            contact_person: '',
            terms: '',
            products: [
              {
                product_id: '',
                rate: '',
                quantity: 1
              }
            ]
          }
        ];
        // 🔒 Don't touch this.selectedPRN here!
     },
    async viewBidsByPRN(prnId) {
      const payload = { prn_id: prnId };
      try {
        const res = await this.callApi('post', 'bid-summaries/show', payload);
        if (res.data.success) {
          this.groupedBids = res.data.bids_by_prn; // result will be an array of PRN objects with their bids
          this.$nextTick(() => {
            $('#viewBidModal').modal('show');
          });
        }
      } catch (error) {
        console.error("Failed to fetch bids by PRN:", error);
      }
     },
    getSubtotal(details) {
      if (!Array.isArray(details)) return 0;
      return details.reduce((sum, item) => sum + parseFloat(item.total || 0), 0);
     },
    calculateTax(bid) {
      const subtotal = this.getSubtotal(bid.details);
      const taxPercent = parseFloat(bid.tax_percent || 0);
      return (subtotal * taxPercent / 100).toFixed(2);
     },
    calculateGrandTotal(bid) {
      const subtotal = this.getSubtotal(bid.details);
      const tax = parseFloat(this.calculateTax(bid));
      return (subtotal + tax).toFixed(2);
     },
    loadTinyMCE() {
      const script = document.createElement('script');
      script.src = "https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js";
      script.referrerPolicy = "origin";
      document.head.appendChild(script);
     }
  }
};
</script>
