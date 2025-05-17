<template>
    <section class="section">
      <div class="section-body">
        <div class="row">
          <div class="col-12">
            <div class="card card-primary">
              <div class="card-header">
                <h4>Outward`s</h4>
                <div class="card-header-action">
                  <a href="#" class="btn btn-primary" data-toggle="modal" :data-target="'#' + formID" @click="clearForm">
                    Add New OutWard
                  </a>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-hover">
                    <thead>
                      <tr>
                        <th>Sr No.</th>
                        <th>Invoice#</th>   
                        <th>Date</th>
                        <th>Request By</th>
                        <th>Assigned By</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, index) in outWards" :key="item.id">
                        <td>{{ index + 1 }}</td>
                        <td>Invoice-{{ item.invoice_no }}</td>
                        <td>{{ item.date }}</td> 
                        <td v-if="editId === item.id">
                          <input v-model="item.requestBy" disabled class="form-control form-control-sm" />
                        </td>
                        <td v-else>{{ item.requestBy }}</td>
  
                        <td v-if="editId === item.id">
                          <input v-model="item.assignedBy" disabled class="form-control form-control-sm" />
                        </td>
                        <td v-else>{{ item.assignedBy }}</td>
                        <td>
                          <button v-if="editId === item.id" class="btn btn-success btn-sm" @click="savePO(item)">Save</button>
                          <button v-else class="btn btn-primary btn-sm mx-1" @click="editPO(item)">
                            <i class="far fa-edit"></i>
                          </button>
                          <button class="btn btn-danger btn-sm mx-1" data-toggle="modal" data-target="#deleteConfirmModal"  @click="deleteConfirm(item.id)">
                            <i class="fas fa-trash"></i>
                          </button>
                          <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewMRModal" @click="viewMR(item.id)">
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
  
        <!-- Add Supplier Modal -->
        <Add :heading="'Add New Outward'" :errors="validationErrors" :success="success" :formID="formID">
           
            <div class="row" v-for="(item, index) in outWardFormRows" :key="item.id">
            <div class="form-group col-md-4">
              <div class="d-flex justify-content-between">
                <label>Select Product</label>
                <button class="btn btn-primary p-0 m-0 px-2" data-toggle="modal" data-target="#addProducts">Add New</button>
              </div>
                <select v-model="data.product_name" class="form-control select2">
                  <option value="">Select</option>
                  <option v-for="cat in products" :key="cat" :value="cat">{{ cat }}</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label>Quantity <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="item.qty" />
            </div>  
            <div class="form-group col-md-2">
                <label>Action</label>
                <div>
                    <button class="btn btn-info" @click="addRow">
                        <i class="fa fa-plus"></i>
                    </button>
                    <button class="btn btn-danger mx-1" @click="removeRow(index)" v-if="outWardFormRows.length > 1">
                        <i class="fa fa-minus"></i>
                    </button> 
                </div>
            </div>
        </div>
        
        <template v-slot:button>
          <button type="button" class="btn btn-primary" :disabled="loading" @click="add">
            {{ loading ? 'Loading...' : 'Add' }}
          </button>
        </template>
 
  
          <div class="table-responsive mt-3">
            <h5>Outward`s</h5>
            <table class="table table-striped table-hover">
                <thead>
                      <tr>
                        <th>Sr No.</th>
                        <th>Invoice#</th>   
                        <th>Date</th>
                        <th>Request By</th>
                        <th>Assigned By</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, index) in outWards" :key="item.id">
                        <td>{{ index + 1 }}</td>
                        <td>Invoice-{{ item.invoice_no }}</td>
                        <td>{{ item.date }}</td> 
                        <td v-if="editId === item.id">
                          <input v-model="item.requestBy" disabled class="form-control form-control-sm" />
                        </td>
                        <td v-else>{{ item.requestBy }}</td>
  
                        <td v-if="editId === item.id">
                          <input v-model="item.assignedBy" disabled class="form-control form-control-sm" />
                        </td>
                        <td v-else>{{ item.assignedBy }}</td>
                        <td>
                          <button v-if="editId === item.id" class="btn btn-success btn-sm" @click="savePO(item)">Save</button>
                          <button v-else class="btn btn-primary btn-sm mx-1" @click="editPO(item)">
                            <i class="far fa-edit"></i>
                          </button>
                          <button class="btn btn-danger btn-sm mx-1" data-toggle="modal" data-target="#deleteConfirmModal1"  @click="deleteConfirm(item.id)">
                            <i class="fas fa-trash"></i>
                          </button>
                          <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewMRModal" @click="viewMR(item.id)">
                            <i class="fas fa-eye"></i>
                          </button>
                        </td>
                      </tr>
                    </tbody>
            </table>
          </div>
        </Add>  
        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteConfirmModal1" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
          <div class="modal-content shadow border-0">
            <div class="modal-header py-2 bg-danger">
              <h5 class="modal-title text-white" id="actionModalLabel">
                <i class="mr-2 fas fa-trash-alt"></i> Delete OutWard</h5>
              <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
              <p class="mb-0 font-weight-semibold text-dark">Are you sure you want to permanently delete this OutWard?</p>
              <small class="text-muted">This action cannot be undone.</small>
            </div>
            <div class="modal-footer justify-content-end py-2">
              <button class="btn btn-sm btn-danger">
                <i class="fas fa-trash-alt"></i> Yes, Delete
              </button>
              <button class="btn btn-sm btn-outline-secondary" data-dismiss="modal">
                <i class="fas fa-times"></i> Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
         <!-- view MR Detail Modal -->
         <div class="modal fade" id="viewMRModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content pt-3">
              <div class="modal-header py-2">
                <h5 class="modal-title">Invoice-012</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
              </div>
              <div class="modal-body">
                <div class="table-responsive mt-3">
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>Sr No.</th> 
                  <th>Product Name</th> 
                  <th>Quantity</th> 
                </tr>
              </thead>
              <tbody>
                      <tr v-for="(po, index) in outWards" :key="po.id">
                        <td>{{ index + 1 }}</td> 
                        <td>{{ po.product_name }}</td>
                        <td>{{ po.qty }}</td>
                      </tr>
                    </tbody>
            </table>
          </div>
              </div>
              <div class="modal-footer py-2"> 
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
        formID: 'OutWard',
        editId: null,
        deleteId: null,
        data: {
          product_name: '',
          invoice_no: '',
          qty: '',
          date: '',
          requestBy: '',
          assignedBy: ''
        },
        outWards: [
          { 
          supplier: '',
            id: 1, product_name: 'Mouse',invoice_no: '012', qty: '15', date: '12-04-25',requestBy: 'Ali', assignedBy: 'raza' },
        ],
        products: ['Electronics', 'Furniture', 'Vehicles', 'Tools'],

        outWardFormRows: [    // This is just for the form
        {
            id: Date.now(),
            product_name: '',
            invoice_no: '',
            qty: '',
            date: new Date().toLocaleDateString('en-GB'),
            requestBy: 'Ali',
            assignedBy: 'Raza'
        }
        ],
        isModalOpen: false,
            loading: false,
            validationErrors: [],
            success: '',
        };
        },
        methods: {
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
        addRow() {
        this.outWardFormRows.push({
            id: Date.now(),
            product_name: '',
            invoice_no: '',
            qty: '',
            requestBy: 'Ali',
            assignedBy: 'Raza'
        });
        },
        removeRow(index) {
        this.outWardFormRows.splice(index, 1);
        },
        submitForm() {
        this.outWards.push(...this.outWardFormRows); // Now adds form rows to the main table
        this.isModalOpen = false;
        this.clearForm(); // Optional: clears the modal
        },


      add() {
        if (!this.data.product_name || !this.data.qty) {
          alert('All fields are required.');
          return;
        }
  
        const newPO = {
          id: Date.now(),
          product_name: this.data.product_name,
          invoice_no: this.data.invoice_no,
          qty: this.data.qty,
          date: new Date().toLocaleDateString('en-GB'),
          requestBy: 'System',  // or fetch actual user
          assignedBy: 'Admin',  // or fetch actual assigner
        };
  
        this.outWards.push(newPO);
        this.clearForm();
        $('#' + this.formID).modal('hide');
      },
      editPO(item) {
        this.editId = item.id;
      },
      savePO(item) {
        this.editId = null;
      },
      viewMR(id) {
        this.deleteId = id;
      },
      deleteConfirm(id) {
        this.deleteId = id;
      },
      
    }
  };
  </script>
  