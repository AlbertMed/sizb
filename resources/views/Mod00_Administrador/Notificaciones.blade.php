@extends('app')

@section('content')

@include('partials.menu-admin')



    <div >

        <div class="container" >

            <!-- Page Heading -->
            <div class="row">
                <div >
                    <div class="visible-xs"><br><br></div>
                    <h3 class="page-header">
                        Bandeja de  Mensajes 
                    </h3>
                       <div class= "col-lg-6.5 col-md-8 col-sm-7">
                        <div class="hidden-xs">
                        <div class="hidden-sm">
                        <ol class="breadcrumb">
                        <li>
                            <i class="fa fa-home"></i>  <a href="{!! url('home') !!}">Inicio</a>
                        </li>
                        <li>
                            <i class="fa fa-archive"></i> <a href="users">Usuarios</a>
                       </li>
                       <li>
                            <i class="fa fa-home"></i>  <a href="{!! url('/admin/Nueva') !!}">Nueva Noticia</a>
                        </li>
                    </ol>
                </div>
            </div>
            @include('partials.alertas')
            <table id="usuarios" class="table table-striped table-bordered table-condensed">
                                    <thead>
                                    <tr>
                                             <th>Id_Autor</th>
                                            <th>Autor</th>
                                            <th>Asunto</th>  
                                            <th>Descripción</th> 
                                            <th>Modificar</th>  
                                            <th>Eliminar</th>  
                                    </tr>
                                    </thead>
                                    <tbody>
                        @foreach ($noti as $campo)
                        <tr>
                        <th>{{ $campo->Id_Autor }}</th>
                        <td>{{ $campo->Autor }}</td>
                        <td>{{ $campo->Destinatario}}</td>
                        <td>{{ $campo->Descripcion }}</td>
                        <td>
                            <a href=" Mod_Noti/{{$campo->Id_Autor}}/{{0}}" class="btn btn-warning"><i class="glyphicon glyphicon-edit"></i</a>
                        </td>
                        <td>
                        <a href="delete_Noti/{{$campo->Id_Autor}}" class="btn btn-danger"><i class="glyphicon glyphicon-trash"value"delete"></i></a>
                        </td>
                        </tr>
                    @endforeach 
                    </tbody>
                                
    </div>

@endsection
