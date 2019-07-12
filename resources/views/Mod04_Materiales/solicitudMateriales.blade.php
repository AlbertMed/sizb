@extends('home') 
@section('homecontent')

<style>
    th, td{
        font-size: 12px;
    }
    
    .table{
        width: auto;
        margin-bottom:0px;
    }
    .detalle {
     margin-left: 3%;
    }
    .table > thead > tr > th, 
    .table > tbody > tr > th, 
    .table > tfoot > tr > th, 
    .table > thead > tr > td, 
    .table > tbody > tr > td,
    .table > tfoot > tr > td { 
        padding-bottom: 2px; padding-top: 2px; padding-left: 4px; padding-right: 0px;
    }
   
    .list-group-item {
        border: 1px solid #b3b0b0;
        padding: 3px 10px
    }
.list-group-item:last-child {
margin-bottom: 10px;

}
h5 small {
    font-size:100%;
}
.container {
padding-right: 15px;
padding-left: 15px;
margin-right: 15px;
margin-left: 10px;
}
.green-edit-field {
border: 1px solid #000000;  
}

</style>
<style>
    div.dataTables_wrapper div.dataTables_processing {
        width: 500px;
        height: 200px;
        margin-left: 0%;
        background: linear-gradient(to right, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.95) 25%, rgba(255, 255, 255, 0.95) 75%, rgba(255, 255, 255, 0.2) 100%);
        z-index: 15;
    }

    input {
        color: black;
    }

    div.dataTables_wrapper {
        margin: 0;
    }

    div.container {
        min-width: 100%;
        margin: 0 auto;
    }

    table {
        //me ayudo a que no se desfazaran las columnas en Chrome
        table-layout: fixed;
    }

   .ignoreme{
       background-color: hsla(0, 100%, 46%, 0.10) !important;       
   }
</style>

<div class="container"  ng-controller="MainController">
 {!! Form::open(['url' => 'articuloToSap', 'method' => 'POST', 'id' => 'mainform']) !!}
 {{ csrf_field() }}
    <!-- Page Heading -->
    <div class="row">
        
            <div class="visible-xs visible-sm"><br><br></div>
         
            <div class="col-md-12">
                <h3 class="page-header">
                    Solicitud de Materiales
                    <div class="visible-xs visible-sm"><br></div>                 
                </h3>
                
            </div>
        
    </div>
   <div class="row">
    <div class="col-md-12">
        @include('partials.alertas')
    </div>
</div>
    <div class="row">      
        <div class="col-lg-6">
            <div class="input-group">
                
                <span class="input-group-btn">
                    <button class="btn btn-primary" id="myBtn" type="button">Agregar</button>
                </span>
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
    </div><!-- /.row -->
    <div class="row">
       
     
    </div>   <!-- /.row -->
    
    <div class="row">
    
    </div> <!-- /.row -->
    
