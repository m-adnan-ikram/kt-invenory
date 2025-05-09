<template>
  <section class="section">
    <div class="section-body">
      <div class="row">
        <div class="col-12">
          <div class="card card-primary">
            <div class="card-header">
              <h4>Material Requests - MR</h4>
              <div class="card-header-action">
                <button class="btn btn-primary" data-toggle="modal" :data-target="'#' + formID" @click="clearForm">
                  Add New MR
                </button>
              </div>
            </div>
            <div class="card-body">
              <!-- MR List Table -->
              <div class="table-responsive">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>Sr No.</th>
                      <th>MR No</th>
                      <th>Date</th>
                      <th>Request By</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(mr, index) in mrs" :key="mr.id">
                      <td>{{ index + 1 }}</td>
                      <td>MR-{{ mr.id }}</td>
                      <td>{{ new Date(mr.created_at).toLocaleString() }}</td>
                      <td><span>{{ mr.requested_by_user?.name }}</span></td>
                      <td>
                        <span
                          class="badge text-white"
                          :class="{
                            'bg-danger': mr.status == 0,
                            'bg-warning': mr.status == 1,
                            'bg-success': mr.status == 2, 
                            'bg-info': mr.status == 3, 
                          }"
                        >
                          {{
                            mr.status == 0 ? 'Rejected' : mr.status == 1 ? 'Processing' : mr.status == 2
                              ? 'Issued' : mr.status == 3 ? 'PRN Generated' : 'Unknown'
                          }}
                        </span>
                      </td>
                      <td>
                        <button
                          v-if="mr.status != 3"
                          class="btn btn-danger btn-sm mx-1"
                          @click="confirmDelete(mr.id)"
                        >
                          <i class="fas fa-trash"></i>
                        </button>
                          <button class="btn btn-info btn-sm mx-1" @click="viewMR(mr)" data-toggle="modal" data-target="#viewMRModal">
                            <i class="fas fa-eye"></i>
                          </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- End Table -->
            </div>
          </div>
        </div>
      </div>

      <!-- Add MR Modal -->
      <div class="modal fade" :id="formID" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Add New Material Request</h5>
              <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <!-- Product select -->
                <div class="form-group col-md-4">
                  <label>Select Product</label>
                  <select v-model="singleProduct.product_id" class="form-control">
                    <option value="">Select</option>
                    <option v-for="prod in products" :key="prod.id" :value="prod.id">{{ prod.name }}</option>
                  </select>
                </div>

                <!-- Qty -->
                <div class="form-group col-md-3">
                  <label>Quantity</label>
                  <input type="number" class="form-control" v-model="singleProduct.qty">
                </div>

                <!-- Reason -->
                <div class="form-group col-md-4">
                  <label>Reason</label>
                  <textarea class="form-control" v-model="singleProduct.reason"></textarea>
                </div>

                <!-- Add Button -->
                <div class="form-group col-md-1">
                  <label>Action</label>
                  <button class="btn btn-success btn-sm" @click="addProduct">
                    <i class="fa fa-plus"></i>
                  </button>
                </div>
              </div>

              <!-- Products Added Table -->
              <div class="table-responsive mt-3" v-if="productsList.length > 0">
                <h5>Products Added</h5>
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Product</th>
                      <th>Qty</th>
                      <th>Reason</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in productsList" :key="index">
                      <td>{{ index + 1 }}</td>
                      <td>{{ getProductName(item.product_id) }}</td>
                      <td>{{ item.qty }}</td>
                      <td>{{ item.reason }}</td>
                      <td>
                        <button class="btn btn-danger btn-sm" @click="removeProduct(index)">
                          <i class="fa fa-trash"></i> Remove
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-primary" :disabled="loading" @click="submitMR">
                {{ loading ? 'Submitting...' : 'Submit' }}
              </button>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- View MR Details Modal -->
      <div class="modal fade" id="viewMRModal" tabindex="-1" role="dialog" v-if="selectedMR">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">MR Details - MR-{{ selectedMR.id }}</h5>
              <button type="button" class="close" @click="selectedMR = null">&times;</button>
            </div>
            <div class="modal-body">
              <table class="table table-bordered">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Product Name</th>
                  <th>Qty</th>
                  <th>Reason</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(detail, index) in selectedMR.details" :key="index">
                  <td>{{ index + 1 }}</td>
                  <td >
                    {{ detail.product.name }}
                  </td>

                  <td v-if="editingIndex === index">
                    <input type="number" v-model="editDetailData.qty" class="form-control" />
                  </td>
                  <td v-else>
                    {{ detail.qty }}
                  </td>

                  <td v-if="editingIndex === index">
                    <input type="text" v-model="editDetailData.reason" class="form-control" />
                  </td>
                  <td v-else>
                    {{ detail.reason }}
                  </td>

                  <td>
                    <!-- If editing, show Save/Cancel -->
                    <template v-if="editingIndex === index">
                      <button class="btn btn-success btn-sm mx-1" @click="saveDetail(index)">Save</button>
                      <button class="btn btn-secondary btn-sm" @click="cancelEdit()">Cancel</button>
                    </template>

                    <!-- Otherwise, show Edit/Delete -->
                    <template v-else>
                      <button class="btn btn-primary btn-sm mx-1" @click="editDetail(detail, index)">Edit</button>
                      <button class="btn btn-danger btn-sm" @click="deleteDetail(detail.id, index)">Delete</button>
                    </template>
                  </td>
                </tr>
              </tbody>
            </table>

            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" @click="selectedMR = null">Close</button>
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
  data() {
    return {
      formID: 'addMRForm',
      products: [],
      mrs: [],
      singleProduct: { product_id: '', qty: '', reason: '' },
      productsList: [],
      selectedMR: null,
      loading: false,
      deleteId: null,
      editingIndex: null, 
      selectedMR: {
      details: [] // full list
    },
    editedDetails: [] // only edited rows
    };
  },
  mounted() {
    this.fetchMRs();
  },
  methods: {
    async fetchMRs() {
      try {
        const response = await this.callApi('post', 'mr');  // Correct your API endpoint here
        this.mrs      = response.data.data;
        this.products = response.data.products;
        this.users    = response.data.users;
      } catch (error) {
        console.error(error);
      }
    },
    getProductName(id) {
      const product = this.products.find(p => p.id == id);
      return product ? product.name : 'Unknown';
    },
    addProduct() {
      if (!this.singleProduct.product_id || !this.singleProduct.qty) {
        Swal.fire('Error', 'Please select product and quantity.', 'error');
        return;
      }
      this.productsList.push({...this.singleProduct});
      this.singleProduct = { product_id: '', qty: '', reason: '' };
    },
    removeProduct(index) {
      this.productsList.splice(index, 1);
    },
    async submitMR() {
      if (this.productsList.length === 0) {
        Swal.fire('Error', 'Add products before submitting.', 'error');
        return;
      }
      try {
        const payload = { details: this.productsList };
        await this.callApi('post', 'mr/store', payload);
        Swal.fire('Success', 'MR submitted successfully!', 'success');
        this.fetchMRs();
        this.clearForm();
      } catch (error) {
        Swal.fire('Error', error.response?.data?.message || 'Something went wrong!', 'error');
      }
    },
    clearForm() {
      this.productsList = [];
      this.singleProduct = { product_id: '', qty: '', reason: '' };
    },
    viewMR(mr) {
      this.selectedMR = mr;
    },
   
  editDetail(detail, index) {
    this.editingIndex = index;
    this.editDetailData = JSON.parse(JSON.stringify(detail)); // deep clone
  },
  cancelEdit() {
    this.editingIndex = null;
    this.editDetailData = {};
  },

  async saveDetail(index) {
  try {
    if (!this.editDetailData || !this.editDetailData.id) {
      throw new Error('No detail selected for update.');
    }

    const payload = {
      id: this.editDetailData.id,
      qty: this.editDetailData.qty,
      reason: this.editDetailData.reason,
    };

    const response = await this.callApi('post', 'mr/detail-update', payload);

    if (response.data && response.data.success) {
      // Update locally (correct way in Vue 3)
      this.selectedMR.details[index] = { ...this.editDetailData };

      this.cancelEdit();

      Swal.fire('Success', 'MR Updated successfully!', 'success');
    } else {
      Swal.fire('Error', error.response?.data?.message || 'Failed to update Material Request Detail!', 'error');
    }
  } catch (error) {
    Swal.fire('Error', error.response?.data?.message || 'Something went wrong!', 'error');
  }
},

async deleteDetail(id, index) {
  try {
    const confirm = await Swal.fire({
      title: 'Are you sure?',
      text: 'You will not be able to recover this detail!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it!',
    });

    if (confirm.isConfirmed) {
      const response = await this.callApi('post', 'mr/detail-delete', { id });
      if (response.data && response.data.success) {
        // Remove detail from local array
        this.selectedMR.details.splice(index, 1);
        Swal.fire('Success', 'Deleted successfully!', 'success');
      } else {
        Swal.fire('Error', error.response?.data?.message || 'Something went wrong!', 'error');
      }
    }
  } catch (error) { 
    Swal.fire('Error', error.response?.data?.message || 'Failed to delete!', 'error');
  }
},
async confirmDelete(id) {
  try {
    const confirm = await Swal.fire({
      title: 'Are you sure?',
      text: 'This will permanently delete this Material Request!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'Cancel'
    });

    if (confirm.isConfirmed) {
      // User clicked "Yes, delete it!"
      const response = await this.callApi('post', 'mr/mr-delete', { id });

      if (response && response.data && response.data.success) {
        Swal.fire('Success', 'Deleted successfully!', 'success');

        // Reload the list or remove the item locally
        this.fetchMRs(); // or whatever you use to refresh
      } else {
        Swal.fire('Error', error.response?.data?.message || 'Something went wrong!', 'error');
      }
    } 
    // else: User clicked "Cancel" => Do nothing
  } catch (error) {
    console.error('Delete MR error:', error);
    Swal.fire('Error', error.response?.data?.message || 'Failed to delete!', 'error');
  }
}

  }
}
</script>
