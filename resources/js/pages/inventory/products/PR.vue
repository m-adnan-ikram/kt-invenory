<template>
  <section class="section">
    <div class="section-body">
      <div class="row">
        <div class="col-12 mt-4">
          <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
              <a
                class="nav-link"
                :class="{ active: activeTab === 'prn' }"
                href="#"
                @click.prevent="activeTab = 'prn'"
              >
              <b>Purchase Requisition Note - PRNs</b>
              </a>
            </li>
            <li class="nav-item ml-2">
              <a
                class="nav-link"
                :class="{ active: activeTab === 'mr' }"
                href="#"
                @click.prevent="activeTab = 'mr'"
              >
              <b>Material Requests - MRs <span class="badge badge-primary">{{ mrRequests }}</span></b>
              </a>
            </li>
          </ul>
        </div>
        <!-- MR Table -->
        <div class="col-12" v-if="activeTab === 'mr'">
          <div class="card card-primary">
            <div class="card-header">
              <h4>Material Requests - MRs <span class="badge badge-primary">{{ mrRequests }}</span></h4>
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
                      <td>{{ mr.requested_by_user?.name }}</td>
                      <td>
                        <button class="btn btn-info btn-sm" @click="viewMR(mr)">
                          <i class="fas fa-eye"></i> View
                        </button>
                      </td>
                    </tr>
                    <tr v-if="mrs.length === 0">
                      <td colspan="6" class="text-center">No Material Requests Found</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <!-- PRN Table -->
        <div class="col-12"  v-if="activeTab === 'prn'">
          <div class="card card-primary">
            <div class="card-header">
              <h4>Purchase Requisition Note - PRNs </h4>
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
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  
                    <tr v-for="(prn, index) in prns" :key="prn.id">
                      <td>{{ index + 1 }}</td>
                      <td>MR - {{ prn.mr_id }}</td>
                      <td>PRN - {{ prn.id }}</td>
                      <td>{{ new Date(prn.created_at).toLocaleString() }}</td>
                      <td>
                        <span
                          class="badge text-white"
                          :class="{
                            'bg-danger':  prn.status == 0,
                            'bg-warning': prn.status == 1,
                            'bg-success': prn.status == 2
                          }"
                        >
                          {{
                            prn.status == 0
                              ? 'Rejected'
                              : prn.status == 1
                              ? 'Processing'
                              : prn.status == 2
                              ? 'Issued'
                              : 'Unknown'
                          }}
                        </span>
                      </td>
                      <td>
                        <button class="btn btn-info btn-sm" @click="viewPRN(prn)">
                          <i class="fas fa-eye"></i> View
                        </button>
                      </td>
                    </tr>

                    <tr v-if="mrs.length === 0">
                      <td colspan="6" class="text-center">No Purchase Requisition Note Found</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- MR Details Modal -->
      <div class="modal fade" id="viewMRModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">MR Details - MR-{{ selectedMR?.mr_id }}</h5>
              <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" v-if="selectedMR">
              <table class="table table-bordered">
                <thead>
                  <tr>
                  <th>#</th>
                  <th>Product</th>
                  <th>Requested Qty</th>
                  <th>Available Stock</th> 
                  <th>PRN Qty</th>
                </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in selectedMR.details" :key="index">
                    <td>{{ index + 1 }}</td>
                    <td>{{ item.product?.name }}</td>
                    <td>{{ item.qty }}</td>
                    <td>{{ item.product?.qty }}</td>
                    <td>
                      <input
                        type="number"
                        class="form-control form-control-sm"
                        :max="item.qty"
                        :min="0"
                        v-model.number="item.prnQty"
                        placeholder="Enter PRN Qty"
                      />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="modal-footer">
              <!-- <button class="btn btn-success" @click="issueSelectedItems(selectedMR)">Issue Selected Items</button> -->
              <button class="btn btn-primary" @click="submitInlinePRN">Submit PRN</button>
              <button class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Create PRN Modal -->
      <div class="modal fade" id="prnModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Create PRN</h5>
              <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Qty</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in remainingItems" :key="index">
                    <td>{{ item.product?.name }}</td>
                    <td><input
                        type="text"
                        class="form-control form-control-sm"
                        v-model="item.qty"
                        placeholder="Enter Quantity"
                      /> 
                      </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="modal-footer">
              <button class="btn btn-primary" @click="submitPRN">Submit</button>
              <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
          </div>
        </div>
      </div>

      <!-- PRN Detail Modal -->
      <div class="modal fade" id="viewPRNModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content shadow rounded-3">
            <div class="modal-header">
              <h5 class="modal-title">PRN Details - <span class="fw-semibold">PRN-{{ selectedPRN?.id }}</span></h5>
              <button type="button" class="btn btn-close-white" data-bs-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <hr>
            <div class="modal-body" v-if="selectedPRN">
              <div class="mb-3">
                <p class="mb-1"><strong>Requested By:</strong> {{ selectedPRN.mr.requested_by_user?.name }}</p>
                <p class="mb-1"><strong>MR ID:</strong> MR-{{ selectedPRN.mr.id }}</p>
                <p class="mb-3"><strong>Created At:</strong> {{ new Date(selectedPRN.created_at).toLocaleString() }}</p>
              </div>

              <h6 class="mb-3">Products</h6>
              <div class="table-responsive">
                <table class="table table-striped align-middle">
                  <thead class="table-light">
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">Product</th>
                      <th scope="col">Quantity</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(detail, index) in selectedPRN.details" :key="detail.id">
                      <td>{{ index + 1 }}</td>
                      <td>{{ detail.product?.name }}</td>
                      <td>{{ detail.qty }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</template>

<script>
import Swal from 'sweetalert2';
export default {
  name: "PRNManager",
  data() {
    return {
      stockList: [
        { product: 'Mouse', available: 10 },
        { product: 'Keyboard', available: 5 },
      ],
      selectedMR: null,
      remainingItems: [],
      mrs: [],
      prns: [],
      mrRequests: 0,
      selectedPRN: null,
      activeTab: 'prn',
    };
  },
  mounted() {
    this.fetchMR_PRN(); 
  },
      methods: {
    async fetchMR_PRN() {
        try {
          const response = await this.callApi('post', 'prn');
          if (response.data.success) {
            this.mrs = response.data.mrs;
            this.prns = response.data.prns;
            this.mrRequests = this.mrs.length;
          }
        } catch (error) {
          console.error('Failed to fetch data:', error);
        }
      },
    getStock(productName) {
      const product = this.stockList.find(p => p.product === productName);
      return product ? product.available : 0;
     },
    viewMR(mr) {
      this.selectedMR = {
        ...mr,
        details: mr.details.map(detail => ({
          ...detail,
          issueQty: 0,
        }))
      };
      $('#viewMRModal').modal('show');
     },
    viewPRN(prn) {
    this.selectedPRN = prn;
    $('#viewPRNModal').modal('show');
     },
    issueSelectedItems(mr) {
      const issuedItems = mr.details.filter(item => item.issueQty > 0);
      if (issuedItems.length === 0) {
        alert("Please enter issue quantity.");
        return;
      }
      issuedItems.forEach(item => {
        const stock = this.stockList.find(s => s.product === item.product.name);
        if (stock) stock.available -= item.issueQty;
      });
      mr.status = 'issued';
      $('#viewMRModal').modal('hide');
      alert("MR items successfully issued.");
     },
    createPRNForRemaining(mr) {
  this.remainingItems = mr.details
    .filter(item => (item.qty - (item.issueQty || 0)) > 0)
    .map(item => ({
      product: item.product,
      product_id: item.product.id,
      qty: item.qty - (item.issueQty || 0),
    }));
  $('#prnModal').modal('show');
     },
    async submitInlinePRN() {
      try {
        const prnItems = this.selectedMR.details
          .filter(item => item.prnQty && item.prnQty > 0)
          .map(item => ({
            product_id: item.product.id,
            qty: item.prnQty
          }));

        if (prnItems.length === 0) {
          Swal.fire('Warning', 'No PRN quantities entered.', 'warning');
          return;
        }

        const payload = {
          mr_id: this.selectedMR.id,
          items: prnItems
        };

        const response = await this.callApi('post', 'prn/store', payload);

        if (response.data.success) {
          Swal.fire('Success', response.data.message || 'PRN created successfully!', 'success');
          $('#viewMRModal').modal('hide');
          this.fetchMR_PRN();  // Refresh data
        } else {
          Swal.fire('Error', response.data.message || 'Something went wrong!', 'error');
        }
      } catch (error) {
        console.error("PRN Submit Error:", error);
        Swal.fire('Error', error.response?.data?.message || 'Error while submitting PRN.', 'error');
      }
     },
  },
};
</script>

<style scoped>
.table td,
.table th {
  vertical-align: middle;
}
</style>