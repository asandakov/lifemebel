BX.ready(function () {

});

LifeMebelComponent = {

    //флаг ajax
    ajaxFlag: true,
    ajaxFormFlag: true,

    //инициализация после загрузки страницы
    init: function (parameters) {
        this.ajaxUrl = parameters.ajaxUrl || '';
        this.ajaxForm = parameters.ajaxForm || '';
        this.signedParamsString = parameters.sign || '';
        this.curPage = window.location.pathname;
        this.blockId = BX("test-block");
        this.event();
    },

    //Событие
    event: function () {

        BX.bind(this.blockId.querySelector('.nm .fa-plus'), 'click', BX.delegate(this.addShowCityFormAction.bind(this, "add")));

        var editCity = this.blockId.querySelectorAll('table tr td .fa-pencil');
        for (var i = 0; i < editCity.length; i++) {
            BX.bind(editCity[i], 'click', BX.delegate(this.editShowCityFormAction.bind(this, "edit")));
        }

        var deleteCity = this.blockId.querySelectorAll('table tr td .fa-trash');
        for (var i = 0; i < deleteCity.length; i++) {
            BX.bind(deleteCity[i], 'click', BX.delegate(this.editShowCityFormAction.bind(this, "delete")));
        }
    },

    //показать форму для добваление
    addShowCityFormAction: function (type, event) {
        var target = event.target || event.srcElement;
        this.showCityFormAction(type, target, {});
    },

    //показать форму для редактирование/удаление
    editShowCityFormAction: function (type, event) {
        var target = event.target || event.srcElement,
            node = BX.findParent(target, {tagName: 'tr'}),
            id = node.getAttribute('data-id');

        this.showCityFormAction(type, target, {"ID": id});
    },

    // показать формы манипуляции с городами
    showCityFormAction: function (type, target, params) {
        if ((!type) || (!this.ajaxForm))
            return;

        var postData = {
            'TYPE': type,
            'signedParamsString': this.signedParamsString
        };

        //параметры которые нужно отправить
        if (!!params && typeof params === 'object') {
            for (i in params) {
                if (params.hasOwnProperty(i))
                    postData[i] = params[i];
            }
        }

        //если предыдущий ajax не выполнен то неделаем ничего
        if (!this.ajaxFormFlag) return;
        $(target).addClass("act");
        $("#actionModal .modal-content").html("");
        $("#actionModal .msg").removeClass('err').html("");
        $("#actionModal form[name=form-action]").removeClass('was-validated');
        BX.ajax({
            timeout: 60,
            method: 'POST',
            dataType: 'html',
            url: this.ajaxForm + "/" + type + ".php",
            data: postData,
            onsuccess: BX.delegate(function (result) {
                $("#actionModal .modal-content").html(result);
                BX.bind(BX("actionModal").querySelector('form button[name=send]'), 'click', BX.delegate(this.sendCityFormAction, this));
                $("#actionModal").modal('show');
                $(target).removeClass("act");
                this.ajaxFormFlag = true;
            }, this),
            onfailure: BX.delegate(function () {
                this.ajaxFormFlag = true;
                $("#actionModal .modal-content").html("<div class='msg err'>Ошибка. Попробуйте еще раз.</div>");
                $("#actionModal").modal('show');
                $(target).removeClass("act");
            }, this)
        });
    },

    // ПРОВЕРКА ПОЛЕЙ НА ВАЛИДНОСТЬ
    errValide: function () {
        var flag = true;

        $('#actionModal form[name=form-action]').find('.form-control').each(function () {
            if ($(this).prop('required')) {
                if ($(this).val() == '') {
                    flag = false;
                }
            }
        });

        return flag;
    },

    //Отправить форму
    sendCityFormAction: function (e) {
        e.preventDefault();
        $("#actionModal .msg").removeClass('err').html("");
        $("#actionModal form[name=form-action]").removeClass('was-validated');
        var err = this.errValide();
        if (err) {
            var data = $("#actionModal").find('form[name=form-action]').serialize();
            this.sendRequest({data: data, action: "sendCityFormAjax"});
        } else {
            $("#actionModal form[name=form-action]").addClass('was-validated');
        }
    },


    //общение к серваку
    sendRequest: function (params) {

        var postData,
            i;

        //если предыдущий ajax не выполнен то неделаем ничего
        if (!this.ajaxFlag) return;

        postData = {
            'ajax': 'Y',
            'signedParamsString': this.signedParamsString,
        };


        //параметры которые нужно отправить
        if (!!params && typeof params === 'object') {
            for (i in params) {
                if (params.hasOwnProperty(i))
                    postData[i] = params[i];
            }
        }

        //если нет метода то ничего не делаем
        if (!postData["action"]) return;

        //выполним ajax
        this.ajaxFlag = false;
        BX.ajax({
            timeout: 60,
            method: 'POST',
            dataType: 'json',
            url: this.ajaxUrl,
            data: postData,
            onsuccess: BX.delegate(function (result) {
                this.beforeAction(postData["action"], result);
                this.ajaxFlag = true;
            }, this),
            onfailure: BX.delegate(function () {
                this.beforeErrorAction(postData["action"]);
                this.ajaxFlag = true;
            }, this)
        });

    },
    //после ajax сделаем что то - ОШИБКА
    beforeErrorAction: function (type) {
        switch (type) {
            case "sendCityFormAjax":
                $("#actionModal .msg").addClass('err').html("Ошибка. Попробуйте еще раз.");
                break;
        }
    },


    //после ajax сделаем что то
    beforeAction: function (type, result) {
        switch (type) {
            case "sendCityFormAjax":
                this.sendCityFormAjaxBefore(result);
                break;
        }
    },


    //форма: действия над городами
    sendCityFormAjaxBefore: function (result) {
        if (result["error"]) {
            $("#actionModal .msg").addClass('err').html(result["error"]);
        } else {
            var msg = "Операция удачно выполнена";
            if (result["msg"]) {
                var msg = result["msg"];
            }

            $("#actionModal .msg").html(msg);
            $("#actionModal form").remove();
            setTimeout(BX.delegate(function () {
                $("#actionModal").modal('hide');
                window.location.href = this.curPage;
            }, this), 700);
        }
    },

    //СКРОЛЛ
    scrollTop: function (id_block) {
        $('html, body').animate({scrollTop: $('#' + id_block).offset().top}, 500);
    }

}