@extends('adminlte::page')

@section('title', 'RECOJO')

@section('content_header')
  <h1 class="text-center">
    <i class="fa fa-motorcycle text-primary" aria-hidden="true"></i> Bandeja de Recojo
  </h1>

@stop

@section('content')

  @include('envios.motorizado.modal.recojo_enviarope')

  <div class="card p-0">

    <div class="tab-content" id="myTabContent">
      <div class="tab-pane fade show active" id="enmotorizado" role="tabpanel" aria-labelledby="enmotorizado-tab">
        <table id="tblListadoRecojo" class="table table-striped">{{-- display nowrap  --}}
          <thead>
          <tr>
            <th></th>
            <th scope="col">Código</th>
            <th scope="col">Cliente</th>
            <th scope="col">Razón social</th>
            <th scope="col">Cantidad</th>
            <th scope="col">Asesor</th>
            <th scope="col">RUC</th>
            <th scope="col">F. Registro</th>
            <th scope="col">F. Actualizacion</th>
            <th scope="col">Total (S/)</th>
            <th scope="col">Est. pago</th>
            <th scope="col">Con. pago</th>
            <th scope="col">Est. Sobre</th>
            <th scope="col">Diferencia</th>
            <th scope="col">...</th>
          </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
        @include('operaciones.modal.confirmarRecepcionRecojo')
      </div>

    </div>


  </div>

@stop

@push('css')

  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.bootstrap4.min.css">
  <style>
    @media (max-width: 32rem) {
      div.dataTables_wrapper div.dataTables_filter input {
        width: 200px !important;
      }

      .content-wrapper {
        background-color: white !important;
      }

      .card {
        box-shadow: 0 0 1px white !important;
      }
    }

    .yellow_color_table {
      background-color: #ffd60a !important;
    }
    .blue_color_table {
      background-color: #3A98B9 !important;
    }
  </style>
  @include('partials.css.time_line_css')
@endpush

