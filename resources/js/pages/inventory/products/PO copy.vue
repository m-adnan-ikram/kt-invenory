<template>
    <section class="section">
      <div class="section-body">
        <div class="row">
          <div class="col-12 mt-4">
          <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
              <a class="nav-link" :class="{ active: activeTab === 'po' }" href="#" @click.prevent="activeTab = 'po'">
                <b>
                  Purchase Order - POs
                  <span class="badge badge-primary">{{ pos.length }}</span>
                </b>
              </a>
            </li>
            <li class="nav-item ml-2">
              <a class="nav-link" :class="{ active: activeTab === 'bid' }" href="#" @click.prevent="activeTab = 'bid'">
                <b>Bid Summaries</b>
              </a>
            </li>
          </ul>
        </div>

           <!-- POs Table -->
          <div class="col-12" v-if="activeTab === 'po'">
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
                        <th>MR #</th> 
                        <th>Total Amount</th> 
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Request By</th>
                        <th>Approved By</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(po, index) in pos" :key="po.id">
                        <td>{{ index + 1 }}</td>
                        <td>PO - {{ po.po_no }}</td>
                        <td>MR - {{ po.mr_no }}</td>
                        <td>{{ po.net_amount }}</td>

                        <td>{{ po.date }}</td>

                        <td v-if="editId === po.id">
                          <select v-model="po.supplier" class="form-control form-control-sm">
                            <option value="">Select</option>
                            <option v-for="cat in suppliers" :key="cat">{{ cat }}</option>
                          </select>
                        </td>
                        <td v-else>{{ po.supplier }}</td>

                        <td v-if="editId === po.id">
                          <input v-model="po.requestBy" disabled class="form-control form-control-sm" />
                        </td>
                        <td v-else>{{ po.requestBy }}</td>
  
                        <td v-if="editId === po.id">
                          <input v-model="po.assignedBy" disabled class="form-control form-control-sm" />
                        </td>
                        <td v-else>{{ po.assignedBy }}</td>
                        <td>
                          <span v-if="po.status == '2'" class="badge badge-success">Approved</span>
                          <span v-else-if="po.status == '1'" class="badge badge-danger">Rejected</span>
                          <span v-else class="badge badge-warning">Pending</span>
                        </td>
                        <td>
                          <!-- Buttons -->
                         <!-- Buttons visible only for Pending or Rejected -->
                        <button v-if="po.status === '0' || po.status === '1'" 
                                class="btn btn-success btn-sm" 
                                :data-target="'#' + formID" 
                                data-toggle="modal" 
                                @click="clearForm">
                          <i class="fas fa-check"></i>
                        </button>

                        <button v-if="po.status === '0' || po.status === '1'" 
                                class="btn btn-danger btn-sm mx-1" 
                                data-toggle="modal" 
                                data-target="#actionConfirmModal" 
                                @click="openModal('reject', po.id)">
                          <i class="fas fa-times"></i>
                        </button>  
                          <button class="btn btn-danger btn-sm mx-1" data-toggle="modal" data-target="#actionConfirmModal" @click="openModal('delete', po.id)">
                            <i class="fas fa-trash"></i>
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
                        <button class="btn btn-info btn-sm"
                            @click="viewBID(bid.id)">
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
        </div>
  
        <!-- Add Supplier Modal -->
        <Add :heading="'PO Request'" :errors="validationErrors" :success="success" :formID="formID">
          <div class="modal-content">
                <div class="modal-header py-2 bg-primary text-white">
                    <h5 class="modal-title">
                    <i class="fas fa-file-alt mr-2"></i> Material Request Detail - MR# {{ selectedMR?.mr_no || 'N/A' }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" >
                    <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <!-- Loop through each bidder -->
                    <div v-if="selectedMR && selectedMR.bidders && selectedMR.bidders.length">
                    <div v-for="(bidder, index) in selectedMR.bidders" :key="index" class="mb-4 border rounded p-3">
                        <h6 class="font-weight-bold text-primary">
                        Bidder {{ index + 1 }}: {{ bidder.supplier || 'Unnamed Supplier' }}
                        </h6>

                        <!-- Quotation Info -->
                        <div class="row mb-3">
                        <div class="col-md-12"><strong>Supplier:</strong> {{ bidder.supplier }}</div>
                        <div class="col-md-4"><strong>Quotation Ref:</strong> {{ bidder.quotation_ref }}</div>
                        <div class="col-md-4"><strong>Date:</strong> {{ bidder.quotation_date }}</div>
                        <div class="col-md-4"><strong>Contact Person:</strong> {{ bidder.contact_person }}</div>
                        </div>

                        <!-- Bid Table -->
                        <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Rate</th>
                                <th>Subtotal</th>
                                <th>Tax</th>
                                <th>Delivery</th>
                                <th>Discount</th>
                                <th>Net Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item, i) in bidder.products" :key="i">
                                <td>{{ i + 1 }}</td>
                                <td>{{ item.product_name }}</td>
                                <td>{{ item.qty }}</td>
                                <td>{{ item.price }}</td>
                                <td>{{ item.sub_total }}</td>
                                <td>{{ item.tax_amount }}</td>
                                <td>{{ item.delivery_amount }}</td>
                                <td>{{ item.discount }}</td>
                                <td>{{ item.net_amount }}</td>
                            </tr>
                            </tbody>
                        </table>
                        </div>

                        <!-- Summary -->
                        <div class="row mt-2">
                        <div class="col-md-3"><strong>Advance (%):</strong> {{ bidder.advance_percent }}</div>
                        <div class="col-md-3"><strong>After Delivery (%):</strong> {{ bidder.after_delivery_percent }}</div>
                        <div class="col-md-3"><strong>Credit Days:</strong> {{ bidder.credit_days }}</div>
                        <div class="col-md-3">
                          <strong>Desicion</strong>
                          <button
                                class="btn btn-success btn-sm" 
                                :data-target="'#' + formID" 
                                data-toggle="modal" 
                                @click="clearForm"> Approve
                          <i class="fas fa-check"></i>
                        </button>

                        <button
                                class="btn btn-danger btn-sm mx-1" 
                                data-toggle="modal" 
                                data-target="#actionConfirmModal" 
                              > Reject
                          <i class="fas fa-times"></i>
                        </button>  
                        </div>
                        </div>
                    </div>
                    </div>
                    <div v-else class="text-muted text-center">
                    No bidder data available for this MR.
                    </div>
                </div>

                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                    </button>
                </div>
                </div>
        
        <template v-slot:button>
          <button type="button" class="btn btn-primary" :disabled="loading" @click="add">
            {{ loading ? 'Loading...' : 'Add' }}
          </button>
        </template>

        </Add>
  
      <!-- Reusable Action Confirmation Modal -->
      <div class="modal fade" id="actionConfirmModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
          <div class="modal-content shadow border-0">
            <div class="modal-header py-2" :class="modalData.headerClass">
              <h5 class="modal-title text-white" id="actionModalLabel">
                <i :class="modalData.iconClass" class="mr-2"></i>{{ modalData.title }}
              </h5>
              <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
              <p class="mb-0 font-weight-semibold text-dark">{{ modalData.message }}</p>
              <small class="text-muted" v-if="modalData.note">{{ modalData.note }}</small>
            </div>
            <div class="modal-footer justify-content-end py-2">
              <button class="btn btn-sm" :class="modalData.btnClass" @click="confirmAction">
                <i :class="modalData.btnIcon"></i> {{ modalData.btnText }}
              </button>
              <button class="btn btn-sm btn-outline-secondary" data-dismiss="modal">
                <i class="fas fa-times"></i> Cancel
              </button>
            </div>
          </div>
        </div>
      </div>

         <!-- view Bid Detail Modal -->
         <div class="modal fade" id="viewMRModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
              <div class="modal-header py-2">
                <h5 class="modal-title">MR-012</h5>
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
                  <th>Price</th> 
                  <th>Sub Total</th> 
                  <th>Tax Amount</th> 
                  <th>Delivery Charges</th> 
                  <th>Dicount</th> 
                  <th>Net Amount</th> 
                </tr>
              </thead>
              <tbody>
                      <tr v-for="(po, index) in pos" :key="po.id">
                        <td>{{ index + 1 }}</td> 
                        <td>{{ po.product_name }}</td>
                        <td>{{ po.qty }}</td>
                        <td>{{ po.price }}</td>
                        <td>{{ po.sub_total }}</td>
                        <td>{{ po.tax_amount }}</td>
                        <td>{{ po.delivery_amount }}</td>
                        <td>{{ po.dicount }}</td>
                        <td>{{ po.net_amount}}</td>
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
        
        <AddSupplierModal></AddSupplierModal>
        <AddProductModal></AddProductModal>
      </div>
    </section>
  </template>
  
  <script>
  import Add from '../../../components/Add.vue'; 
  import AddSupplierModal from '../modal/addSupplierModal.vue'; 
  import AddProductModal from '../modal/addProductsModal.vue'; 
  
  export default {
    name: "POManager",
    activeTab: 'po',
    components: {
      Add, 
      AddSupplierModal,
      AddProductModal,
    },
    data() {
      return {
        selectedMR: {
        mr: '012',
        bidders: [
            {
            supplier: 'Ali Traders',
            quotation_ref: 'QUO-001',
            quotation_date: '2025-04-10',
            contact_person: 'Mr. Ali',
            advance_percent: '20',
            after_delivery_percent: '80',
            credit_days: '30',
            status: '0', // Pending
            products: [
                {
                product_name: 'Mouse',
                qty: 10,
                price: 200,
                sub_total: 2000,
                tax_amount: 0,
                delivery_amount: 100,
                discount: 50,
                net_amount: 2050
                },
                {
                product_name: 'Keyboard',
                qty: 5,
                price: 500,
                sub_total: 2500,
                tax_amount: 0,
                delivery_amount: 100,
                discount: 100,
                net_amount: 2500
                }
            ]
            },
            {
            supplier: 'Zain Enterprises',
            quotation_ref: 'QUO-002',
            quotation_date: '2025-04-11',
            contact_person: 'Mr. Zain',
            advance_percent: '30',
            after_delivery_percent: '70',
            credit_days: '20',
            status: '1', // Rejected
            products: [
                {
                product_name: 'Mouse',
                qty: 10,
                price: 190,
                sub_total: 1900,
                tax_amount: 0,
                delivery_amount: 80,
                discount: 20,
                net_amount: 1960
                }
            ]
            },
            {
            supplier: 'Rehan Supplies',
            quotation_ref: 'QUO-003',
            quotation_date: '2025-04-12',
            contact_person: 'Mr. Rehan',
            advance_percent: '10',
            after_delivery_percent: '90',
            credit_days: '10',
            status: '2', // Approved
            products: [
                {
                product_name: 'Mouse',
                qty: 10,
                price: 185,
                sub_total: 1850,
                tax_amount: 0,
                delivery_amount: 50,
                discount: 0,
                net_amount: 1900
                }
            ]
            }
        ]
        },
        formID: 'addPOForm',
        editId: null,
        deleteId: null,
        modalData: {
        title: '',
        message: '',
        note: '',
        btnText: '',
        btnClass: '',
        btnIcon: '',
        headerClass: '',
        iconClass: '',
        action: null
       },
        data: {
          product_name: '',
          po_no: '',
          mr_no: '',
          qty: '',
          price: '',
          date: '',
          sub_total: '',
          tax_amount:'',
          delivery_amount: '',
          dicount: '',
          net_amount: '',
          status: '',
          supplier: '',
          requestBy: '',
          assignedBy: ''
        },
        pos: [
          { 
            id: 1,
            product_name: 'Mouse',
            po_no: '-',
            mr_no: '02',
            qty: '15',
            price: '-',
            date: '12-04-25',
            sub_total: '-',
            tax_amount: '-',
            delivery_amount: '-',
            dicount: '-',
            net_amount: '-',
            requestBy: 'Ali',
            status: '0',
            supplier: '-',
            assignedBy: '-'
          },
          {
            id: 2,
            product_name: 'Keyboard',
            po_no: '001',
            mr_no: '01',
            qty: '10',
            price: '200',
            date: '13-04-25',
            sub_total: '2000',
            tax_amount: '50',
            delivery_amount: '150',
            dicount: '200',
            net_amount: '2000',
            requestBy: 'Zain',
            status: '2',
            supplier: 'Another Supplier',
            assignedBy: 'Hassan'
          }
        ],
        suppliers: ['Electronics', 'Furniture', 'Vehicles', 'Tools'],
        products: ['Electronics', 'Furniture', 'Vehicles', 'Tools'],

        poFormRows: [    // This is just for the form
        {
            id: Date.now(),
            product_name: '',
            po_no: '',
            mr_no: '',
            qty: '',
            price: '',
            date: new Date().toLocaleDateString('en-GB'),
            sub_total: '',
            tax_amount:'',
            delivery_amount: '',
            dicount: '',
            net_amount: '',
            status: '',
            supplier: 'test',
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
          async fetchBid_PO() {
          try {
            const response = await this.callApi('get', 'pos'); // fixed GET
            if (response.data.success) {
              this.bids      = response.data.bids;
              this.prns      = response.data.prns;
              this.products  = response.data.products || [];
              this.suppliers = response.data.suppliers || [];
              this.pos       = response.data.pos || []; // fix: add this
            }
          } catch (error) {
            console.error('Failed to fetch bids and PRNs:', error);
          }
        },
        clearForm() {
            this.data = {
            product_name: '',
            po_no: '',
            mr_no: '',
            qty: '',
            price: '',
            date: '',
            sub_total: '',
            tax_amount:'',
            delivery_amount: '',
            dicount: '',
            net_amount: '',
            status: '',
            supplier: '',
            requestBy: '',
            assignedBy: ''
            };
        },
        addRow() {
        this.poFormRows.push({
            id: Date.now(),
            product_name: '',
            po_no: '',
            mr_no: '',
            qty: '',
            price: '',
            sub_total: '',
            tax_amount:'',
            delivery_amount: '',
            dicount: '',
            net_amount: '',
            date: new Date().toLocaleDateString('en-GB'),
            status: 'Pending',
            supplier: 'test',
            requestBy: 'Ali',
            assignedBy: 'Raza'
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
          mr_no: this.data.mr_no,
          qty: this.data.qty,
          price: this.data.price,
          sub_total:this.data.sub_total,
          tax_amount:this.data.tax_amount,
          delivery_amount: this.data.delivery_amount,
          dicount: this.data.dicount,
          net_amount: this.data.net_amount,
          date: new Date().toLocaleDateString('en-GB'),
          status: 'Pending',  // or fetch actual user
          supplier: 'System',  // or fetch actual user
          requestBy: 'System',  // or fetch actual user
          assignedBy: 'Admin',  // or fetch actual assigner
        };
  
        this.pos.push(newPO);
        this.clearForm();
        $('#' + this.formID).modal('hide');
      },
      editPO(po) {
        this.editId = po.id;
      },
      savePO(po) {
        this.editId = null;
      },
      confirmDelete(id) {
        this.deleteId = id;
      },
      deletePO() {
        this.pos = this.pos.filter(po => po.id !== this.deleteId);
        this.deleteId = null;
        $('#deleteConfirmModal').modal('hide');
      },
      viewMR(id) {
        this.deleteId = id;
      },
      openModal(type, id) {
    this.deleteId = id;

    const config = {
      approve: {
        title: 'Approve PO',
        message: 'Are you sure you want to approve this Purchase Order?',
        btnText: 'Yes, Approve',
        btnClass: 'btn-success',
        btnIcon: 'fas fa-check',
        headerClass: 'bg-success',
        iconClass: 'fas fa-check-circle',
        action: this.approvePOAction
      },
      reject: {
        title: 'Reject PO',
        message: 'Are you sure you want to reject this Purchase Order?',
        btnText: 'Yes, Reject',
        btnClass: 'btn-danger',
        btnIcon: 'fas fa-times',
        headerClass: 'bg-danger',
        iconClass: 'fas fa-times-circle',
        action: this.rejectPOAction
      },
      delete: {
        title: 'Delete PO',
        message: 'Are you sure you want to permanently delete this Purchase Order?',
        note: 'This action cannot be undone.',
        btnText: 'Yes, Delete',
        btnClass: 'btn-danger',
        btnIcon: 'fas fa-trash-alt',
        headerClass: 'bg-danger',
        iconClass: 'fas fa-exclamation-triangle',
        action: this.deletePOAction
      }
    };

    this.modalData = config[type];
  },

  confirmAction() {
    if (this.modalData.action) {
      this.modalData.action(this.deleteId);
      $('#actionConfirmModal').modal('hide');
    }
  },

  approvePOAction(id) {
    console.log('Approved PO:', id);
    // Your logic here
  },

  rejectPOAction(id) {
    console.log('Rejected PO:', id);
    // Your logic here
  },

  deletePOAction(id) {
    console.log('Deleted PO:', id);
    // Your logic here
  }
    }
  };
  </script>
  