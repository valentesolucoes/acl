<?php echo $this->extend('Layout/principal'); ?>


<?php echo $this->section('titulo') ?> <?php echo $titulo; ?> <?php echo $this->endSection() ?>


<?php echo $this->section('estilos') ?>

<link rel="stylesheet" href="<?php echo site_url('recursos/vendor/fullcalendar/fullcalendar.min.css'); ?>">
<link rel="stylesheet" href="<?php echo site_url('recursos/vendor/fullcalendar/toastr.min.css'); ?>">

<style>

    /* Alterar o a cor de fundo dos eventos */
    .fc-event, .fc-event-dot {
        background-color: #343a40 !important;
    }

</style>

<?php echo $this->endSection() ?>



<?php echo $this->section('conteudo') ?>


<div id="calendario" class="container-fluid">

    <!-- Aqui será renderizado o fullcalendar -->

</div>



<?php echo $this->endSection() ?>




<?php echo $this->section('scripts') ?>

<script src="<?php echo site_url('recursos/vendor/fullcalendar/fullcalendar.min.js'); ?>"></script>
<script src="<?php echo site_url('recursos/vendor/fullcalendar/toastr.min.js'); ?>"></script>
<script src="<?php echo site_url('recursos/vendor/fullcalendar/moment.min.js'); ?>"></script>

<script>
$(document).ready(function() {

    var calendario = $("#calendario").fullCalendar({

        header: {

            left: 'prev, next today',
            center: 'title',
            right: 'month',

        },
        height: 580,
        editable: true,
        events: '<?php echo site_url('eventos/eventos'); ?>',
        displayEventTime: false,
        selectable: true,
        selectHelper: true,
        select: function(start, end, allDay) {

            var title = prompt('Inform o título do evento');

            if (title) {

                var start = $.fullCalendar.formatDate(start, 'Y-MM-DD'); // Formatação do moment.js
                var end = $.fullCalendar.formatDate(end, 'Y-MM-DD'); // Formatação do moment.js


                $.ajax({

                    url: '<?php echo site_url('eventos/cadastrar'); ?>',
                    type: 'GET',
                    data: {
                        title: title,
                        start: start,
                        end: end,
                    },
                    success: function(response) {

                        exibeMensagem('Evento criado com sucesso!');

                        calendario.fullCalendar('renderEvent', {
                            id: response.id,
                            title: title,
                            start: start,
                            end: end,
                            allDay: allDay,
                        }, true);
                        calendario.fullCalendar('unselect');

                    }, // fim success


                }); // fim ajax cadastro


            } // fim if title

        },


        // Atualiza evento
        eventDrop: function(event, delta, revertFunc) {


            if (event.conta_id || event.ordem_id) {

                alert(
                    'Não é possível alterar um evento, pois o mesmo está atrelado a uma conta ou ordem de serviço'
                );
                revertFunc();
            } else {


                // Posso editar tranquilamento o evento

                var start = $.fullCalendar.formatDate(event.start,
                    'Y-MM-DD'); // Formatação do moment.js
                var end = $.fullCalendar.formatDate(event.end,
                    'Y-MM-DD'); // Formatação do moment.js


                $.ajax({

                    url: '<?php echo site_url('eventos/atualizar/'); ?>' + event
                        .id, // Id do evento a ser atualizado
                    type: 'GET',
                    data: {
                        start: start,
                        end: end,
                    },
                    success: function(response) {

                        exibeMensagem('Evento atualizado com sucesso!');

                    }, // fim success


                }); // fim ajax atualização


            } // fim else


        }, // fim atualiza evento


        // Exclusão de evento
        eventClick: function(event) {


            if (event.conta_id || event.ordem_id) {

                alert(event.title);

            } else {

                var exibeEvento = confirm(event.title + '\r\n\r' +
                    'Gostaria de excluir esse evento?');


                if (exibeEvento) {

                    var confirmaExclusao = confirm("Tem certeza?");


                    if (confirmaExclusao) {

                        // Posso excluir o evento

                        $.ajax({

                            url: '<?php echo site_url('eventos/excluir'); ?>', 
                            type: 'GET',
                            data: {
                                id: event.id,                                
                            },
                            success: function(response) {

                                calendario.fullCalendar('removeEvents', event.id);
                                exibeMensagem('Evento removido com sucesso!');

                            }, // fim success


                        }); // fim ajax exclusão


                    } // fim confirmaExclusao

                } // fim exibeEvento


            } // fim else

        } // fim eventClick
    });

});


// Função fica fora do jquery
function exibeMensagem(mensagem) {
    toastr.success(mensagem, 'Evento');
}
</script>

<?php echo $this->endSection() ?>