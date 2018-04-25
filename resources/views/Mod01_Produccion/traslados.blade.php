@extends('home')

@section('homecontent')

        <div class="container" >

            <!-- Page Heading -->
            <div class="row">
                <div class="col-lg-8 col-md-9 col-xs-10">
                <div class="hidden-lg"><br><br></div>
                    <h3 class="page-header">
                        Traslados
                        <small>Producción</small>
                    </h3>
                    <div class="visible-lg">
                    <ol class="breadcrumb">
                        <li>
                            <i class="fa fa-dashboard">  <a href="{!! url('home') !!}">Inicio</a></i>
                        </li>
                        <li>
                            <i class= "fa fa-archive"> <a href="traslados">Traslados</a></i>
                    </ol>
                    </div>
                </div>
            </div>
            <!-- /.row -->
            <div class="row">

                <div class="col-md-12 ">
                    @include('partials.alertas')

                    <div id="login" data-field-id="{{Session::get('usertraslados') }}" >
                    </div>
                    @if(Session::has('usertraslados') && Session::get('usertraslados')>0)



                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Usuario Traslado</h3>
                                </div>
                                <div class="panel-body">
                                    <h5>Usuario: {{$t_user->firstName.' '.$t_user->lastName}}</h5>
                                    <h5>Control de Piso: {{$t_user->U_EmpGiro}}</h5>
                                    @if(Session::get('usertraslados') == 1)
                                        {!! Form::open(['url' => 'home/TRASLADO ÷ AREAS/'.$t_user->U_EmpGiro, 'method' => 'POST']) !!}
                                        <label for="op" class="control-label">Introduce Orden de Produccion:</label>
                                        <input type="number" name="op" id="op" min="1" max="9999999999">


                                    @endif
                                </div>
                                @if(Session::get('usertraslados') == 1)
                                <div class="panel-footer">
                                    <div align="right">
                                        <button type="submit" class="btn btn-primary">Consultar</button>
                                    </div>

                                </div>
                                    {!! Form::close() !!}
                                @endif
                            </div>
                        </div>


                            @if(Session::get('usertraslados') == 2)



                              <div class="col-md-6">
                                  <div class="panel panel-default">
                                      <div class="panel-heading">
                                          <h3 class="panel-title">Orden de Producción</h3>
                                      </div>
                                      <div class="panel-body">
                                          <h5>O.P.: {{$op}} </h5>
                                          <h5>Pedido: {{$pedido}}</h5>
                                      </div>
                                  </div>
                              </div>
{!! Html::style('assets/css/tablas.css') !!}

<style>
   	/*
		Label the data
		*/
		td:nth-of-type(1):before { content: "Código"; }
		td:nth-of-type(2):before { content: "Descripción"; }
		td:nth-of-type(3):before { content: "Reproceso"; }
		td:nth-of-type(4):before { content: "Cantidad"; }
		td:nth-of-type(5):before { content: "Cantidad Recibida"; }
		td:nth-of-type(6):before { content: "Procesado"; }
		td:nth-of-type(7):before { content: "Estación Actual"; }
		td:nth-of-type(8):before { content: "Estación Siguiente"; }
		td:nth-of-type(9):before { content: "Retroceder OP"; }
        td:nth-of-type(10):before { content: "Avanzar OP"; }
