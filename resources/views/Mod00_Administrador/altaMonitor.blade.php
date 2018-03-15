@extends('app')

@section('content')
@include('partials.menu-admin')

    <div >

        <div class="container" >

            <!-- Page Heading -->
            <div class="row">
                <div >
                    <h3 class="page-header">
                        {{Route::current()->getName()}}
                    </h3>
                    <ol class="breadcrumb">
                        <li>
                            <i class="fa fa-dashboard"></i>  <a href="MOD00-ADMINISTRADOR">ADMINISTRADOR</a>
                        </li>
                        <li>
                            <i class="fa fa-archive"></i>  <a href="inventario">GESTIÒN DE INVENTARIO</a>
                        </li>
                        <li>
                            <i class="fa fa-archive"></i> <a href="monitores">MONITORES</a>
                        </li>
                        <li>
                            <i class="fa fa-archive"></i>  <a href="altaMonitor">ALTA MONITORES</a>
                        </li>

                    </ol>
                </div>
            </div>
            <!-- /.row -->
         <div class="container">
        <div class="row">
             {{--este form tiene que enviar la informacion para crear un modulo--}}
             <div class="col-md-6">
             {!! Form::open(['url' => 'admin/altaMonitor', 'method' => 'POST']) !!}
                <div class="form-group">
                    <label for="exampleFormControlInput1">Nombre Monitor</label>
                    <input type="text" id="nombre_monitor" name="nombre_monitor" class="form-control" placeholder="Ej. HP LV1911" required>
                </div>
                <button type="submit" class="btn btn-primary">Guardar</button>
             {!! Form::close() !!} 
             </div>
             @yield('subcontent-01')


             TEXT
         </div>



        </div>
        <!-- /.container-fluid -->

    </div>
    <!-- /#page-wrapper -->
    </div>
    </div>



    <!-- /#wrapper -->
@endsection

@section('script')





@endsection