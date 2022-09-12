$(function () {
    $('.money').mask('#,##0.00', {reverse: true});
    $('.cep').mask('00000-000');
    $('.cpf').mask('000.000.000-00', {reverse: true});
    $('.cnpj').mask('00.000.000/0000-00', {reverse: true});
    $('.pis').mask('000.00000.00-0', {reverse: true});
    $('.phone_with_ddd').mask('(00) 0000-0000'); 
    $('.uf').mask('AA');
    $('.selectonfocus').mask("00000000", {selectOnFocus: true});
    $(".card_number").mask("0000000000000000");
    $(".card_month").mask("00");
    $(".card_year").mask("0000");
    $(".card_cvv").mask("0000"); // Algumas bandeiras são 3 e outros 4

    var SPMaskBehavior = function (val) {
        return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
    },
            spOptions = {
                onKeyPress: function (val, e, field, options) {
                    field.mask(SPMaskBehavior.apply({}, arguments), options);
                }
            };

    $('.sp_celphones').mask(SPMaskBehavior, spOptions);

    /*----------------------------Daqui para baixo Criado quando trabalhamos no módulo Entregadores */
    $(document).ready(function () {
        maskMercosul('.placa');
    });

    function maskMercosul(selector) {
        var MercoSulMaskBehavior = function (val) {
            var myMask = 'AAA0A00';
            var mercosul = /([A-Za-z]{3}[0-9]{1}[A-Za-z]{1})/;
            var normal = /([A-Za-z]{3}[0-9]{2})/;
            var replaced = val.replace(/[^\w]/g, '');
            if (normal.exec(replaced)) {
                myMask = 'AAA-0000';
            } else if (mercosul.exec(replaced)) {
                myMask = 'AAA-0A00';
            }
            return myMask;
        },
                mercoSulOptions = {
                    onKeyPress: function (val, e, field, options) {
                        field.mask(MercoSulMaskBehavior.apply({}, arguments), options);
                    }
                };
        $(function () {
            $(selector).bind('paste', function (e) {
                $(this).unmask();
            });
            $(selector).bind('input', function (e) {
                $(selector).mask(MercoSulMaskBehavior, mercoSulOptions);
            });
        });
    }
})