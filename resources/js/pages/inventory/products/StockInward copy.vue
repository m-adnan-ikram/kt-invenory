<template>
    <section class="section">
        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                    <div class="card card-primary ">
                        <div class="card-header">
                            <h4>Stock Inward </h4>
                            <div class="card-header-action">
                                <a href="#" data-toggle="modal" :data-target="'#'+formID" @click="clearForm()"
                                   class="btn btn-primary">
                                   Add New Purchase
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Table -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover"
                                                       id="table">
                                                    <thead>
                                                    <tr>
                                                        <th>Sr No.</th>
                                                        <th>Category Name</th>
                                                        <th>Product Name</th>
                                                        <th>Quantity</th>
                                                        <th>Price</th>
                                                        <th>Total Amount</th>
                                                        <th>Paid Amount</th>
                                                        <th>Supplier Name</th>
                                                        <th>Received Name</th>
                                                        <th>Date</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>1</td>
                                                            <td>Test</td> 
                                                            <td>Test</td>
                                                            <td>10 /items</td>
                                                            <td>110</td>
                                                            <td>1100</td>
                                                            <td>800</td> 
                                                            <td>XYZ</td>
                                                            <td>ABC</td>
                                                            <td>01 Mar 25</td>
                                                            <td class="text-center">  
                                                                <button
                                                                    title="Edit" :data-target="'#' + editFormID"
                                                                    data-toggle="modal" @click="edit(single)"
                                                                    class=" text-light btn btn-primary ">
                                                                    <i class="far fa-edit"></i>
                                                                </button>
                                                                <button title="Edit" data-target="#viewModal" data-toggle="modal"
                                                                        class="text-light btn btn-warning mx-1">
                                                                    <i class="far fa-eye"></i>
                                                                </button>

                                                                <button title="Delete" v-if="checkForSubmenuButtons('delete-city')"
                                                                    class=" text-light btn btn-danger" :data-target="'#' + hideFormID" @click="delId = city.id"
                                                                    data-toggle="modal">
                                                                <i class="far fas fa-trash"></i>
                                                            </button>
                                                            </td>
                                                       </tr>
                                                    <!-- <tr v-for="(single,i) in officeExpenses" :key="i">
                                                        <td>{{ i + 1 }}</td>
                                                        <td>{{ single.amount }}</td>
                                                        <td>{{ single.closing_date }}</td>
                                                        <td>{{ single.narration }}</td>
                                                        <td>
                                                            <button
                                                                title="Edit Expenses" :data-target="'#' + editFormID"
                                                                data-toggle="modal" @click="edit(single)"
                                                                class=" text-light btn btn-primary mx-1">
                                                                <i class="far fa-edit"></i>
                                                            </button>
                                                        </td>
                                                    </tr> -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- END TABLE -->
                        </div>
                    </div>
                </div>
            </div>
 
        </div>
              <!-- Add Modal -->
              <Add
    :heading="'Add New Checkin'"
    :errors="validationErrors"
    :success="success"
    :formID="formID"
