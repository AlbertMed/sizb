@extends('home') 
@section('homecontent')
<style>
    th { font-size: 12px; }
    td { font-size: 11px; }
   
    div.dataTables_wrapper {
    margin: 0 ;
    }
    div.container {
        min-width: 980px;
        margin: 0 auto;
    }
    td:first-child{
        width:2%;
    }
    th:first-child {
        position: -webkit-sticky;
        position: sticky;
        left: 0px;
        z-index: 4;
        width: 2%;
    }
    table.dataTable thead .sorting_asc{
        position: sticky;
    }
    .DTFC_LeftBodyWrapper{
        margin-top: 88px;
    }
    .DTFC_LeftHeadWrapper {
        display:none;
    }

    th, td { white-space: nowrap; }
    .dataTables_wrapper .dataTables_length { /*mueve el selector de registros a visualizar*/
    float: right;
    }

    .yadcf-filter-range-number-seperator {
    margin-left: 0px; 
    margin-right: 10px;
    }
    .yadcf-filter-reset-button {
    display: inline-block;
    background-color: #337ab7;
        border-color: #2e6da4;
    }

    input{
        color: black;
    }
</style>
<?php
                $fecha = \Carbon\Carbon::now();
            ?>
<div class="container">

    <!-- Page Heading -->
    <div class="row">
        <div class="col-md-11">
            <h3 class="page-header">
                Resumen de MRP
                <small id="parameter">Necesidades de Materia Prima  ({{$fechauser}}/{{$tipo}})</small>
            </h3>
            
            <h5>{{$text}}</h5>
            <!-- <h5>Fecha & hora: {{\AppHelper::instance()->getHumanDate(date('d-m-Y h:i a', strtotime("now")))}}</h5> -->
        </div>
    </div>
     <div  id="infoMessage" class="alert alert-info" role="alert">
        ¡Importante!  Para un mejor rendimiento de las descargas, aplicar filtros al MRP.
     </div> 
    <!-- /.row -->
    <div class="row">
        
        <div class="col-md-12">
            
            <table id="tmrp" class="stripe cell-border display" >
                        <thead class="table-condensed">
                         <tr>

                         </tr>
                         </thead>
                       
                    </table>
        </div> <!-- /.col-md-12 -->

   </div> <!-- /.row -->
<input hidden value="{{$fechauser}}" id="fechauser" name="fechauser" />
<input hidden value="{{$tipo}}" id="tipo" name="tipo" />

</div>
    <!-- /.container -->
@endsection
 
@section('homescript')





