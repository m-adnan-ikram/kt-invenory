<template>
    <section class="section">
      <div class="section-body">
        <div class="row">
          <div class="col-12">
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
                      <tr v-for="(po, index) in pos" :key="po.id">
                        <td>{{ index + 1 }}</td>
                        <td>PO - {{ po.po_no }}</td> 
                        <td>{{ po.date }}</td>
                        <td v-if="editId === po.id">
                          <select v-model="po.supplier" class="form-control form-control-sm">
                            <option value="">Select</option>
                            <option v-for="cat in suppliers" :key="cat">{{ cat }}</option>
                          </select>
                        </td>
                        <td>{{ po.supplier }}</td>
                        <td>{{ po.receivedBy }}</td>
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
        </div>
  
        <!-- Add Supplier Modal -->
        <Add :heading="'Add New Inward'" :errors="validationErrors" :success="success" :formID="formID">
           
            <div class="row" v-for="(item, index) in pos" :key="item.id">
            <!-- Product Name (Disabled) -->
            <div class="form-group col-md-3">
                <label>Product Name</label>
                <input type="text" class="form-control" :value="item.product_name" disabled />
            </div>

            <!-- Receivable Quantity (Disabled with Dummy Value) -->
            <div class="form-group col-md-3">
                <label>Receivable Quantity <span class="text-danger">*</span></label>
                <input type="text" class="form-control" :value="item.qty" disabled />
            </div>

              <!-- Already Received Quantity (Disabled with Dummy Value) -->
              <div class="form-group col-md-3">
                <label>Already Received Quantity <span class="text-danger">*</span></label>
                <input type="text" class="form-control" :value="item.alreadey_received_qty" disabled />
            </div>

            <!-- Received Quantity (Editable) -->
            <div class="form-group col-md-3">
                <label>Received Quantity <span class="text-danger">*</span></label>
                <input type="text" class="form-control" placeholder="Enter Received Quantity" />
                <small class="text-danger">You Can Receive just '<b>12</b>' against this PO.</small>
            </div>

            </div>
        <template v-slot:button>
          <button type="button" class="btn btn-primary" :disabled="loading" @click="add">
            {{ loading ? 'Loading...' : 'Add' }}
          </button>
        </template>
        </Add>

         <!-- view MR Detail Modal -->
         <div class="modal fade" id="viewMRModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
              <div class="modal-header py-2">
                <h5 class="modal-title">PO-012</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
              </div>
              <div class="modal-body">
                <div class="table-responsive mt-3">
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>Sr No.</th> 
                  <th>Product Name</th> 
                  <th>Receivalble Quantity</th>  
                  <th>Already Received Quantity</th>  
                  <th>Received Quantity</th>  
                </tr>
              </thead>
              <tbody>
                      <tr v-for="(po, index) in pos" :key="po.id">
                        <td>{{ index + 1 }}</td> 
                        <td>{{ po.product_name }}</td>
                        <td>{{ po.qty }}</td> 
                        <td>{{ po.received_qty }}</td> 
                        <td>{{ po.alreadey_received_qty }}</td> 
                      </tr>
                    </tbody>
            </table>
          </div>
              </div>
              <div class="modal-footer py-2"> 
                <button class="btn btn-secondary btn-sm" data-dismiss="modal">close</button>
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
        pos: [
          { 
            id: 1,
            product_name: 'Mouse',
            po_no: '03',
            qty: '15',
            received_qty: '12', 
            alreadey_received_qty: '3', 
            date: '12-04-25',
            status: '1',  
            supplier: 'Ali ',
            receivedBy: '-',
          },
          {
            id: 2,
            product_name: 'Keyboard',
            po_no: '001',
            qty: '20',
            received_qty: '6', 
            alreadey_received_qty: '8', 
            date: '13-04-25',
            status: '2',
            supplier: 'Another Supplier',
            receivedBy: 'Admin',
          }
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
            supplier: 'test',
            receivedBy: 'Admin',
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
        addRow() {
        this.poFormRows.push({
            id: Date.now(),
            product_name: '',
            po_no: '',
            qty: '',
            received_qty: '', 
            alreadey_received_qty: '', 
            date: new Date().toLocaleDateString('en-GB'),
            status: '',  
            supplier: 'test',
            receivedBy: 'Admin',
        });
        },
        removeRow(index) {
        this.poFormRows.splice(index, 1);
        },
        submitForm() {
        this.pos.push(...this.poFormRows); // Now adds form rows to the main table
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
          po_no: this.data.po_no,
          qty: this.data.qty,
          received_qty: this.data.received_qty,
          alreadey_received_qty: this.data.alreadey_received_qty,
          date: new Date().toLocaleDateString('en-GB'),
          status: '',  
          supplier: 'System',  // or fetch actual user
          receivedBy: 'Admin',  // or fetch actual assigner
        };
  
        this.pos.push(newPO);
        this.clearForm();
        $('#' + this.formID).modal('hide');
      },
      savePO(po) {
        this.editId = null;
      },
      viewMR(id) {
        this.deleteId = id;
      },
    }
  };
  </script>
  