>
    <div class="row">
        <div class="form-group col-md-12">
            <div class="d-flex justify-content-between">
                <label for="category">Select Supplier</label>
                <button class="btn btn-primary p-0 m-0 px-2" data-target="#addSupplier" data-toggle="modal">
                    Add New
                </button>
            </div>
            <select id="category" name="category" class="form-control select2">
                <option value="">Select</option>
                <option value="electronics">Ali</option>
                <option value="furniture">Rehan</option>
            </select>
        </div>

        <div class="row" v-for="(product, index) in products" :key="index">
            <div class="form-group col-md-4">
                <div class="d-flex justify-content-between">
                    <label>Select Product</label>
                    <button class="btn btn-primary p-0 m-0 px-2" data-target="#addProducts" data-toggle="modal">
                        Add New
                    </button>
                </div>
                <select v-model="product.name" class="form-control select2">
                    <option value="">Select</option>
                    <option value="electronics">Electronics</option>
                    <option value="furniture">Furniture</option>
                    <option value="vehicles">Vehicles</option>
                    <option value="tools">Tools</option>
                </select>
            </div>

            <div class="form-group col-md-2">
                <label>Quantity</label>
                <input type="number" v-model="product.quantity" @input="calculateTotal" class="form-control" placeholder="Enter Quantity">
            </div>

            <div class="form-group col-md-2">
                <label>Price</label>
                <input type="number" v-model="product.price" @input="calculateTotal" class="form-control" placeholder="Enter Price">
            </div>

            <div class="form-group col-md-2">
                <label>Total Amount</label>
                <input type="number" id="total" v-model="product.total" class="form-control" readonly>
            </div>

            <div class="form-group col-md-2">
                <label>Action</label>
                <div>
                    <button class="btn btn-info" @click="addRow">
                        <i class="fa fa-plus"></i>
                    </button>
                    <button class="btn btn-danger ml-1" @click="removeRow(index)" v-if="products.length > 1">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-5 ml-auto">
                    <div class="card">
                        <div class="card-header">
                            <h3>Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group row p-0 m-0">
                            <label for="inputPassword" class="col-sm-5 col-form-label">SubTotal: </label>
                            <div class="col-sm-7">
                            <input type="number" class="form-control text-right my-1" placeholder="">
                            </div>
                        </div>
                        <div class="form-group row p-0 m-0">
                            <label for="inputPassword" class="col-sm-5 col-form-label">Tax Amount: </label>
                            <div class="col-sm-7">
                            <input type="number" class="form-control text-right my-1" placeholder="">
                            </div>
                        </div>
                        <div class="form-group row p-0 m-0">
                            <label for="inputPassword" class="col-sm-5 col-form-label">Delivery Charges: </label>
                            <div class="col-sm-7">
                            <input type="number" class="form-control text-right my-1" placeholder="">
                            </div>
                        </div>
                        <div class="form-group row p-0 m-0">
                            <label for="inputPassword" class="col-sm-5 col-form-label">Discount: </label>
                            <div class="col-sm-7">
                            <input type="number" class="form-control text-right my-1" placeholder="">
                            </div>
                        </div>
                        <div class="form-group row p-0 m-0">
                            <label for="inputPassword" class="col-sm-5 col-form-label">Net Amount: </label>
                            <div class="col-sm-7">
                            <input type="number" class="form-control text-right my-1" placeholder="">
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
             <!-- Add Modal -->
             <Edit
                heading="Edit Checkin"
                :errors="this.validationErrors"
                :success="success"
                :editForm="editFormID"
            >
                <div class="form-group">
                    <label for="name">Name <span class="text-danger ml-1">*</span></label>
                    <input type="text" class="form-control" placeholder="Enter Product Category" v-model="dataEdit.name">
                </div>

                <template v-slot:button>
                    <button type="button" class="btn btn-primary" :disabled="loading" @click="update">
                        {{ loading ? 'Loading...' : 'Update' }}
                    </button>
                </template>
            </Edit>

             <!-- Hide Modal -->
             <Hide :hideForm="hideFormID" confirmationMessage="Are You Sure You want To Delete This Checkin ???">
                <template v-slot:button>
                    <button
                        type="button"
                        class="btn btn-danger btn-block"
                       :disabled="loading" @click="hideCity"
                    >
                    {{ loading ? 'Loading...' : 'Yes, I want to Delete' }}
                    </button>
                </template>
            </Hide>
             <AddProductModal></AddProductModal>
             <AddProductModalCategory></AddProductModalCategory>
             <AddSupplierModal></AddSupplierModal>
            <!-- view modal -->
            <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="seatAllDetailsModalLabel"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="">Checkin Detail</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                @click="closeModal()">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover"
                                                       id="office_expenses_table">
                                                    <thead>
                                                    <tr>
                                                        <th>Sr No.</th>
                                                        <th>Category Name</th>
                                                        <th>Product Name</th>
                                                        <th>Quantity</th>
                                                        <th>Quantity</th>
                                                        <th>Model #</th>
                                                        <th>Serial #</th>
                                                        <th>Usage</th>
                                                        <th>Condition</th>
                                                        <th>Remarks</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>1</td>
                                                            <td>Test</td> 
                                                            <td>Test</td>
                                                            <td>10 /items</td>
                                                            <td>123</td>
                                                            <td>456</td>
                                                            <td>150/ Km</td>
                                                            <td>New</td>
                                                            <td>testing</td>
                                                       </tr> 
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                        <button type="button" class="btn btn-secondary">
                            close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import Add from '../../../components/Add.vue';  
import Edit from '../../../components/Edit.vue';  
import Hide from '../../../components/Hide.vue';           
import AddProductModalCategory from '../modal/addProductCategoryModal.vue';    
import AddSupplierModal from '../modal/addSupplierModal.vue';       
import AddProductModal from '../modal/addProductsModal.vue';       
import {mapGetters} from 'vuex';