{!! Form::close() !!}

                   <div class="row">
                       <div class="col-md-12">
                           <div class="modal fade" id="confirma" tabindex="-1" role="dialog">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                                    aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title" id="pwModalLabel">Agregar Artículo <small>Busca y selecciona el artículo de lista</small></h4>
                                        </div>
                                        
                                        <div class="modal-body">
                                            
                                            <div class="row">
                                               <div class="col-md-12">
                                                   <table id="tabla" width="100%" class="table-condensed stripe cell-border display" style="width:100%">
                                                        <thead class="">
                                                            <tr>
                                                                <th><i>Código</i></th>
                                                                <th>Descripción</th>
                                                                <th>UM</th>
                                                                <th>Existencia</th>
                                                            </tr>
                                                        </thead>
                                                    
                                                    </table>
                                               </div>
                                            </div>
                                           
                                            <form name="modalForm" ng-submit="AddArt()" >
                                            <div class="row">
                                                <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="pKey">Código:</label>
                                                    <input type="text" readonly name="pKey" class="form-control"
                                                        ng-model="insert.pKey">
                                             
                                                </div>                                               
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="cant">Cantidad:</label>
                                                        
                                                        <input type="number" step="0.01" min="0" class="form-control" name="cant" id="cant"
                                                     ng-model="insert.cant" required>
                                                    </div>                                               
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="destino">Surtirse en:</label>
                                                        <select class="form-control" name="destino" id="destino" ng-model="insert.destino" required>
                                                            <option></option>
                                                            @foreach($rutasConNombres as $key)
                                                            <option class="col-md-6" value="{{$key->Code}}">{{$key->Name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" id="submitBtn" class="btn btn-primary" disabled>Agregar</button>
                                        </div>
                                    </form>
                                    </div>
                                </div>
                            </div>
                       </div>
                   </div>
</div>
    <!-- /.container -->
@endsection

@section('homescript')

var data,
    tableName= '#tabla',
    table,
    str,
    jqxhr = $.ajax({
    dataType:'json',
    type: 'GET',
    data: {
    
    },
    url: '{!! route('OITM.WH.show') !!}',
    success: function(data, textStatus, jqXHR) {
    data = JSON.parse(jqxhr.responseText);
  
    data.columns[3].render = function (data, type, row) {
    
    var val = new Intl.NumberFormat("es-MX", {minimumFractionDigits:2}).format(data);
    return val;
    }
    
     table = $(tableName).DataTable({
    dom: 'irtp',
    orderCellsTop: true,
    "order": [[ 1, "asc" ]],
    "autoWidth":true,
    scrollY: "200px",
    "pageLength": 50,
    scrollX: true,
    paging: true,
    processing: true,
    deferRender: true,
    scrollCollapse: true,
    data:data.data,
    columns: data.columns,
    "language": {
    "url": "{{ asset('assets/lang/Spanish.json') }}",
    },
    columnDefs: [
    { width: "7px", targets: 0},
   
    ],
   "rowCallback": function( row, data, index ) {
        //console.log(data['Existencia']);
    if ( data['Existencia'] == '.000000' )
    {
      //  $(row).addClass("ignoreme");
        $('td',row).addClass("ignoreme");
       
    }
    },
    "initComplete": function(settings, json) {
     
    }
    
    });
    
    $('#tabla thead tr:eq(1) th').each( function (i) {
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
    
    $('#tabla tbody').on( 'click', 'tr', function () {
        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
            $('input[name=pKey]').val('');
            $('input[name=cant]').val('');
            $('input[name=cant]').blur();
            $('#submitBtn').attr("disabled", true);
        }
        else {           
                table.$('tr.selected').removeClass('selected');
                table.$('tr.usel').removeClass('usel');
                $(this).addClass('usel');
                var idx = table.cell('.usel', 0).index();
                var fila = table.rows( idx.row ).data();
               // console.log(fila[0]['Existencia']);
                if(fila[0]['Existencia'] == '.000000'){
                    $('input[name=pKey]').val('');
                    $('input[name=cant]').val('');
                    $('input[name=cant]').blur();
                    $('#submitBtn').attr("disabled", true);
                }else{
                    var valor = new Intl.NumberFormat("es-MX", {minimumFractionDigits:2}).format(fila[0]['Existencia']);
                    $(this).addClass('selected');
                   // console.log(fila[0]['ItemCode']);
                    $('input[name=pKey]').val(fila[0]['ItemCode']);
                   // $('input[name=cant]').attr('title', valor+" en Existencia");
                    $('input[name=cant]').focus();                    
                    $('input[name=cant]').attr('max', fila[0]['Existencia']);
                    $('input[name=cant]').attr('min',1);
                    $('#submitBtn').attr("disabled", false);                    
                }                           
        }
    } );
    
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
    
    $('#tabla thead tr').clone(true).appendTo( '#tabla thead' );

 $("#myBtn").click(function(){
$("#confirma").modal();


});
$(window).on('load',function(){
$('#confirma').modal('show');
});


@endsection
<script src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.7.2/angular.min.js"></script>
<script >
   
   var app = angular.module('app', [], function($interpolateProvider) {
$interpolateProvider.startSymbol('<%');
    $interpolateProvider.endSymbol('%>');
});
   app.controller("MainController",["$scope", function($scope, $http){
    $scope.insert = {};
    $scope.AddArt = function(){
        alert('ok');
    }
    }]);
</script>