</style>


            
<div class="row">
                      
                            <div style="overflow-x:auto" class="col-md-12">
                            
                         <table id="usuarios" class="table table-striped table-bordered table-condensed">
                                    <thead>
                                    <tr>

                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th>Reproceso</th>
                                        <th>Cantidad</th>
                                        <th>Cantidad Recibida</th>
                                        <th>Procesado</th>
                                        <th>Estación Actual</th>
                                        <th>Estación Siguiente</th>
                                        @if(Auth::user()->U_EmpGiro=='246')
                                        <th>Retroceder OP</th>
                                        @endif
                                        <th>Avanzar OP</th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($ofs as $of)
                                        <tr>
                                            {{-- <td align="center">  <button type="button" class="btn btn-default" data-toggle="modal" data-target="#mymodal" data-whatever="{{$o->empID}}">
                                                     <i class="fa fa-edit" aria-hidden="true"></i>
                                                 </button>
                                             </td>--}}

                                           {{-- <td align="center">  <a class="btn btn-default" href="{{url('users/edit/'.$o->empID)}}" role="button">
                                                    <i class="fa fa-edit" aria-hidden="true"></i>
                                                </a>
                                            </td>--}}
                                            <td> {{$of->Code}} </td>
                                            <td> {{$of->ItemName}} </td>
                                            <td> {{$of->U_Reproceso}} </td>
                                            <td> {{$of->PlannedQty}} </td>
                                            <td> {{$of->U_Recibido}} </td>
                                            <td> {{$of->U_Procesado}} </td>
                                            <td> {{$of->U_CT_ACT}} </td>
                                            <td> {{$of->U_CT_SIG}} </td>

                                                @if(Auth::user()->U_EmpGiro=='246')
                                            <td> <a class="btn btn-danger" data-toggle="modal"
                                            data-target="#Retroceder">
                                                    <i class="fa fa-mail-reply-all" aria-hidden="1">   Retroceder</i>
                                                </a> </td>
                                                @endif

                                            <td> <a class="btn btn-success {{$of->avanzar}}" data-toggle="modal"
                                                    data-target="#cantidad" data-whatever="{{$of->Code}}"
                                                    data-whatever2="{{$of->U_Recibido - $of->U_Procesado}}">
                                                    <i class="fa fa-send-o" aria-hidden="true">   Avanzar</i>
                                                </a> </td>
                                        </tr>
                                    @endforeach
</tbody>
                                </table></div></div>

                            @endif
                    @endif

                </div>
            </div>


            <!-- Modal -->

            <div class="modal fade" id="pass" role="dialog" >
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content" style=" background-color: rgb(189, 217, 254)">
                        <div class="modal-header" style="background-color: rgb(198,221,254)">

                            <h4 class="modal-title" id="pwModalLabel">Login</h4>
                        </div>
                        {!! Form::open(['url' => 'home/TRASLADO ÷ AREAS', 'method' => 'POST']) !!}
                        <div class="modal-body image">

                            <input type="text" hidden name="send" value="send">

                            <br>
                            <div class="row">
                                <div class="col-md-2 col-md-offset-1 col-xs-5 col-xs-offset-1">
                                    
                                    <img src= "{{ URL::asset('images/Mod01_Produccion/password.png')}}"
                                         alt="">
                                </div>
                                <div class="col-md-6 col-md-offset-1 col-xs-5">
                                    @include('partials.alertas')
                                    <div id="hiddendiv" style="display: none">
                                        <label for="miusuario" class="control-label">Usuario:</label>
                                        <input id="miusuario" type="number" class="form-control" name="miusuario" minlength="3" maxlength="5">
                                        <br>
                                    </div>

                                    <label for="pass" class="control-label">Contraseña:</label>
                                    <input id="pass" type="password" class="form-control" name="pass" required minlength="3" maxlength="10">
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">

                            <a  id="mostrar" onclick="mostrar()">Cambiar usuario</a>
                            <a  id="ocultar" onclick="ocultar()" style="display: none">Regresar</a>
                            &nbsp;&nbsp;&nbsp;
                            <button type="submit" class="btn btn-primary">Entrar</button>
                            <a type="button" class="btn btn-default"  href="{!!url('home')!!}">Cancelar</a>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div><!-- /modal -->


            <!--  cantidadModal -->
            <div class="modal fade" id="cantidad" role="dialog" >
                <div class="modal-dialog modal-sm" role="document">
                    {!! Form::open(['url' => 'home/traslados/avanzar/', 'method' => 'POST']) !!}
                    <div class="modal-content" >

                        <div class="modal-header" >

                            <h4 class="modal-title" id="pwModalLabel">Cantidad a Procesar</h4>
                        </div>

            <div class="modal-body">

               <div class="row">
                   <div class="col-md-6">
                       <input id="code" name="code" type="text" hidden>
                       @if(Session::has('usertraslados') && Session::get('usertraslados')>0)
                          <input id="userId" name="userId" type="text" hidden value="{{$t_user->U_EmpGiro}}">
                       @endif
                       <input id="numcant" name="numcant" type="text" hidden>
                       <label for="cant" class="control-label">Cantidad:</label>
                       <input id="cant" type="number" class="form-control" name="cant" minlength="3" maxlength="5" min="1" autofocus>

                   </div>
                   <div class="col-md-6">

                       <div class="row">
                           <a class="btn" onclick="cambiarvalor(7)">7</a>
                           <a class="btn" onclick="cambiarvalor(8)">8</a>
                           <a class="btn" onclick="cambiarvalor(9)">9</a>
                       </div>
                       <div class="row">
                           <a class="btn" onclick="cambiarvalor(4)">4</a>
                           <a class="btn" onclick="cambiarvalor(5)">5</a>
                           <a class="btn" onclick="cambiarvalor(6)">6</a>
                       </div>
                       <div class="row">
                           <a class="btn" onclick="cambiarvalor(1)">1</a>
                           <a class="btn" onclick="cambiarvalor(2)">2</a>
                           <a class="btn" onclick="cambiarvalor(3)">3</a>
                       </div>
                       <div class="row">

                       </div>
                   </div>
               </div>

            </div>
                        <div class="modal-footer">
                            
                            <a class="btn btn-default" onclick="limpiacant()"><i class="fa fa-eraser" aria-hidden="true"> </i></a>
                            <button type="submit" class="btn btn-primary">Procesar</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Cancelar</button>
                        </div>

                    </div>
                    {!! Form::close() !!}
                </div>
            </div><!-- /modal -->

            <!-- /cantidadModal-->

        </div>
 <!-- /.container -->
 

        <!-- .Model retroceso -->