@section('js')

  <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>

  <script src="https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js"></script>

  <script src="https://momentjs.com/downloads/moment.js"></script>
  <script src="https://cdn.datatables.net/plug-ins/1.11.4/dataRender/datetime.js"></script>

  <script>
    let tblListadoRecojo=null;

    $(document).ready(function () {

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      function renderButtomsDataTable(row, data) {
        if (data.destino == 'PROVINCIA') {
          $('td', row).css('color', '#20c997')
        }
        if (data.estado == 0) {
          $('td', row).css('color', 'red')
        }

      }
      var detailRows = [];

      tblListadoRecojo = $('#tblListadoRecojo').DataTable({
        dom: 'Blfrtip',
        processing: true,
        serverSide: true,
        searching: true,
        //stateSave: true,
        order: [[8, "desc"]],
        ajax: "{{ route('pedidosrecojotabla') }}",
        createdRow: function (row, data, dataIndex) {
          if (data["estado"] == "1") {
            if (data.pendiente_anulacion == 1) {
              $('td', row).css('background', 'red').css('font-weight', 'bold');
            }
          } else {
            $(row).addClass('textred');
          }
        },
        rowCallback: function (row, data, index) {
          var pedidodiferencia = data.diferencia;

          if (data.condicion_code == 4 || data.estado == 0) {
            $('td:eq(12)', row).css('background', '#ff7400').css('color', '#ffffff').css('text-align', 'center').css('font-weight', 'bold');
          } else {
            if (pedidodiferencia == null) {
              $('td:eq(12)', row).css('background', '#ca3a3a').css('color', '#ffffff').css('text-align', 'center').css('font-weight', 'bold');
            } else {
              if (pedidodiferencia > 3) {
                $('td:eq(12)', row).css('background', '#ca3a3a').css('color', '#ffffff').css('text-align', 'center').css('font-weight', 'bold');
              } else {
                $('td:eq(12)', row).css('background', '#44c24b').css('text-align', 'center').css('font-weight', 'bold');
              }
            }
          }

          $('[data-jqconfirm]', row).click(function () {
            $.confirm({
              theme:'material',
              columnClass: 'large',
              title: 'Editar direccion de envio',
              content: function () {
                var self = this;
                return $.ajax({
                  url: '{{route('pedidos.envios.get-direccion')}}?pedido_id=' + data.id,
                  dataType: 'json',
                  method: 'get'
                })
                  .done(function (response) {
                    console.log(response);

                    self.setContent(response.html);
                    if (!response.success) {
                      self.$$confirm.hide();
                    }
                  })
                  .fail(function (e) {
                    console.error(e)
                    self.setContent('Ocurrio un error');
                  });
              },
              buttons: {
                confirm: {
                  text: 'Actualizar',
                  btnClass: 'btn-success',
                  action: function () {
                    var self = this;
                    console.log(self.$content.find('form')[0])
                    const form = self.$content.find('form')[0];
                    const data = new FormData(form)
                    if(data.get('celular').length!=9){
                      $.alert({
                        title: 'Alerta!',
                        content: '¡El numero de celular debe tener 9 digitos!',
                      });
                      return false

                    }

                    self.showLoading(true)
                    $.ajax({
                      data: data,
                      processData: false,
                      contentType: false,
                      type: 'POST',
                      url: "{{route('pedidos.envios.update-direccion')}}",
                    }).always(function () {
                      self.close();
                      $('#tblListadoRecojo').DataTable().ajax.reload();
                    });
                    return false
                  }
                },
                cancel: function () {

                },
              },
              onContentReady:function (){

                var self = this;
                const form = self.$content.find('form')[0];
                const data = new FormData(form)
                console.log("aa")
                console.log(form)
                self.$content.find('select#distrito').selectpicker('refresh');
              }
            });
          })

          $('[data-verforotos]', row).click(function () {
            var data = $(this).data('verforotos')
            $.dialog({
              columnClass: 'xlarge',
              title: 'Fotos confirmadas',
              type: 'green',
              content: function () {
                return `<div class="row">
${data.foto1 ? `
<div class="col-md-4">
<div class="card">
<div class="card-header d-none"><h5>Foto de los sobres</h5></div>
<div class="card-body">
<img src="${data.foto1}" class="w-100">
</div>
</div>
</div>
` : ''}
${data.foto2 ? `
<div class="col-md-4">
<div class="card">
<div class="card-header d-none"><h5>Foto del domicilio</h5></div>
<div class="card-body">
<img src="${data.foto2}" class="w-100">
</div>
</div>
</div>
` : ''}
${data.foto3 ? `
<div class="col-md-4">
<div class="card">
<div class="card-header d-none"><h5>Foto de quien recibe</h5></div>
<div class="card-body">
<img src="${data.foto3}" class="w-100">
</div>
</div>
</div>
` : ''}
</div>`
              }
            })

          })

          $("[data-jqconfirmdetalle=jqConfirm]",row).on('click', function (e) {
            openConfirmDownloadDocuments($(e.target).data('target'), $(e.target).data('idc'), $(e.target).data('codigo'))
          })
        },
        initComplete: function (settings, json) {
        },
        columns: [
          {
            class: 'details-control',
            orderable: false,
            data: null,
            defaultContent: '',
            "searchable": false
          },
          {data: 'codigos', name: 'codigos',},
          {
            data: 'celulares',
            name: 'celulares',
            render: function (data, type, row, meta) {
              if (row.icelulares != null) {
                return row.celulares + '-' + row.icelulares + ' - ' + row.nombres;
              } else {
                return row.celulares + ' - ' + row.nombres;
              }

            },
          },
          {data: 'empresas', name: 'empresas',},
          {data: 'cantidad', name: 'cantidad', render: $.fn.dataTable.render.number(',', '.', 2, ''),},
          {data: 'users', name: 'users',},
          {data: 'ruc', name: 'ruc',},
          {
            data: 'fecha',
            name: 'fecha',
          },
          {
            data: 'fecha_up',
            name: 'fecha_up',
            "visible": false,
          },
          {
            data: 'total',
            name: 'total',
            render: $.fn.dataTable.render.number(',', '.', 2, '')
          },
          {
            data: 'condicion_pa',
            name: 'condicion_pa',
            render: function (data, type, row, meta) {

              if (row.condiciones == 'ANULADO' || row.condicion_code == 4 || row.estado == 0) {
                return 'ANULADO';
              } else {
                if (row.condicion_pa == null) {
                  return 'SIN PAGO REGISTRADO';
                } else {
                  if (row.condicion_pa == '0') {
                    return '<p>SIN PAGO REGISTRADO</p>'
                  }
                  if (row.condicion_pa == '1') {
                    return '<p>ADELANTO</p>'
                  }
                  if (row.condicion_pa == '2') {
                    return '<p>PAGO</p>'
                  }
                  if (row.condicion_pa == '3') {
                    return '<p>ABONADO</p>'
                  }
                  //return data;
                }
              }

            }
          },
          {
            data: 'condiciones_aprobado',
            name: 'condiciones_aprobado',
            render: function (data, type, row, meta) {
              if (row.condicion_code == 4 || row.estado == 0) {
                return 'ANULADO';
              }
              if (data != null) {
                return data;
              } else {
                return 'SIN REVISAR';
              }

            }
          },
          {
            data: 'condicion_envio',
            name: 'condicion_envio',
          },
          {
            data: 'diferencia',
            name: 'diferencia',
            render: function (data, type, row, meta) {
              if (row.condicion_code == 4 || row.estado == 0) {
                return '0';
              }
              if (row.diferencia == null) {
                return 'NO REGISTRA PAGO';
              } else {
                if (row.diferencia > 0) {
                  return row.diferencia;
                } else {
                  return row.diferencia;
                }
              }
            }
          },
          {
            data: 'action',
            name: 'action',
          },
        ],
        language: {
          "decimal": "",
          "emptyTable": "No hay información",
          "info": "Mostrando del _START_ al _END_ de _TOTAL_ Entradas",
          "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
          "infoFiltered": "(Filtrado de _MAX_ total entradas)",
          "infoPostFix": "",
          "thousands": ",",
          "lengthMenu": "Mostrar _MENU_ Entradas",
          "processing": "Procesando...",
          "search": "Buscar:",
          "zeroRecords": "Sin resultados encontrados",
          "paginate": {
            "first": "Primero",
            "last": "Ultimo",
            "next": "Siguiente",
            "previous": "Anterior"
          }

        },
      });

      $('#modal-envio-recojo').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget)
        var grupopedido = button.data('grupopedido')
        var codigos = button.data('codigos')

        $(".textcode").html(codigos);
        $("#hiddenIdGrupoPedido").val(grupopedido);
      });

      $(document).on("submit", "#modal-envio-recojo", function (evento) {
        evento.preventDefault();

        var data = new FormData();
        data.append('hiddenIdGrupoPedido', $("#hiddenIdGrupoPedido").val());

        $.ajax({
          data: data,
          processData: false,
          contentType: false,
          type: 'POST',
          url: "{{ route('envios.confirmar-recepcion-recojo') }}",
          success: function (data) {
            console.log(data);
            $("#modal-envio-recojo .textcode").text('');
            $("#modal-envio-recojo").modal("hide");
            Swal.fire('Mensaje', data.mensaje, 'success')
            $('#tblListadoRecojo').DataTable().ajax.reload();
          }
        });
      });

      datatablerecojo.on('responsive-display', function (e, datatable, row, showHide, update) {
        console.log('Details for row ' + row.index() + ' ' + (showHide ? 'shown' : 'hidden'));
        if (showHide) {
          renderButtomsDataTable($(row.node()).siblings('.child'), row.data())
        }
      });

      $('#modal_recojomotorizado').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget)
        $("#input_recojomotorizado").val(button.data('direccion_grupo'));
      });

      /*$(document).on("submit", "#form_recojo_enviarope", function (evento) {
        evento.preventDefault();
        var drecojoenviarope = new FormData();
        drecojoenviarope.append('input_recojoenviarope', $('#input_recojoenviarope').val());
        $.ajax({
          data: drecojoenviarope,
          processData: false,
          contentType: false,
          type: 'POST',
          url: "{{ route('courier.recojoenviarope') }}",
          success: function (data) {
            $("#modal_recojoenviarope").modal("hide");
            $('#tblListadoRecojo').DataTable().ajax.reload();
          }
        });

      });*/
    });
  </script>

@stop