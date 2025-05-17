<template>
    <section class="section">
      <div class="section-body">
        <div class="row">
          <div class="col-12 mt-4">
            <ul class="nav nav-tabs mb-3">
              <li class="nav-item ml-2">
                <a class="nav-link" :class="{ active: activeTab === 'outward' }" href="#" @click.prevent="activeTab = 'outward'">
                  <b>Stock Outwards</b>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" :class="{ active: activeTab === 'mrs' }" href="#" @click.prevent="activeTab = 'mrs'">
                  <b>
                    Material Requests - MRs
                    <span class="badge badge-primary">{{ mrs.length }}</span>
                  </b>
                </a>
              </li>
            </ul>
          </div>

          <!-- Stock Outwards Table -->
          <div class="col-12" v-if="activeTab === 'outward'">
            <div class="card card-primary">
              <div class="card-header">
                <h4>Stock Outward</h4>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-hover">
                    <thead>
                      <tr>
                        <th>Sr No.</th>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Requested By</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, index) in outwards" :key="item.id">
                        <td>{{ index + 1 }}</td>
                        <td>Invoice-{{ item.id }}</td>
                        <td>{{ new Date(item.created_at).toLocaleString() }}</td>
                        <td>{{ item.requested_by }}</td>
                        <td>
                          <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewOutwardModal" @click="viewOutward(item)">
                            <i class="fas fa-eye"></i> View & Issue
                          </button>
                        </td>
                      </tr>
                      <tr v-if="outwards.length === 0">
                        <td colspan="5" class="text-center">No Stock Outward Records Found</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <!-- Material Requests Table -->
          <div class="col-12" v-if="activeTab === 'mrs'">
            <div class="card card-primary">
              <div class="card-header">
                <h4>Material Requests - MRs <span class="badge badge-primary">{{ mrs.length }}</span></h4>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-hover">
                    <thead>
                      <tr>
                        <th>Sr No.</th>
                        <th>MR #</th>
                        <th>Date</th>
                        <th>Request By</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(mr, index) in mrs" :key="mr.id">
                        <td>{{ index + 1 }}</td>
                        <td>MR - {{ mr.id }}</td>
                        <td>{{ new Date(mr.created_at).toLocaleString() }}</td>
                        <td>{{ mr.requested_by_user?.name || 'N/A' }}</td>
                        <td>
                          <button class="btn btn-info btn-sm" @click="viewMR(mr)">
                          <i class="fas fa-eye"></i> View
                        </button>
                        </td>
                      </tr>
                      <tr v-if="mrs.length === 0">
                        <td colspan="5" class="text-center">No Material Requests Found</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
           </div>

          <!-- MR Details Modal -->
          <div class="modal fade" id="viewMRModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">MR Details - MR-{{ selectedMR?.id }}</h5> <!-- Fixed here -->
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                  </div>

                  <div class="modal-body" v-if="selectedMR">
                    <table class="table table-bordered">
                      <thead class="table-light bg-light border-top">
                        <tr>
                          <th>#</th>
                          <th>Product</th>
                          <th>Requested Qty</th>
                          <th>Available Stock</th> 
                          <th>Issuance Qty</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(item, index) in selectedMR.details" :key="index">
                          <td>{{ index + 1 }}</td>
                          <td>{{ item.product?.name }}</td>
                          <td>{{ item.qty }}</td>
                          <td>{{ item.product?.qty }}</td>
                          <td>
                            <input type="number" class="form-control form-control-sm"
                                :max="item.qty" :min="0" v-model.number="item.prnQty"
                                :disabled="item.product?.qty === 0" placeholder="Enter Issuance Qty"
                            />
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <div class="modal-footer table-light bg-light border-top">
                    <button class="btn btn-primary" @click="submitIssuance">Submit Issuance</button>
                    <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                  </div>
                </div>
            </div>
           </div>
          <!-- Outward Details Modal -->
          <div class="modal fade" id="viewOutwardModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Outward Details - Invoice-{{ selectedOutward?.id }}</h5>
                  <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body" v-if="selectedOutward">
                  <p><strong>Requested By:</strong> {{ selectedOutward.requested_by }}</p> 
                  <p><strong>Date:</strong> {{ new Date(selectedOutward.created_at).toLocaleString() }}</p>

                  <table class="table table-bordered">
                    <thead class="table-light bg-light border-top">
                      <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Rate</th>
                        <th>Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(detail, index) in selectedOutward.details" :key="index">
                        <td>{{ index + 1 }}</td>
                        <td>{{ detail.product?.name }}</td>
                        <td>{{ detail.qty }}</td>
                        <td>{{ detail.rate }}</td>
                        <td>{{ detail.qty * detail.rate }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div class="modal-footer table-light bg-light border-top">
                  <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
           </div>
          </div>

        <AddProductModal></AddProductModal>
      </div>
    </section>
  </template>
  
  <script>
  import Add from '../../../components/Add.vue'; 
  import AddProductModal from '../modal/addProductsModal.vue'; 
  export default {
    name: "POManager",
    components: {
      Add, 
      AddProductModal,
    },
    data() {
      return { 
        activeTab: 'outward', 
        outwards: [],
        mrs: [],
        selectedMR: null,
        selectedOutward: null,
        data: {
          product_name: '',
          invoice_no: '',
          qty: '',
          date: '', 
          requestBy: '',
          assignedBy: ''
        }, 
        isModalOpen: false,
            loading: false,
            validationErrors: [],
            success: '',
        };
        },
        mounted() {
          this.fetchMRAndOutwards(); 
        },
        methods: {
        async fetchMRAndOutwards() {
            try {
              const response = await this.callApi('post', 'outward');
              if (response.data.success) { 
                this.outwards = response.data.outwards || []; // Store Issuance Notes
                this.mrs = response.data.mrs || [];           // Material Requests, if included
              } else {
                console.error("Error loading data:", response?.data?.message || 'Unknown error');
              }

            } catch (error) {
              console.error("API error:", error.message || error);
            }
         },
        viewMR(mr) {
          this.selectedMR = {
            ...mr,
            details: mr.details.map(detail => ({
              ...detail,
              prnQty: 0, // Use prnQty here to match the v-model in input
            }))
          };
          $('#viewMRModal').modal('show'); // Trigger Bootstrap modal
         },
        viewOutward(item) {
            this.selectedOutward = item; // Store selected item for modal display
         },
        async submitIssuance() {
            try {
              // Prepare payload
              const payload = {
                mr_id: this.selectedMR.id,
                requested_by: this.selectedMR.requested_by_user?.name || 'Unknown', // adjust if needed
                reason: this.selectedMR.reason || 'NA',
                details: this.selectedMR.details
                  .filter(item => item.prnQty > 0)
                  .map(item => ({
                    product_id: item.product_id || item.product?.id,
                    qty: item.prnQty ?? 0,
                    rate: item.product?.rate || 0,
                    total: (item.prnQty * (item.product?.rate || 0)).toFixed(2),
                  })),
              };
              if (payload.details.length === 0) {
                alert("Please enter at least one valid issuance quantity.");
                return;
              }
              const response = await this.callApi("post", "outward/store", payload);
              if (response.status == 200) {
                this.$toast.success("Issuance submitted successfully.");
                $('#viewMRModal').modal('hide');
                this.fetchMRAndOutwards(); // refresh your data
              } else {
                this.$toast.error(response.data.message || "Submission failed.");
              }
            } catch (error) {
              console.error("Error submitting Issuance:", error);
              this.$toast.error("An error occurred while submitting the Issuance.");
            }
         },
        clearForm() {
              this.data = {
              product_name: '',
              invoice_no: '',
              qty: '',
              date: '',
              requestBy: '',
              assignedBy: ''
              };
         }, 
      
    }
  };
  </script>
  