<div class="modal fade" id="Retroceder" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="">Numero de Orden de Produccion OP</h4>
      </div>
      <div class="modal-body">
      {!! Form::open(['url' => 'home/traslados/Reprocesos', 'method' => 'POST']) !!}
      <div class="dropdown">
  <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
    Áreas anteriores
    <span class="caret"></span>
  </button>
  <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
    <li><a href="#">Action</a></li>
    <li><a href="#">Another action</a></li>
    <li><a href="#">Something else here</a></li>
    <li role="separator" class="divider"></li>
    <li><a href="#">Separated link</a></li>
  </ul>
</div>
        <div>
            <label for="message-text" class="control-label">Nota</label>
            <textarea class="form-control" id="message-text"></textarea>
          </div>
       
      </div>
      <div class="modal-footer">
      
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary">Enviar</button>
      </div>
      </div>
      {!! Form::close() !!}
    </div>
  </div>
</div>
        <!-- /.container -->
<?php
//$GraficaOrden=1;
if(isset($HisOrden)){
?>
<?= 
    Lava::render('AreaChart', 'HisOrden', 'chart');
?> 
<?php
}
?>
<div id="chart"></div>   

    

@endsection

@section('homescript')


    var myuser = $('#login').data("field-id");

    if(myuser == false){
            $('#pass').modal(
            {
                    show: true,
                    backdrop: 'static',
                    keyboard: false
            }
            );
    }

    $('#cantidad').on('show.bs.modal', function (event) {
       var button = $(event.relatedTarget) // Button that triggered the modal
       var recipient2 = button.data('whatever2') // Extract info from data-* attributes
       var recipient = button.data('whatever') // Extract info from data-* attributes
    // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
    // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
       var modal = $(this)

        modal.find('#cant').val(recipient2)
        modal.find('#code').val(recipient)
        modal.find('#numcant').val(recipient2)
        modal.find('#cant').attr('max', recipient2);
    });

@endsection

<script>

    function limpiacant() { numcant
        $("#cant").val($("#numcant").val());
    }

    function cambiarvalor(num) {
        $("#cant").val($("#cant").val() + num);
    }

    function ocultar(){
        $("#hiddendiv").hide();
        $("#ocultar").hide();
        $("#mostrar").show();
        $("#miusuario").removeAttr('required');
    };
function mostrar(){
        $("#hiddendiv").show();
        $("#mostrar").hide();
        $("#ocultar").show();
        $('#miusuario').attr('required', 'required');
    };

</script>