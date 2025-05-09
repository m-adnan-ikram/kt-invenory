<template>
     <div class="modal fade" id="addProductUnitModal" tabindex="-1" aria-labelledby="seatAllDetailsModalLabel"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="">Add New Product Unit</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name">Name <span class="text-danger ml-1">*</span></label>
                                <input v-model="newUnit" type="text" class="form-control" placeholder="Enter Name">
                            </div> 
                        </div>
                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                      <button type="submit" class="btn btn-primary" @click="createUnit">Add</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            close
                        </button>
                    </div>
                    <div class="card m-2 p-2">  
                        <div class="table-responsive">
                          <table class="table table-striped table-hover">
        <thead>
          <h5 class="modal-title" id="">Product Units</h5>
          <tr>
            <th>Sr No.</th>
            <th>Unit Name</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <tr v-for="(unit, index) in unitData" :key="unit.id">
          <td>{{ index + 1 }}</td>
          <td>
            <input v-if="editIndex === index" v-model="editUnit" class="form-control" />
            <span v-else>{{ unit.name }}</span>
          </td>
          <td>{{ formatDate(unit.created_at) }}</td>
          <td>
            <button v-if="editIndex === index" class="btn btn-success" @click="submitUnitEdit()">Save</button>
            <button v-else class="btn btn-primary" @click="editUnitRow(index, unit)"><i class="far fa-edit"></i></button>
            <button class="btn btn-danger ml-1" @click="confirmUnitDelete(unit.id, index)">
              <i class="fas fa-trash"></i>
            </button>
          </td>
        </tr>
      </tbody>
      </table>
                  </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal">
      <div class="modal-backdrop fade show"></div>
      <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Confirm Delete</h5>
              <button type="button" class="close" @click="showDeleteModal = false">
                <span>&times;</span>
              </button>
            </div>
            <div class="modal-body">
              Are you sure you want to delete this Product Unit?
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" @click="showDeleteModal = false">Cancel</button>
              <button class="btn btn-danger" @click="deleteUnitRow">Delete</button>
            </div>
          </div>
        </div>
      </div>
    </div>

                    </div>
                </div>
            </div>
        </div>
</template>

<script>
import Swal from 'sweetalert2';
import axios from 'axios';

export default {
  data() {
    return {
      unitData: [],
      newUnit: '',
      editUnit: '',
      editIndex: null,
      editId: null,
    };
  },
  mounted() {
    this.fetchUnits();
  },
  methods: {
    formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString();
  },
  async fetchUnits() {
    try {
      const res = await this.callApi('post', 'inventory-product-unit');
      this.unitData = res.data.data;
    } catch (err) {
      console.error(err);
    }
  },

    async createUnit() {
    if (!this.newUnit) return;
    try {
      const res = await this.callApi('post', 'inventory-product-unit/store', {
        unit: this.newUnit,
      });

      const newUnit = res.data.data;
      this.unitData.unshift(newUnit);

      this.newUnit = '';
      Swal.fire('Success', 'Unit added successfully!', 'success');
    } catch (err) {
      console.error(err.response?.data || err);
      Swal.fire('Error', 'Could not add category.', 'error');
    }
  },

  editUnitRow(index, unit) {
  this.editIndex = index;
  this.editUnit  = unit.name; // Correct field here
  this.editId    = unit.id;
},

async submitUnitEdit() {
  try {
    const res = await this.callApi("post", "inventory-product-unit/update", {
      id: this.editId,
      name: this.editUnit,
    });

    // Update local data directly without re-fetching
    if (this.editIndex !== null) {
      this.unitData[this.editIndex].name = this.editUnit;
    }

    this.editIndex = null;
    this.editUnit = '';
    this.editId = null;

    Swal.fire('Updated', 'Unit updated successfully!', 'success');
  } catch (err) {
    console.error(err.response?.data || err);
    Swal.fire('Error', 'Failed to update unit.', 'error');
  }
},

async confirmUnitDelete(id, index) {
  const result = await Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#aaa',
    confirmButtonText: 'Yes, delete it!',
  });

  if (result.isConfirmed) {
    this.deleteUnitRow(id, index);
  }
},

async deleteUnitRow(id, index) {
  try {
    await this.callApi("post", "inventory-product-unit/delete", { id });

    // Instantly remove from local array
    this.unitData.splice(index, 1);

    Swal.fire('Deleted!', 'Unit has been deleted.', 'success');
  } catch (err) {
    console.error(err.response?.data || err);
    Swal.fire('Error', 'Could not delete unit.', 'error');
  }
},
  }
};

</script>

