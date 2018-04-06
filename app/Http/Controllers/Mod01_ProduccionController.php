<?php

namespace App\Http\Controllers;
use DateTime;
use App\Modelos\MOD01\LOGOT;
use App\User;
use App\OP;
use App\Modelos\MOD01\LOGOF;
use App\Http\Controllers\Controller;
use Hash;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Session;
use Auth;
use Lava;
class Mod01_ProduccionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
            return view('Mod00_Administrador.admin');
    }

    public function estacionSiguiente( Request $request)
    {
       // echo OP::getEstacionSiguiente("81143");
      // dd(OP::getStatus("81143"));
      // dd(OP::getRuta("81143"));
    }

    public function allUsers(Request $request){
            $users = DB::select('select * from view_Plantilla_SIZ');
        $users = $this->arrayPaginator($users, $request);
        $stocksTable = Lava::DataTable();
        $stocksTable->addDateColumn('Day of Month')
            ->addNumberColumn('Projected')
            ->addNumberColumn('Official');

        // Random Data For Example
        for ($a = 1; $a < 30; $a++) {
            $stocksTable->addRow([
                '2015-10-' . $a, rand(800,1000), rand(800,1000)
            ]);
        }

        $beto = Lava::AreaChart('beto', $stocksTable, [
            'title' => 'Population Growth',
            'legend' => [
                'position' => 'in'
            ]
        ]);

        return view('Mod00_Administrador.usuarios', compact('users', 'beto'));
    }

    public function arrayPaginator($array, $request)
    {
        $page = Input::get('page', 1);
        $perPage = 10;
        $offset = ($page * $perPage) - $perPage;

        return new \Illuminate\Pagination\LengthAwarePaginator(array_slice($array, $offset, $perPage, true), count($array), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]);
    }

    public function usuariosActivos( Request $request)
    {
        $users = User::name($request->get('name'))->where('jobTitle', '<>' , 'Z BAJA')->where('status', '1')
            ->orderBy('Ohem.dept, OHEM.jobTitle, ohem.firstname', 'asc')
           ;
        dd($users);
        return view('Mod00_Administrador.usuarios', compact('users'));
    }

    public function cambiopassword(){
        try {
            $password = Hash::make(Input::get('password'));
            DB::table('dbo.OHEM')
                ->where('U_EmpGiro',Input::get('userId') )
                ->update(['U_CP_Password' => $password]);
        } catch(\Exception $e) {
            return redirect()->back()->withErrors(array('msg' => $e->getMessage()));
        }
        $user = User::find(Input::get('userId'));
        Session::flash('mensaje', 'La contraseña de '.$user->firstName.' '.$user->lastName.' ha cambiado.');
        return redirect()->back();
    }

    public function traslados(Request $request)
    {
        $enviado = $request->input('send');
//dd($request->input('miusuario'));

        if (Auth::check()){
            $user = Auth::user();
            $actividades = $user->getTareas();
        if  ($enviado == 'send')
        {
            if (strlen($request->input('miusuario')) == null){
                $t_user =  Auth::user();
            }else{
                $t_user = User::find($request->input('miusuario'));
                if ($t_user == null){
                    return redirect()->back()->withErrors(array('message' => 'Error, el usuario no existe.'));
                }
            }

            if (Hash::check($request->input('pass'), $t_user->U_CP_Password)) {
                Session::flash('usertraslados', 1);
                return view('Mod01_Produccion.traslados', ['actividades' => $actividades, 'ultimo' => count($actividades), 't_user' => $t_user]);
            }else{
                return redirect()->back()->withErrors(array('message' => 'Error de autenticación.'));
            }

        }else{

            Session::flash('usertraslados', false);
        }


            return view('Mod01_Produccion.traslados', ['actividades' => $actividades, 'ultimo' => count($actividades)]);
        }else{
            return redirect()->route('auth/login');
        }

    }
    public function getOP($id)
    {
        if (Session::has('op')) {
            $op = Session::get('op');
            Session::forget('op');
        }else if (Input::has('op'))
        {
            $op = Input::get('op');
        }else{
            return redirect()->route('home');
        }

        $t_user = User::find($id);
        if ($t_user == null) {
            return redirect()->back()->withErrors(array('message' => 'Error, el usuario no existe.'));
        }

        $user = Auth::user();
        $actividades = $user->getTareas();
        Session::flash('usertraslados', 2);  //evita que salga el modal


        $Codes = OP::where('U_DocEntry',$op)->get();

        if (count($Codes) > 0){
            $index = 0;
        foreach ($Codes as $code) {

if ($code->U_Recibido > $code->U_Procesado){



           // dd($code->U_Recibido);
            if ($code->U_Recibido == '0' && $code->U_Procesado == '0' && $code->U_Entregado == '0'){
           $cantlogof = DB::table('@CP_LOGOF')
               ->where('U_DocEntry', $code->U_DocEntry)
               ->get();

           //dd($cantlogof);

                if (count($cantlogof) == 0){
                    $CantOrden = DB::table('OWOR')
                        ->where('DocEntry', $code->U_DocEntry)
                        ->first();

                   // dd($CantOrden->PlannedQty);
                    $code->U_Recibido = (int) $CantOrden->PlannedQty;
                    $code->save();
                }

            }
 //dd($code);
            $index = $index + 1;

            $order = DB::table('OWOR')
                ->leftJoin('OITM', 'OITM.ItemCode', '=', 'OWOR.ItemCode')
                ->leftJoin('@CP_OF', '@CP_OF.U_DocEntry', '=', 'OWOR.DocEntry')
                ->select(DB::raw(OP::getEstacionActual($code->Code) . ' AS U_CT_ACT'), DB::raw(OP::getEstacionSiguiente($code->Code, 1) . ' AS U_CT_SIG'), DB::raw(OP::avanzarEstacion($code->Code, $t_user->U_CP_CT) . ' AS avanzar'),
                    'OWOR.DocEntry', '@CP_OF.Code', '@CP_OF.U_Orden', 'OWOR.Status', 'OWOR.OriginNum', 'OITM.ItemName', '@CP_OF.U_Reproceso',
                    'OWOR.PlannedQty', '@CP_OF.U_Recibido', '@CP_OF.U_Procesado')
                ->where('@CP_OF.Code', $code->Code)->get();
            if ($index == 1) {
                $one = DB::table('OWOR')
                    ->leftJoin('OITM', 'OITM.ItemCode', '=', 'OWOR.ItemCode')
                    ->leftJoin('@CP_OF', '@CP_OF.U_DocEntry', '=', 'OWOR.DocEntry')
                    ->select(DB::raw(OP::getEstacionActual($code->Code) . ' AS U_CT_ACT'), DB::raw(OP::getEstacionSiguiente($code->Code, 1) . ' AS U_CT_SIG'),
                        DB::raw(OP::avanzarEstacion($code->Code, $t_user->U_CP_CT) . ' AS avanzar'),
                        'OWOR.DocEntry', '@CP_OF.Code', '@CP_OF.U_Orden', 'OWOR.Status', 'OWOR.OriginNum', 'OITM.ItemName', '@CP_OF.U_Reproceso',
                        'OWOR.PlannedQty', '@CP_OF.U_Recibido', '@CP_OF.U_Procesado')
                    ->where('@CP_OF.Code', $code->Code)->get();
                foreach ($one as $o) {
                    $pedido = $o->OriginNum;
                }
            } else {

                $one = array_merge($one, $order); //$one->merge($order);
                //dd($one);
            }

        }
        }
        //  $order = OP::find('492418');
        //return $one;


        // $actual = OP::getEstacionActual(Input::get('op'));
        // $siguiente = OP::getEstacionSiguiente(Input::get('op'));

        return view('Mod01_Produccion.traslados', ['actividades' => $actividades, 'ultimo' => count($actividades), 't_user' => $t_user, 'ofs' => $one, 'op' => $op, 'pedido' => $pedido]);

    }
        return redirect()->back()->withErrors(array('message' => 'La OP '.Input::get('op').' no existe.'));


    }

    public function avanzarOP(){


        try{

            $id = Input::get('userId');

            $t_user = User::find($id);
            if ($t_user == null) {
                return redirect()->back()->withInput()->withErrors(array('message' => 'Error, el usuario no existe.'));

            }

            $Cant_procesar = Input::get('cant');
            $Code_actual = OP::find(Input::get('code'));
            Session::put('op', $Code_actual->U_DocEntry);
//dd($Code_actual);
            $U_CT_siguiente = OP::getEstacionSiguiente($Code_actual->Code, 2);

            if ($U_CT_siguiente == $Code_actual->U_CT){
                Session::flash('info', 'La estacion '.OP::getEstacionSiguiente($Code_actual->Code, 1).' es la ultima');
                return redirect()->back();
            }

            //  $cant_pendiente = $Code_actual->U_Recibido - $Code_actual->U_Procesado;
// ->where(DB::raw('(U_Recibido - U_Procesado)', '>', '0'))
            $Code_siguiente =  OP::where('U_CT', $U_CT_siguiente)
                ->where('U_DocEntry', $Code_actual->U_DocEntry)
                ->where('U_Reproceso', 'N')
                ->get();

            $dt = date('d-m-Y H:i:s');
            $CantOrden = DB::table('OWOR')
                ->where('DocEntry', $Code_actual->U_DocEntry)
                ->first();
            $cantO = (int)$CantOrden->PlannedQty;
            //dd($Code_siguiente);
            if (count($Code_siguiente) == 1){
                $Code_siguiente =  OP::where('U_CT', $U_CT_siguiente)
                    ->where('U_DocEntry', $Code_actual->U_DocEntry)
                    ->where('U_Reproceso', 'N')
                    ->first();
               // dd( ($Cant_procesar + $Code_siguiente->U_Recibido) <= (Input::get('numcant')+$Code_actual->U_Procesado));


                if ( ($Cant_procesar + $Code_siguiente->U_Recibido) <= $cantO){

                    $Code_siguiente->U_Recibido = $Code_siguiente->U_Recibido + $Cant_procesar;
                    $Code_siguiente->save();

                }else{
                    return redirect()->back()->withInput()->withErrors(array('message' => 'La cantidad total recibida no debe ser mayor a la cantidad de la Orden.'));
                }
            }else if(count($Code_siguiente) == 0){
                try{
                    $consecutivo =  DB::select('select top 1 MAX( mm.Code) as Code from [@CP_Of] mm inner join (SELECT TOP 1 Code, U_DocEntry FROM [@CP_LogOF] ORDER BY  U_FechaHora DESC) as tt on tt.U_DocEntry = mm.U_DocEntry');

                    $newCode = new OP();
                    $newCode->Code = ((int)$consecutivo[0]->Code)+1;
                    $newCode->Name = ((int)$consecutivo[0]->Code)+1;
                    $newCode->U_DocEntry = $Code_actual->U_DocEntry;
                    $newCode->U_CT = $U_CT_siguiente;
                    $newCode->U_Entregado = 0;
                    $newCode->U_Orden = $U_CT_siguiente;
                    $newCode->U_Procesado = 0;
                    $newCode->U_Recibido = $Cant_procesar;
                    $newCode->U_Reproceso = "N";
                    $newCode->U_Defectuoso = 0;
                    $newCode->U_Comentarios = "";
                    $newCode->U_CTCalidad = 0;
                    $newCode->save();

                    $consecutivologot =  DB::select('SELECT TOP 1 Code FROM  [@CP_LOGOT] ORDER BY  U_FechaHora DESC');
                    $lot = new LOGOT();
                    $lot->Code = ((int)$consecutivologot[0]->Code)+1;
                    $lot->Name = ((int)$consecutivologot[0]->Code)+1;
                    $lot->U_idEmpleado = $id;
                    $lot->U_CT = $Code_actual->U_CT;
                    $lot->U_Status = "O";
                    $lot->U_FechaHora = $dt;
                    $lot->U_OP = $Code_actual->U_DocEntry;
                    $lot->save();

                }catch (Exception $e)
                {
                    return redirect()->back()->withInput()->withErrors(array('message' => 'Error al guardar nuevo registro en CP_OF.'));
                }
            }
            else{
                return redirect()->back()->withInput()->withErrors(array('message' => 'Existen registros duplicados en la siguiente estación.'));
            }
            // dd(count($Code_siguiente));
            $Code_actual->U_Procesado = $Code_actual->U_Procesado + $Cant_procesar;
            $Code_actual->U_Entregado = $Code_actual->U_Entregado + $Cant_procesar;


            $consecutivologof =  DB::select('SELECT TOP 1 Code FROM  [@CP_LOGOF] ORDER BY  U_FechaHora DESC');
            $log = new LOGOF();
            $log->Code = ((int)$consecutivologof[0]->Code)+1;
            $log->Name = ((int)$consecutivologof[0]->Code)+1;
            $log->U_idEmpleado = $id;
            $log->U_CT = $Code_actual->U_CT;
            $log->U_Status = "T";
            $log->U_FechaHora = $dt;
            $log->U_DocEntry = $Code_actual->U_DocEntry;
            $log->U_Cantidad = $Cant_procesar;
            $log->U_Reproceso = 'N';

            $Code_actual->save();
            $log->save();
       // dd($cantO);

            Session::flash('mensaje', 'El usuario '.$id.' avanzo '.$Cant_procesar.' pza(s) a la estación '.OP::getEstacionSiguiente($Code_actual->Code, 1));

            if ($Code_actual->U_Recibido > 0 && $cantO == $Code_actual->U_Procesado){
                $lineaActual = OP::find($Code_actual->Code);   //si esta linea ya termino de procesar_todo entonces se borra
                $lineaActual->delete();
            }



            return redirect()->back()->withInput();
        }catch (Exception $e){
            return redirect()->back()->withInput()->withErrors(array('message' => 'Error al Guardar la Orden.'));
        }

        //eliminar linea procesada completa de CP_OF
        //creacion de linea  en CP_LOGOF y CP_LOGOT

    }

    public function MethodGET_OP($id)
    {

        if (Session::has('op')) {
           $op = Session::get('op');
           Session::forget('op');
        }else if (Input::has('op'))
        {
$op = Input::get('op');
        }else{
            return redirect()->route('home');
        }

        $t_user = User::find($id);
        if ($t_user == null) {
            return redirect()->back()->withErrors(array('message' => 'Error, el usuario no existe.'));
        }

        $user = Auth::user();
        $actividades = $user->getTareas();
        Session::flash('usertraslados', 2);  //evita que salga el modal


        $Codes = OP::where('U_DocEntry', Input::get('op'))->get();
        if (count($Codes) > 0){
            $index = 0;
            foreach ($Codes as $code) {
                $index = $index + 1;
                $order = DB::table('OWOR')
                    ->leftJoin('OITM', 'OITM.ItemCode', '=', 'OWOR.ItemCode')
                    ->leftJoin('@CP_OF', '@CP_OF.U_DocEntry', '=', 'OWOR.DocEntry')
                    ->select(DB::raw(OP::getEstacionActual($code->Code) . ' AS U_CT_ACT'), DB::raw(OP::getEstacionSiguiente($code->Code, 1) . ' AS U_CT_SIG'), DB::raw(OP::avanzarEstacion($code->Code, $t_user->U_CP_CT) . ' AS avanzar'),
                        'OWOR.DocEntry', '@CP_OF.Code', '@CP_OF.U_Orden', 'OWOR.Status', 'OWOR.OriginNum', 'OITM.ItemName', '@CP_OF.U_Reproceso',
                        'OWOR.PlannedQty', '@CP_OF.U_Recibido', '@CP_OF.U_Procesado')
                    ->where('@CP_OF.Code', $code->Code)->get();
                if ($index == 1) {
                    $one = DB::table('OWOR')
                        ->leftJoin('OITM', 'OITM.ItemCode', '=', 'OWOR.ItemCode')
                        ->leftJoin('@CP_OF', '@CP_OF.U_DocEntry', '=', 'OWOR.DocEntry')
                        ->select(DB::raw(OP::getEstacionActual($code->Code) . ' AS U_CT_ACT'), DB::raw(OP::getEstacionSiguiente($code->Code, 1) . ' AS U_CT_SIG'),
                            DB::raw(OP::avanzarEstacion($code->Code, $t_user->U_CP_CT) . ' AS avanzar'),
                            'OWOR.DocEntry', '@CP_OF.Code', '@CP_OF.U_Orden', 'OWOR.Status', 'OWOR.OriginNum', 'OITM.ItemName', '@CP_OF.U_Reproceso',
                            'OWOR.PlannedQty', '@CP_OF.U_Recibido', '@CP_OF.U_Procesado')
                        ->where('@CP_OF.Code', $code->Code)->get();
                    foreach ($one as $o) {
                        $pedido = $o->OriginNum;
                    }
                } else {

                    $one = array_merge($one, $order); //$one->merge($order);
                    //dd($one);
                }
            }
            //  $order = OP::find('492418');
            //return $one;


            // $actual = OP::getEstacionActual(Input::get('op'));
            // $siguiente = OP::getEstacionSiguiente(Input::get('op'));

            return view('Mod01_Produccion.traslados', ['actividades' => $actividades, 'ultimo' => count($actividades), 't_user' => $t_user, 'ofs' => $one, 'op' => $op, 'pedido' => $pedido]);

        }
        return redirect()->back()->withErrors(array('message' => 'La OP '.Input::get('op').' no existe.'));


    }

}