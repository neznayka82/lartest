
// noinspection BadExpressionStatementJS
/*
function calcCost(indx) {
    let price = parseInt($("#product_id_" + indx).attr('data-price'));
    let discount = parseInt($("#discount_" + indx).val())
    let count = parseInt($("#count_" + indx).val())
    $("#cost_" + indx) . val (count * price - discount);
}

function changedProduct($this){
    const val = $($this).val();
    let indx = $($this).attr('id');
    indx = indx.split("_");
    indx = indx[indx.length -1];
    $.ajax({
        url:'/admin/products/getbyid',
        type:'GET',
        data:({'id': val}),
        success: function(response){
            const price = parseInt(response.price);
            $("#product_id_" + indx).attr('data-price' , price);
            calcCost(indx);
        }
    })
}

function changedCount($this){
    let indx = $($this).attr('id');
    indx = indx.split("_");
    indx = indx[indx.length -1];
    calcCost(indx);
}

function changedDiscount($this){
    let indx = $($this).attr('id');
    indx = indx.split("_");
    indx = indx[indx.length -1];
    calcCost(indx);
}

*/

let myapp = new Vue({
    el: '#app',
    template : "<div class='row'>" +
        //Информация об ошибке ввода
        "<div v-if=\"!!orderError\" class='col-lg-12 form-element-errors'><b>Ошибка: {{orderError}}</b></div>" + /**/
        "<div class='col-lg-6'>"+
        //Имя клиента
        "<label for='customer' class='control-label'>Имя клиента <span class='form-element-required'>*</span></label>"+
        "<input id='customer' class='form-control' v-model='customer'/></div>" +
        //Телефон клиента
        "<div class='col-lg-6'><label for='phone' class='control-label'>Телефон клиента <span class='form-element-required'>*</span></label>"+
        "<input id='phone' class='form-control' v-model='phone'/></div>" +
        //Менеджер
        "<div class='col-lg-3'><label for='manager' class='control-label'>Менеджер <span class='form-element-required'>*</span></label>"+
        "<select id='manager' class='form-control' v-model='user_id'>" +
        "<option v-for=\"(manager, index) in managers\" :value=\"manager.id\" :key=\"index\">{{manager.name}}</option>" +
        "</select></div>" +
        //Тип заказа
        "<div class='col-lg-3'><label for='type' class='control-label'>Тип заказа</label>"+
        "<select id='type' class='form-control' v-model='type'>" +
            "<option v-for=\"(type, index) in types\" :value=\"type.value\" :key=\"index\">{{type.title}}</option>" +
        "</select></div>" +
        //Статус заказа
        "<div class='col-lg-3'><label for='type' class='control-label'>Статус заказа</label>"+
        "<select id='status' class='form-control' v-model='status'>" +
            "<option v-for=\"(status, index) in statuses\" :value=\"status.value\" :key=\"index\">{{status.title}}</option>" +
        "</select></div>" +
        "<div class='col-lg-3'><a class='btn btn-info' @click='addOrderItem' style='margin-top:32px'>Добавить товар</a></div>"+
        //Раздел Позиция заказа
        "<div class='col-lg-12'><div v-for=\"(item , index) in orderItems \">" +
            //Товар
            "<div class='row' v-if='!item.isRemove' style='margin-top: 15px;border-top:2px solid gray;'> " +
            "<div style='flex: 0 0;'>#{{index + 1}}</div><div class='col-lg-3'> " +
            "<label :for=\"'product_' + index\" class='control-label'>Товар <span class='form-element-required'>*</span></label>" +
            "<select :id=\"'product_' + index\" class='form-control' v-model='item.product_id' @change='calcCost(item)'>" +
            "<option v-for=\"(product, index) in products\" :value=\"product.id\" :key=\"index\">{{product.name}}</option>" +
            "</select></div>" +
            //Количество
            "<div class='col-lg-2'> <label :for=\"'count_' + index\" class='control-label'>Количество <span class='form-element-required'>*</span></label>"+
            "<input type='number' :for=\"'count_' + index\" class='form-control' v-model='item.count' @change='calcCost(item)'/></div>" +
            //Скидка
            "<div class='col-lg-2'> <label :for=\"'discount_' + index\" class='control-label'>Скидка <span class='form-element-required'>*</span></label>"+
            "<input :for=\"'discount_' + index\" class='form-control' v-model='item.discount' @change='calcCost(item)'/></div>" +
            //Стоимость
            "<div class='col-lg-3'> <label :for=\"'cost_' + index\" class='control-label'>Cтоимость с учётом скидки <span class='form-element-required'>*</span></label>"+
            "<input :for=\"'cost_' + index\" class='form-control' v-model='item.cost' style='pointer-events: none;'/></div>" +
            "<div class='col-lg-1'><a class='btn btn-danger' @click='removeOrderItem(item)' style='margin-top:32px'>Удалить</a></div>"+
            //Информация об ошибке ввода
            "<div v-if=\"!!orderItemErrors[index]\" class='col-lg-12 form-element-errors'><b>Ошибка: {{orderItemErrors[index]}}</b></div>"+ /**/
            "</div>" +
        "</div>" + /**/
        //Кнопка создать заказ
        "<div class='col-lg-12' style='margin-top: 15px;'><a class='btn  btn-success' @click='createOrder'>{{!isUpdate ? 'Создать' : 'Изменить' }} заказ</a></div>" +
        "</div></div>",
    data: {
        isUpdate: false,
        id : null,
        customer : "",
        phone : "",
        user_id : null,
        type : "online",
        status : "active",
        orderItems : [],
        managers: [],
        products: [],
        orderError : false,
        orderItemErrors : [],
        types : [
            {value: "online", title: 'Заказ онлайн'},
            {value: "offline", title: 'Заказ офлайн'}
        ],
        statuses : [
            {value: "active", title: 'Заказ выполняется'},
            {value: "completed", title: 'Заказ выполнен'},
            {value: "canceled", title: 'Заказ отменен'}
        ]
    },
    mounted: function () {
        //проверяем урл это создание или редактирование
        this.checkUpdate();
        if(!this.isUpdate) {
            this.order = this.getDefaultOrder();
        }
        //подгружаем продукты
        this.getAllProducts();
        //подгружаем менеджеров
        this.getAllManagers();
    },
    methods: {
        _init(){
            //метод без которого не загружается компонента
        },
        //Валидация формы ввода
        validateForm(){
            //Валидация основных полей Заказа
            if (this.customer.length === 0 ) {
                this.orderError = "Имя клиента не может быть пустым";
                return false;
            }
            if (this.phone.length === 0 ) {
                this.orderError = "Телефон клиента не может быть пустым";
                return false;
            }
            if (!this.user_id) {
                this.orderError = "Укажите менеджера";
                return false;
            }
            if (this.orderItems.length === 0) {
                this.orderError = "Добавьте товар";
                return false;
            }
            //основные поля прошло проверку
            this.orderError = false;
            let check = true;
            //проверка позиций товаров
            let total = [];
            for (let i =0; i < this.orderItems.length; i++) {
                if (!!this.orderItems[i].isRemove) continue;
                //this.orderItemErrors[i] = false;
                if (!this.orderItems[i].product_id) {
                    this.orderItemErrors[i] = "Выберите продукт";
                    check = false;
                    continue;
                }
                if (!this.orderItems[i].count || this.orderItems[i].count === 0) {
                    this.orderItemErrors[i] = "Укажите кол-во товара";
                    check = false;
                }
                if (this.orderItems[i].cost < 0) {
                    this.orderItemErrors[i] = "Стоимость не может быть отрицательной";
                    check = false;
                }
                if (!!total[this.orderItems[i].product_id]) {
                    total[this.orderItems[i].product_id] += this.orderItems[i].count;
                } else {
                    total[this.orderItems[i].product_id] = this.orderItems[i].count;
                }
            }
            this.orderError = !this.orderError;
            this.orderError = false;
            if (check) {
                this.orderItemErrors = [];
            }
            //проверка остатков товаров

            //console.log(this.orderItemErrors, check);
            return check;
        },
        createOrder(){
            if (this.validateForm()) {
                //console.log("create order");
                let order = {
                    id: this.id,
                    customer: this.customer,
                    phone: this.phone,
                    user_id : this.user_id,
                    status: this.status,
                    type: this.type
                }
                const action = this.isUpdate ? 'update' : 'create'
                $.ajax({
                    url:'/admin/orders/' + action,
                    type:'POST',
                    data:({order, items: this.orderItems}),
                    success: function(response){
                        let data = JSON.parse(response);
                        if (data.status === 'ok') {
                            Admin.Messages.success('Выполнили успешно', 'Данные обновлены')
                            if (myapp.isUpdate) {
                                myapp.getOrderById(order.id);
                            }
                        } else {
                            Admin.Messages.error('Произошла ошибка', data.message)
                        }
                    }
                })
            }
        },
        addOrderItem(){
            const index = this.orderItems.length + 1;
            let orderItem = {
                index : index,
                product_id: 0,
                count: 0,
                discount: 0,
                cost:0,
                isRemove : false,
            }
            this.orderItems.push(orderItem)
        },
        removeOrderItem(item){
            for(let i = 0; i < this.orderItems.length ; i++) {
                if (item.index === this.orderItems[i].index) {
                    this.orderItems[i].isRemove = true;
                    this.orderItems[i].count = 0;
                    break;
                }
            }
            const s = myapp.status;
            myapp.status = "cancelled";
            myapp.status = s;
        },
        getAllProducts(){
            $.ajax({
                url:'/admin/products/getall/',
                type:'GET',
                success: function(response){
                    myapp.products = JSON.parse(response);
                    //console.log(myapp.products)
                }
            });
        },
        getAllManagers(){
            $.ajax({
                url:'/admin/managers/getall/',
                type:'GET',
                success: function(response){
                    myapp.managers = JSON.parse(response);
                    //console.log(myapp.managers)
                }
            });
        },
        //разбираем урл и проверяем мы редактируем или создаем
        checkUpdate(){
            const url = window.location.href;
            if (url.toLowerCase().indexOf("edit") > -1){
                const tmp = url.split("/")
                const orderId = tmp[tmp.length - 2];
                this.getOrderById(orderId);
                this.isUpdate = true;
                return true;
            }
            this.isUpdate = false;
            return false;
        },
        calcCost(item) {
            let price = 0;
            for(let i = 0; i < this.products.length ; i++) {
                if (item.product_id === this.products[i].id) {
                    price = this.products[i].price;
                    break;
                }
            }
            item.cost = price * parseInt( !!item.count ? item.count : 0 ) - parseFloat(!!item.discount ? item.discount : 0)
        },
        getOrderById(id){
            console.log("получаем заказ №" + id);
            $.ajax({
                url:'/admin/orders/getbyid',
                type:'GET',
                data:({id}),
                success: function(response){
                    let data = JSON.parse(response);
                    myapp.customer = data.order.customer
                    myapp.phone = data.order.phone
                    myapp.user_id = data.order.user_id
                    myapp.type = data.order.type
                    myapp.status = data.order.status
                    myapp.id = data.order.id
                    myapp.orderItems = data.items
                    for(let i=0 ; i < myapp.orderItems.length ; i++){
                        myapp.orderItems[i].index = i;
                        myapp.orderItems[i].isRemove = false;
                    }
                }
            })
        },
        getDefaultOrder(){
            return {
                user_id: null,
                phone: "",
                customer: "",
                type : "online",
                status : "active",
                items: [
                    {
                        product: null,
                        count: 0,
                        discount: 0,
                        cost : 0,
                        isRemove: false,
                    }
                ]
            };
        },
    }
})