var data,
tableName= '#tmrp',
columnas,
str,
jqxhr =  $.ajax({
        dataType:'json',
        type: 'GET',
        data:  {
              fechauser :$('input[name=fechauser]').val(),
              tipo :$('input[name=tipo]').val()       
                            
            },
        url: '{!! route('datatables.showmrp') !!}',
        success: function(data, textStatus, jqXHR) {
            data = JSON.parse(jqxhr.responseText);
            // Iterate each column and print table headers for Datatables
            $.each(data.columns, function (k, colObj) {
                str = '<th>' + colObj.name + '</th>';
                $(str).appendTo(tableName+'>thead>tr');
               // console.log("adding col "+ colObj.name);
            });
            columnas = data.columns;
            // Add some Render transformations to Columns
            // Not a good practice to add any of this in API/ Json side
            //data.columns[0].render = function (data, type, row) {
            //    return '<h4>' + data + '</h4>';
           // }
                    // Debug? console.log(data.columns[0]);
          var table = $(tableName).DataTable({
               
                dom: 'Blrtfip',
                orderCellsTop: true,    
                scrollY:        "300px",
                "pageLength": 50,
                scrollX:        true,
                paging:         true,
                fixedColumns: true,
                processing: true,
                deferRender:    true,
                scrollCollapse: true, 
                data:data.data,
                columns: data.columns,
                buttons: [
                    {
                        text: '<i class="fa fa-columns" aria-hidden="true"></i> Columna',
                        className: "btn btn-primary",
                        extend: 'colvis',
                        postfixButtons: [                                  
                            {
                                text: 'Restaurar columnas',
                                extend: 'colvisRestore',     
                            }             
                            ]
                    },
                    {
                        text: '<i class="fa fa-copy" aria-hidden="true"></i> Copy', 
                        extend: 'copy',    
                        exportOptions: {
                            columns: ':visible',                
                        }             
                    },
                    {
                        text: '<i class="fa fa-file-excel-o"></i> Excel',
                        className: "btn-success",
                        action: function ( e, dt, node, config ) {
                                             console.log("excel btn");               
                                    var data=table.rows( { filter : 'applied'} ).data().toArray();               
                                    var json = JSON.stringify( data );
                                     var col = JSON.stringify(columnas);
                                    //console.log(col);
                                    $.ajax({
                                        type:'POST',
                                        url:'ajaxtosession/mrp',
                                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},                            
                                        data: {
                                            "_token": "{{ Session::token() }}",
                                            "arr": json,
                                            "cols":  col,
                                            "parameter": "Resumen de MRP (SIZ), Necesidades de Materia Prima ("+$('input[name=fechauser]').val()+"/"+$('input[name=tipo]').val()+")"
                                            },
                                            success:function(data){
                                             window.location.href = 'mrpXLS';                                   
                                        }
                                    });
                                }         
                    }, 
                    
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json",
                    buttons: {
                        copyTitle: 'Copiar al portapapeles',
                        copyKeys: 'Presiona <i>ctrl</i> + <i>C</i> para copiar o la tecla <i>Esc</i> para continuar.',
                        copySuccess: {
                            _: '%d filas copiadas',
                            1: '1 fila copiada'
                        }
                    }
                },
                columnDefs: [
                    
                   
                ], 
            });

            $('#tmrp thead tr:eq(1) th').each( function (i) {
            var title = $(this).text();
            $(this).html( '<input type="text" placeholder="Filtro '+title+'" />' );
            
            $( 'input', this ).on( 'keyup change', function () {
            
            if ( table.column(i).search() !== this.value ) {
            table
            .column(i)
            .search(this.value, true, false)
            .draw();
            
            }
            
            } );
            } );
            yadcf.init(table,
            [
           
            {
                column_number : [4],
                filter_type: 'range_number',
                filter_default_label: ["Min", "Max"]
            },
            {
                column_number : [5],
                filter_type: 'range_number',
                filter_default_label: ["Min", "Max"]
            },
            {
                column_number : [6],
                filter_type: 'range_number',
                filter_default_label: ["Min", "Max"]
            },
            {
                column_number : [(Object.keys(data.columns).length) - 4], //Col TE
                filter_type: 'range_number',
                filter_default_label: ["Min", "Max"]
            },
            {
                column_number : [(Object.keys(data.columns).length) - 7], //Col Pto. Reorden
                filter_type: 'range_number',
                filter_default_label: ["Min", "Max"]
            },
            {
                column_number : [(Object.keys(data.columns).length) - 9], //Col Necesidad
                filter_type: 'range_number',
                filter_default_label: ["Min", "Max"]
            },
            
            ],
            );
        },
        error: function(jqXHR, textStatus, errorThrown) {
            var msg = '';
            if (jqXHR.status === 0) {
                msg = 'Not connect.\n Verify Network.';
            } else if (jqXHR.status == 404) {
                msg = 'Requested page not found. [404]';
            } else if (jqXHR.status == 500) {
                msg = 'Internal Server Error [500].';
            } else if (exception === 'parsererror') {
                msg = 'Requested JSON parse failed.';
            } else if (exception === 'timeout') {
                msg = 'Time out error.';
            } else if (exception === 'abort') {
                msg = 'Ajax request aborted.';
            } else {
                msg = 'Uncaught Error.\n' + jqXHR.responseText;
            }
console.log(msg);
        }
        });


$('#tmrp thead tr').clone(true).appendTo( '#tmrp thead' );





@endsection
<script>
    document.onkeyup = function(e) {
   if (e.shiftKey && e.which == 112) {
    window.open("ayudas_pdf/AYM00_00.pdf","_blank");
  }
  }


             
                      
</script>