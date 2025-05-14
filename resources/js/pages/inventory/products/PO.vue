<template>
    <section class="section">
      <div class="section-body">

        <div class="row">
            <div class="col-12 mt-4">
              <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                  <a class="nav-link" :class="{ active: activeTab === 'pos' }" href="#" @click.prevent="activeTab = 'pos'">
                    <b> Purchase Order - POs </b>
                  </a>
                </li>
                <li class="nav-item ml-2">
                  <a class="nav-link" :class="{ active: activeTab === 'bids' }" href="#" @click.prevent="activeTab = 'bids'">
                    <b>Bid Summaries</b>
                    <span class="badge badge-primary ml-2">{{ bids.length }}</span>
                  </a>
                </li>
              </ul>
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
            <!-- BID TABLE -->
            <div class="col-12" v-if="activeTab === 'bids'">
            <div class="card card-primary">
              <div class="card-header">
                <h4>Bid Summaries  <span class="badge badge-primary ml-1">{{ bids.length }}</span></h4>
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
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(bid, index) in uniquePRNBids" :key="bid.id">
                        <td>{{ index + 1 }}</td>
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
                        <button class="btn btn-success btn-sm" @click="openAddPOModal(bid.prn_id)">
                          <i class="fas fa-check"></i>
                        </button>
                          <button class="btn btn-info btn-sm ml-1" @click="viewBidsByPRN(bid.prn?.id)">
                            <i class="fas fa-eye"></i>
                          </button>
                        </td>
                      </tr>
                      <tr v-if="uniquePRNBids.length == 0">
                        <td colspan="7" class="text-center">No Bid Summaries Found</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            </div>
        </div> 
       <!-- Add PO Modal -->
        <Add id="BidModal" v-if="showAddPOModal" :heading="'PO Request'" :errors="validationErrors" :success="success" :formID="formID"
          @close="showAddPOModal = false; $('#BidModal').modal('hide');">  
          <div class="modal-content">
            <div class="modal-header py-2 bg-primary text-white">
              <h5 class="modal-title"><i class="fas fa-file-alt mr-2"></i> Bid Comparison Table</h5>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12">
                  <div class="card shadow-sm border">
                    <div class="table-responsive">
                      <table class="table table-bordered table-striped align-middle text-center mb-0">
                        <thead class="table-secondary">
                          <tr>
                            <th class="align-middle">#</th>
                            <th class="align-middle">Product</th>
                            <th
                              v-for="(supplier, sIndex) in selectedSuppliers"
                              :key="'sup-' + sIndex"
                              class="text-capitalize"
                            >
                              {{ supplier.name }}
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr
                            v-for="(product, pIndex) in uniqueProducts"
                            :key="'prod-' + pIndex"
                          >
                            <td>{{ pIndex + 1 }}</td>
                            <td class="fw-semibold text-start ps-3">{{ product }}</td>
                            <td
                              v-for="(supplier, sIndex) in selectedSuppliers"
                              :key="'cell-' + pIndex + '-' + sIndex"
                            >
                              <div v-if="getRate(product, supplier.id)">
                                <span class="mb-1 fw-semibold mr-2">
                                  {{ getRate(product, supplier.id).toLocaleString() }}
                                </span>
                                  <input
                                      type="checkbox"
                                      class="checkbox-custom"
                                      v-model="selectedDetails"
                                      :value="getDetailId(product, supplier.id)"
                                  />
                              </div>
                              <span v-else class="text-muted">—</span>
                            </td>
                          </tr>
                           
                          <tr>
                            <th>Delivery Charges</th>
                            <td></td>
                            <td v-for="supplier in selectedSuppliers" :key="'delivery-charges-' + supplier.id">
                              {{ getField(supplier.id, 'delivery_charges') || '—' }}
                            </td>
                          </tr>
                          <tr>
                            <th>Tax</th>
                            <td></td>
                            <td v-for="supplier in selectedSuppliers" :key="'tax-' + supplier.id">
                              {{ getField(supplier.id, 'tax') || '—' }}
                            </td>
                          </tr>
                          <tr>
                            <th>Discount</th>
                            <td></td>
                            <td v-for="supplier in selectedSuppliers" :key="'discount-' + supplier.id">
                              {{ getField(supplier.id, 'discount') || '—' }}
                            </td>
                          </tr>
                          <tr>
                            <th>Grand Total</th>
                            <td></td>
                            <td v-for="supplier in selectedSuppliers" :key="'total-' + supplier.id">
                              <h6>{{ getField(supplier.id, 'total_amount')?.toLocaleString() || '—' }}</h6>
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
          <template v-slot:button>
            <button type="button" class="btn btn-primary" :disabled="loading" @click="add">
              {{ loading ? 'Loading...' : 'Add' }}
            </button>
          </template>
        </Add>
         <!-- VIEW BID MODAL -->
        <div class="modal fade" id="viewBidDetailModal" tabindex="-1" role="dialog">
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
  import Swal from 'sweetalert2';

  export default {
    name: "POManager",
    components: {
      Add,  
    },
    data() {
    return { 
        selectedMR: null,
        selectedPO: null,
        selectedPOs: [],
        extraFields: [
          'Terms & Conditions',
          'Advance %',
          'After Delivery %',
          'Credit Days',
          'Delivery Charges',
          'Tax',
          'Discount',
          'Grand Total'
        ],
        groupedBids: [],
        showAddPOModal: false,      // Add this
        showViewBidModal: false,    // Add this
        selectedBids: [], 
        selectedDetails: [],
        isApprovalMode: false,
        decisionMap: {}, // Keeps track of approve/reject state for each bidder
        activeTab: 'pos', 
        selectedGroup: null, // For modal
        selectedBid: { bidders: [] },
        formID: 'addPOForm',
        data: {
          product_name: '',
          po_no: '',
          mr_no: '',
          qty: '',
          price: '',
          date: '',
          sub_total: '',
          tax_amount: '',
          delivery_amount: '',
          dicount: '',
          net_amount: '',
          status: '',
          supplier: '',
          requestBy: '',
          assignedBy: ''
        },
        pos: [],
        bids: [],
        suppliers: [],
        products: [],
        poFormRows: [{
          id: Date.now(),
          product_name: '',
          po_no: '',
          qty: '',
          price: '',
          date: new Date().toLocaleDateString('en-GB'),
          sub_total: '',
          tax_amount: '',
          delivery_amount: '',
          dicount: '',
          net_amount: '',
          status: '',
          supplier: '',
          requestBy: '',
          assignedBy: ''
        }],
        loading: false,
        validationErrors: [],
        success: '',
     };
    },
    mounted() {
      this.fetchBid_PO();
    },
    computed: {
    posGroupedByPRN() {
      const grouped = {};
      this.pos.forEach(po => {
        const prnId = po.prn_id || 'N/A';
        const prn = po.prn || {};
        const mr = po.mr || {};
        const user = mr.requested_by_user || {};

        if (!grouped[prnId]) {
          grouped[prnId] = {
            prn_id: prnId,
            prn_no: prn.prn_no || 'PRN-' + prnId,
            mr_no: mr.mr_no || 'MR-' + po.mr_id,
            requested_by: user.name || 'N/A',
            pos: []
          };
        }
        grouped[prnId].pos.push(po);
      });
      return Object.values(grouped);
      },
    uniquePRNBids() {
          const seen = new Set();
          return this.bids.filter(bid => {
            if (!bid.prn?.id || seen.has(bid.prn.id)) return false;
            seen.add(bid.prn.id);
            return true;
          });
      },
    selectedSuppliers() {
      return this.selectedBids.map(bid => ({
        id: bid.supplier?.id,
        name: bid.supplier?.name
      }))
     },
    uniqueProducts() {
      const products = new Set()
      this.selectedBids.forEach(bid => {
        bid.details.forEach(detail => {
          if (detail.product?.name) {
            products.add(detail.product.name)
          }
        })
      })
      return Array.from(products)
     },
    },
    methods: {
      async fetchBid_PO() {
            try {
                const response = await this.callApi('post', 'pos'); // API call to your controller
                if (response.data.success) {
                    this.bids      = response.data.bids || [];
                    this.suppliers = response.data.suppliers || [];
                    this.products  = response.data.products || [];
                    this.pos       = response.data.pos || [];
                }
            } catch (error) {
                console.error('Failed to fetch data:', error);
            }
      },
      clearForm() {
        this.data = {
          product_name: '',
          po_no: '',
          qty: '',
          price: '',
          date: '',
          sub_total: '',
          tax_amount: '',
          delivery_amount: '',
          dicount: '',
          net_amount: '',
          status: '',
          supplier: '',
          requestBy: '',
          assignedBy: ''
        };
      },
      async openAddPOModal(prnId) {
      this.showAddPOModal = false
      this.selectedBids = []
      try {
        const res = await this.callApi('post', 'bid-summaries/compareBids', { prn_id: prnId })
        if (res.data.success && res.data.bids.length > 0) {
          this.selectedBids = res.data.bids
          this.selectedMR = res.data.bids[0]?.prn?.mr || null
          this.validationErrors = {}
          this.success = ''
          this.showAddPOModal = true
          this.$nextTick(() => {
            $('#BidModal').modal('show')
          })
        } else {
          window.alert("No bids found for this PRN.")
        }
      } catch (e) {
        console.error("Error loading bids for PRN:", e)
        this.validationErrors = e.response?.data?.errors || {}
      }
      },
      getRate(productName, supplierId) {
        const bid = this.selectedBids.find(b => b.supplier?.id === supplierId)
        if (!bid) return null
        const detail = bid.details.find(d => d.product?.name === productName)
        return detail?.rate || null
      },
      getDetailId(productName, supplierId) {
        const bid = this.selectedBids.find(b => b.supplier?.id === supplierId)
        if (!bid) return null
        const detail = bid.details.find(d => d.product?.name === productName)
        return detail?.id || null
      },
      getField(supplierId, field) {
        const bid = this.selectedBids.find(b => b.supplier?.id === supplierId)
        return bid?.[field] || null
      },
      async viewBidsByPRN(prnId) {
        const payload = { prn_id: prnId };

        try {
          const res = await this.callApi('post', 'bid-summaries/show', payload);
          if (res.data.success) {
            this.groupedBids = res.data.bids_by_prn; // Assign the correct bids
            this.$nextTick(() => {
              $('#viewBidDetailModal').modal('show');
            });
          } else {
            // Handle case when no bids are returned
            window.alert("No bids found for this PRN.");
          }
        } catch (error) {
          console.error("Failed to fetch bids by PRN:", error);
        }
      },
      getSubtotal(details) {
        if (!Array.isArray(details)) return 0;

        return details.reduce((sum, item) => {
          const total = parseFloat(item?.total) || 0;
          return sum + total;
        }, 0);
      },
      calculateGrandTotal(bid) {
        return (
          this.getSubtotal(bid.details) +
          parseFloat(bid.delivery_charges || 0) -
          parseFloat(bid.discount || 0)
        ).toFixed(2);
      },
      async add() {
        if (!this.selectedDetails.length) {
          this.$emit('error', 'Please select at least one bid detail.');
          return;
        }
        this.loading = true;
        const payload = {
          bid_detail_ids: this.selectedDetails
        }; 
        try {
          const response = await this.callApi('post', 'pos/store', payload);
          if (response.status === 200 || response.status === 201) {
            this.success = 'Purchase Orders created successfully!';
            this.selectedDetails = [];
            this.$emit('close');
            $('#BidModal').modal('hide');
          } else {
            this.$emit('error', response.data.message || 'Something went wrong');
          }
        } catch (err) {
          console.error(err);
          this.$emit('error', 'Failed to create Purchase Orders');
        } finally {
          this.loading = false;
        }
      },
      async viewPODetail(po_id) {
        try {
          // Fetch the PO data for the given PRN ID
          const response = await this.callApi('post', 'pos/show', { po_id });
          if (response.data.success) {
            // Update the selectedPOs and selectedGroup
            this.selectedPOs = response.data.pos;
            // Add a slight delay before showing the modal to ensure the DOM is updated
            setTimeout(() => {
              $('#viewPOModal').modal('show');
            }, 100); // Delay in milliseconds (100ms)
          }
        } catch (error) {
          console.error('Error fetching PO data for PRN:', error);
        }
      }
 

    }
  };
  </script>
  