export default {
    name: "city",
    components: {
        Add, 
        Edit, 
        Hide,  
        AddProductModalCategory,
        AddSupplierModal,
        AddProductModal,
    },
    data() {
        return {
            validationErrors: [],
            cities: [],
            permissions: [],
            loading: false,
            formID: 'city_form',
            editFormID: 'edit_city_form',
            hideFormID: 'hide_city_form',
            viewFormID: 'view_city_form',
            data: {
                name: "",
            },
            dataEdit: {
                id: "",
                name: "",
            },
            delId: "",
            success: false,
            errors: false,
            products: [{ name: '', quantity: '', price: '', total: '' }],

            product: {
            quantity: null,
            price: null,
            total: 0
        }
        }
    },
    async created() {
        $('.modal').remove();
        await this.fetchCities();
        this.permissions = this.$store.state.permissions;
        const currentRouteName = this.$route.name;
        if (currentRouteName == 'booking-page') {
            window.addEventListener('keydown', this.enterKey);
            window.addEventListener('keydown', this.altM);
        } else {
            window.removeEventListener('keydown', this.enterKey);
            window.removeEventListener('keydown', this.altM);
        }
    },

    methods: {
        
        clearForm: function () {
            this.data = {};
        },
        addRow() {
            this.products.push({ name: '', quantity: '', price: '', total: '' });
        },
        removeRow(index) {
            this.products.splice(index, 1);
        },
        // async test() {
        //     const resCity = await this.callApi("post", 'api/booking/schedules/available',{
        //         departure_city: "multan",
        //         destination_city: "karachi",
        //         date: "2023-05-12",
        //     });
        // },

        async fetchCities() {
            const resCity = await this.callApi("post", 'cities');
            if (resCity.status == 200) {
                this.cities = resCity.data;
            }
            if (resCity.status == 403) {
                window.history.back();
                return swal({
                    title: "OOPS!!!!!",
                    text: "ACCESS DENIED",
                    icon: "error",
                    timer: 2000
                });
            }
            setTimeout(function () {
                $("#table").DataTable();
            }, 300);
        },
        async add() {
            this.validationErrors = []
            if (!this.data.name)
                return swal({
                    title: "Required",
                    text: "City Name is required",
                    icon: "error",
                    timer: 2000
                });
            this.loading = true
            const res = await this.callApi("post", 'cities/store', this.data);
            if (res.status == 200) {
                $(".modal").click();
                swal({
                    title: "Success",
                    text: "City Created Succesfuly Named as  " + res.data.name,
                    icon: "success",
                    timer: 2000
                });
                $("#table").DataTable().destroy();
                this.loading = false;
                await this.fetchCities();
                this.data.name = "";
                setTimeout(function () {
                    this.success = "";
                    this.data = "";
                }, 300)
            } else {
                if (res.status == 422) {
                    this.loading = false;
                    for (const key in res.data.errors) {
                        res.data.errors[key].forEach((element) => {
                            this.errorsArray(element, key);
                        });
                    }
                }
            }
        },
        
        edit(city) {
            this.dataEdit = city;
        },
        async update() {
            this.validationErrors = []
            if (this.dataEdit.name == "")
                return swal({
                    title: "Required",
                    text: "city Name is required ",
                    icon: "error",
                    timer: 2000
                });
            this.loading = true;
            const resEdit = await this.callApi("post", 'cities/update', this.dataEdit);
            if (resEdit.status == 200) {
                $(".modal").click();
                swal({
                    title: "Success",
                    text: "City updated Successfully",
                    icon: "success",
                    timer: 2000
                });
                this.loading = false;
                $("#table").DataTable().destroy();
                await this.fetchCities();
                setTimeout(() => {
                    this.success = ""
                    $('#edit-modal').modal('hide')
                }, 3000);
            } else {
                if (resEdit.status == 422) {
                    this.loading = false;
                    for (const key in resEdit.data.errors) {
                        resEdit.data.errors[key].forEach((element) => {
                            this.errorsArray(element, key);
                        });
                    }
                }
                setTimeout(() => {
                    this.loading = false
                }, 3000);
            }
        },
        async hide() { 
            this.loading = true;
            const resHide = await this.callApi("post", 'cities/hide', {id:this.delId});
            if (resHide.status == 200) {
                $(".modal").click();
                swal({
                    title: "Success",
                    text: "City Deleted Successfully",
                    icon: "success",
                    timer: 2000
                });
                this.loading = false;
                $("#table").DataTable().destroy();
                await this.fetchCities();
            } else {
                if (resHide.status == 422) {
                    this.loading = false;
                    for (const key in resHide.data.errors) {
                        resHide.data.errors[key].forEach((element) => {
                            this.errorsArray(element, key);
                        });
                    }
                }
                setTimeout(() => {
                    this.loading = false
                }, 3000);
            }
        },

        calculateTotal() {
        if (this.product.quantity && this.product.price) {
            this.product.total = this.product.quantity * this.product.price;
        } else {
            this.product.total = 0;
        }
    }
    },
    computed: {
    totalAmount() {
        return this.product.quantity && this.product.price ? this.product.quantity * this.product.price : 0;
    }
} 
}
</script> 
