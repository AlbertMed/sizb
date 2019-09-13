<?php

namespace App\Http\Controllers;

use App;
use App\Http\Controllers\Controller;
use App\User;
use Auth;
use DB;
use App\OP;
use Dompdf\Dompdf;
//excel
use Illuminate\Http\Request;
//DOMPDF
use Illuminate\Support\Facades\Input;
use App\SAP;
use Session;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Datatables;
use Validator;
ini_set("memory_limit", '512M');
ini_set('max_execution_time', 0);
class Mod04_MaterialesController extends Controller
{
public function reporteEntradasAlmacen()
{
    if (Auth::check()) {
        $user = Auth::user();
        $actividades = $user->getTareas();  
        $data = array(        
            'actividades' => $actividades,
            'ultimo' => count($actividades),
            'db' => DB::getDatabaseName(),          
            'fi' => Input::get('FechIn'),
            'ff' => Input::get('FechaFa')
        );
        return view('Mod04_Materiales.reporteEntradasAlmacen', $data);
    } else {
        return redirect()->route('auth/login');
    }
}
    public function DataShowEntradasMP(Request $request)
    {
        if (Auth::check()) {
            $consulta = DB::select(DB::raw( "
          SELECT * FROM(
            SELECT 'ENTRADA G' as TIPO, PDN1.ItemCode, OPDN.DocNum, OPDN.DocDate, OPDN.CardCode, OPDN.CardName, PDN1.Price, PDN1.LineTotal, PDN1.VatSum, OPDN.DocCur, PDN1.Dscription, OPDN.DocRate, PDN1.WhsCode, PDN1.Quantity, PDN1.NumPerMsr, OPDN.NumAtCard
            ,PDN1.TotalFrgn, PDN1.VatSumFrgn 
            FROM   SALOTTO.dbo.OPDN OPDN INNER JOIN dbo.PDN1 PDN1 ON OPDN.DocEntry=PDN1.DocEntry
            WHERE  (CAST( OPDN.DocDate as DATE) 
                        BETWEEN '" . date('d-m-Y', strtotime($request->get('fi'))) . ' 00:00' . "' and '" . date('d-m-Y', strtotime($request->get('ff'))) . ' 23:59:59' . "'
            )AND (PDN1.WhsCode=N'AMG-CC' OR PDN1.WhsCode=N'AMG-ST' OR PDN1.WhsCode=N'AMG-FE' OR PDN1.WhsCode=N'AGG-RE' OR PDN1.WhsCode=N'AMG-KU' OR PDN1.WhsCode=N'AMP-BL' OR PDN1.WhsCode=N'APG-ST' OR PDN1.WhsCode=N'APG-PA' OR PDN1.WhsCode=N'ATG-ST' OR PDN1.WhsCode=N'ATG-FX' OR PDN1.WhsCode=N'AMP-TR' OR PDN1.WhsCode=N'ARG-ST')
UNION ALL
            SELECT 'ENTRADA L' as TIPO, PDN1.ItemCode, OPDN.DocNum, OPDN.DocDate, OPDN.CardCode, OPDN.CardName, PDN1.Price, PDN1.LineTotal, PDN1.VatSum, OPDN.DocCur, PDN1.Dscription, OPDN.DocRate, PDN1.WhsCode, PDN1.Quantity, PDN1.NumPerMsr, OPDN.NumAtCard
            ,PDN1.TotalFrgn, PDN1.VatSumFrgn 
            FROM   OPDN OPDN INNER JOIN PDN1 PDN1 ON OPDN.DocEntry=PDN1.DocEntry
            WHERE  (CAST( OPDN.DocDate as DATE) 
            BETWEEN '" . date('d-m-Y', strtotime($request->get('fi'))) . ' 00:00' . "' and '" . date('d-m-Y', strtotime($request->get('ff'))) . ' 23:59:59' . "'
            ) 
            AND (PDN1.WhsCode IS  NULL  OR  NOT (PDN1.WhsCode=N'AGG-RE' OR PDN1.WhsCode=N'AMG-CC' OR PDN1.WhsCode=N'AMG-FE' OR PDN1.WhsCode=N'AMG-KU' OR PDN1.WhsCode=N'AMG-ST' OR PDN1.WhsCode=N'AMP-BL' OR PDN1.WhsCode=N'AMP-TR' OR PDN1.WhsCode=N'APG-PA' OR PDN1.WhsCode=N'APG-ST' OR PDN1.WhsCode=N'ARG-ST' OR PDN1.WhsCode=N'ATG-FX' OR PDN1.WhsCode=N'ATG-ST'))
UNION ALL
            SELECT 'NOTA CREDITO' as TIPO, RPC1.ItemCode, ORPC.DocNum, ORPC.DocDate, ORPC.CardCode, ORPC.CardName, RPC1.Price, RPC1.LineTotal, RPC1.VatSum, ORPC.DocCur, RPC1.Dscription, ORPC.DocRate, RPC1.WhsCode, RPC1.Quantity, RPC1.NumPerMsr, ORPC.NumAtCard
            ,RPC1.TotalFrgn, RPC1.VatSumFrgn 
            FROM   ORPC ORPC INNER JOIN RPC1 RPC1 ON ORPC.DocEntry=RPC1.DocEntry
            WHERE  (CAST( ORPC.DocDate as DATE) 
            BETWEEN '" . date('d-m-Y', strtotime($request->get('fi'))) . ' 00:00' . "' and '" . date('d-m-Y', strtotime($request->get('ff'))) . ' 23:59:59' . "'
            ) 
            AND (RPC1.WhsCode IS  NULL  OR  NOT (RPC1.WhsCode=N'AGG-RE' OR RPC1.WhsCode=N'AMG-CC' OR RPC1.WhsCode=N'AMG-FE' OR RPC1.WhsCode=N'AMG-KU' OR RPC1.WhsCode=N'AMG-ST' OR RPC1.WhsCode=N'AMP-BL' OR RPC1.WhsCode=N'AMP-TR' OR RPC1.WhsCode=N'APG-PA' OR RPC1.WhsCode=N'APG-ST' OR RPC1.WhsCode=N'ARG-ST' OR RPC1.WhsCode=N'ATG-FX' OR RPC1.WhsCode=N'ATG-ST'))
UNION ALL
            SELECT 'DEVOLUCION' AS TIPO, RPD1.ItemCode, ORPD.DocNum, ORPD.DocDate, ORPD.CardCode, ORPD.CardName, RPD1.Price, RPD1.LineTotal, RPD1.VatSum, ORPD.DocCur, RPD1.Dscription, ORPD.DocRate, RPD1.WhsCode, RPD1.Quantity, RPD1.NumPerMsr, ORPD.NumAtCard
            ,RPD1.TotalFrgn, RPD1.VatSumFrgn 
            FROM   ORPD ORPD INNER JOIN RPD1 RPD1 ON ORPD.DocEntry=RPD1.DocEntry
            WHERE  (CAST( ORPD.DocDate as DATE) 
            BETWEEN '" . date('d-m-Y', strtotime($request->get('fi'))) . ' 00:00' . "' and '" . date('d-m-Y', strtotime($request->get('ff'))) . ' 23:59:59' . "'
            ) 
            AND (RPD1.WhsCode IS  NULL  OR  NOT (RPD1.WhsCode=N'AGG-RE' OR RPD1.WhsCode=N'AMG-CC' OR RPD1.WhsCode=N'AMG-FE' OR RPD1.WhsCode=N'AMG-KU' OR RPD1.WhsCode=N'AMG-ST' OR RPD1.WhsCode=N'AMP-BL' OR RPD1.WhsCode=N'AMP-TR' OR RPD1.WhsCode=N'APG-PA' OR RPD1.WhsCode=N'APG-ST' OR RPD1.WhsCode=N'ARG-ST' OR RPD1.WhsCode=N'ATG-FX' OR RPD1.WhsCode=N'ATG-ST'))
            
            ) T
            ORDER BY T.TIPO, T.DocNum, T.DocDate
        "));
        
        $request->session()->put( 'fechas_entradas', array(
                'fi' => $request->get('fi'),
                'ff' => $request->get('ff')
            ));
        
        $consulta = collect($consulta);
            return Datatables::of($consulta)
                ->addColumn('Cant', function ($consulta) {
                    return ($consulta->Quantity * $consulta->NumPerMsr);
                })
                ->addColumn('LineaTotal', function ($consulta) {
                    if ($consulta->DocCur == 'MXP') {
                        return $consulta->LineTotal;
                    } 
                    elseif ($consulta->DocCur == 'USD') {
                        return $consulta->TotalFrgn;
                    }
                })
                ->addColumn('Iva', function ($consulta) {
                    if ($consulta->DocCur == 'MXP') {
                        return $consulta->VatSum;
                    } 
                    elseif ($consulta->DocCur == 'USD') {
                        return $consulta->VatSumFrgn;
                    }
                })
                ->addColumn('TotalConIva', function ($consulta) {
                    if ($consulta->DocCur == 'MXP') {
                        return ($consulta->LineTotal + $consulta->VatSum);
                    } 
                    elseif ($consulta->DocCur == 'USD') {
                        return ($consulta->TotalFrgn + $consulta->VatSumFrgn);
                    }
                })
                
                ->make(true);
        } else {
            return redirect()->route('auth/login');
        }
    }
   
public function entradasPDF()
{
    $a = json_decode(Session::get('entradas'));
      
        $entradasL = array_filter($a, function ($value) {
            return $value->TIPO == 'ENTRADA L';
        });
        $entradasG = array_filter($a, function ($value) {
            return $value->TIPO == 'ENTRADA G';
        });
        $devoluciones = array_filter($a, function ($value) {
            return $value->TIPO == 'DEVOLUCION';
        });
        $notascredito = array_filter($a, function ($value) {
            return $value->TIPO == 'NOTA CREDITO';
        });
        
    // dd(\AppHelper::instance()->getHumanDate(array_get( Session::get('fechas_entradas'), 'ff')));
    $data = array('notascredito' => $notascredito, 'entradasL' => $entradasL, 'entradasG' => $entradasG, 'devoluciones' => $devoluciones, 'fechas_entradas' => Session::get('fechas_entradas'));
    $pdf = \PDF::loadView('Mod04_Materiales.ReporteEntradasPDF', $data);
        $pdf->setPaper('Letter', 'landscape')->setOptions(['isPhpEnabled' => true]);  
    return $pdf->stream('Siz_MP_Almacén' . ' - ' . $hoy = date("d/m/Y") . '.Pdf');
}

    public function entradasXLS()
    {
        $path = public_path() . '/assets/plantillas_excel/Mod_01/SIZ_entradas.xlsx';
        $data = json_decode(Session::get('entradas'));
        $fechas_entradas = Session::get('fechas_entradas');
        $fecha = 'Del: '. \AppHelper::instance()->getHumanDate(array_get($fechas_entradas, 'fi')).' al: '.
                \AppHelper::instance()->getHumanDate(array_get($fechas_entradas, 'ff'));

                Excel::load($path, function ($excel) use ($data, $fecha) {
            $excel->sheet('MP', function ($sheet) use ($data, $fecha) {

                $sheet->cell('C4', function ($cell) {
                    $cell->setValue(\AppHelper::instance()->getHumanDate(date("Y-m-d H:i:s")).' '. date("H:i:s"));
                });
                $sheet->cell('C5', function ($cell) use ($fecha) {
                    $cell->setValue($fecha);
                });
                $index = 7;
                foreach ($data as $row) {
                    $sheet->row($index, [
                     $row->DocNum, 
                     $row->TIPO,
                     $row->DocDate,
                     $row->CardCode,
                     $row->CardName,
                     $row->NumAtCard,
                     $row->ItemCode,
                     $row->Dscription,
                     $row->Cant,
                     $row->Price,
                     $row->LineaTotal,
                     $row->VatSum,
                     $row->TotalConIva,
                     $row->DocCur
                     
                    ]);
                    $index++;
                }
            });
        })
            ->setFilename('SIZ Reporte de Materia Prima')
            ->export('xlsx');
    }
    public function DM_Articulos(Request $request){
        if (Auth::check()) {
           
            $rules = [
                // 'fieldText' => 'required|exists:OITM,ItemCode',
                'pKey' => 'required',
                'costocompras' => 'min:0',

            ];
            $customMessages = [
                'pKey.required' => 'Ningun código seleccionado',
                'costocompras.min' => 'El costo de A-COMPRAS debe ser igual/mayor a cero',
                //'fieldText.exists' => 'El Código no existe.'
            ];
            $valid = Validator::make( $request->all(), $rules, $customMessages);
            
            if ($valid->fails()) {
                return redirect()->back()
                    ->withErrors($valid)
                    ->withInput();
            }
            
            $param = self::getParam_DM_Articulos(Input::get('pKey'));
            
            return view('Mod04_Materiales.DM_Articulos', $param);
            
        } else {
            return redirect()->route('auth/login');
        }
    }

    public function articuloToSap(Request $request){
        //monedacompras
        //grupop
        //metodo
        //proveedor
        //code
        //costocompras --
        //comprador
//dd($request->all());
         $rules = [
                // 'fieldText' => 'required|exists:OITM,ItemCode',
                'costocompras' => 'required|numeric',
            ];
            $customMessages = [
                'costocompras.required' => 'El costo de A-COMPRAS debe capturarse',
                'costocompras.numeric' => 'El costo de A-COMPRAS debe ser numérico'
            ];
            $valid = Validator::make($request->all(), $rules, $customMessages);
            
            if ($valid->fails()) {
                
                //$errors = new \Illuminate\Support\MessageBag();

                // add your error messages:
               // $errors->add('Error', 'El costo A-COMPRAS debe contener un número');
               $param = self::getParam_DM_Articulos(Input::get('pKey'));
               return view('Mod04_Materiales.DM_Articulos', $param)->withErrors($valid);
                    
            }else {
                 $result = SAP::SaveArticulo(Input::all());
                 if ($result != 'ok') {                    
                    Session::flash('error', $result);
                 } else {
                    Session::flash('mensaje','Artículo guardado.');
                 }
                 $param = self::getParam_DM_Articulos(Input::get('pKey'));
               return view('Mod04_Materiales.DM_Articulos', $param);
            }
       
    }
public static function getParam_DM_Articulos($item){
 $data = DB::select( "
                    select OITM.ItemCode, ItemName, oitm.CardCode, ocrd.CardName,ALM.*,
                    Costo1.Price as CostoEstandar, Costo1.Currency as MonedaEstandar,
                    Costo10.Price as CostoL10, Costo10.Currency as MonedaL10, 
                    Costo9.Price as CostoACompras, Costo9.Currency as MonedaACompras,
                    CostoUltima.Price as CostoU, CostoUltima.Currency as MonedaU, CostoUltima.DocDate as FechaUltimaCompra, 
                    OITM.InvntryUom as UM, OITM.BuyUnitMsr as UM_Com, OITM.PurPackUn as Factor,
                    UFD1.Descr as Grupo_Pla, tb.ItmsGrpNam as Grupo,
                    UF.Descr as Comprador, OITM.U_ReOrden AS Reorden, OITM.U_Minimo AS Minimo,
                    OITM.U_Maximo AS Maximo, OITM.LeadTime AS TE,OITM.NumInBuy Conversion,
                    (SELECT Descr from UFD1 WHERE TableID = 'OITM' AND FieldID = '18' AND FldValue = OITM.U_Metodo) Metodo, 
                    (SELECT Descr FROM UFD1 WHERE TableID = 'OITM' AND FieldID = '16' AND FldValue = OITM.U_Linea) as Linea,
                     rutas.Name AS Ruta, ordenes.oc as OC 
                    from oitm 
                    left join OCRD on OCRD.CardCode = oitm.CardCode
                    left JOIN
                    (SELECT        ItemCode, SUM(CASE WHEN 
                                                WhsCode = 'AMP-ST' OR
                                                WhsCode = 'AMP-CC' OR
                                                WhsCode = 'AMP-TR' OR
                                                WhsCode = 'AXL-TC' OR
                                                WhsCode = 'APG-PA' 
                                                THEN OnHand ELSE 0 END) AS A_Lerma, 
                                                SUM(CASE WHEN 
                                                WhsCode = 'AMG-ST' 
                                                --OR WhsCode = 'AMG-CC' 
                                                THEN OnHand ELSE 0 END) AS A_Gdl, 
                                                SUM(CASE WHEN 
                                                WhsCode = 'APP-ST' OR
                                                WhsCode = 'APT-PA' OR
                                                WhsCode = 'APG-ST'
                                                THEN OnHand ELSE 0 END) AS WIP,
                                                SUM(CASE WHEN 
                                                WhsCode = 'AMP-CO' OR
                                                WhsCode = 'ARF-ST' OR 
                                                WhsCode = 'AMP-FE'
                                                THEN OnHand ELSE 0 END) AS ALM_OTROS
                    FROM            dbo.OITW
                    GROUP BY ItemCode) AS ALM ON oitm.ItemCode = ALM.ItemCode
                    left join ITM1 Costo1 on Costo1.ItemCode = OITM.ItemCode
                    AND Costo1.PriceList = 1
                    left join ITM1 Costo10 on Costo10.ItemCode = OITM.ItemCode
                    AND Costo10.PriceList = 10
                    left join ITM1 Costo9 on Costo9.ItemCode = OITM.ItemCode
                    AND Costo9.PriceList = 9
                    left join UFD1 on UFD1.FldValue = OITM.U_GrupoPlanea AND UFD1.TableID = 'OITM'
                        AND UFD1.FieldID = 19
                    LEFT OUTER JOIN dbo.UFD1 AS UF ON OITM.U_Comprador = UF.FldValue
                    AND UF.TableID = 'OITM' 
                    left join OITB tb on tb.ItmsGrpCod = OITM.ItmsGrpCod
                    left join [@PL_RUTAS] rutas on rutas.Code = OITM.U_estacion
                    left join (SELECT P.DocEntry, P.ItemCode, P.Price, P.DocDate, P.Currency
                                    FROM PDN1 P 
                                    ) CostoUltima on CostoUltima.ItemCode = OITM.ItemCode
                                    AND CostoUltima.DocEntry = (Select max(DocEntry) from PDN1 where PDN1.ItemCode = OITM.ItemCode)
                    left join (SELECT  POR1.itemCode, SUM( OITM.NumInBuy * POR1.OpenQty ) as oc
                    FROM OPOR INNER JOIN POR1 ON OPOR.DocEntry = POR1.DocEntry LEFT JOIN OITM ON POR1.ItemCode = OITM.ItemCode 
                    
                    WHERE POR1.LineStatus <> 'C'  
                    group by POR1.ItemCode)as ordenes on ordenes.ItemCode = OITM.ItemCode
                    where oitm.ItemCode =  ?            
                ",[$item]); 
        
                $semanas = DB::select('exec SIZ_SP_Art ?, ?', ['semana', $item]);
         $columns = array();
         $sem = '';
         if (count($semanas) > 0) {
            $sem = json_decode(json_encode($semanas[0]), true);
            if ( array_key_exists('ant', $semanas[0]) ) {
                array_push($columns,["data" => "ant", "name" => "Anterior"]);
            } 
               $numerickeys = array_where(array_keys((array)$semanas[0]), function ($key, $value) {
                    return is_numeric($value);
                });
        //Antes de agregar hay que ordenar las columnas numericas obtenidas
        sort($numerickeys);
        //agregar columnas...  hasta 2099 usar 20, para 2100 a 2199 usar 21...
        $string_comienzo_anio = '20';
        foreach ($numerickeys as $value) {
            //averiguamos cuando inicia la semana
            $num_semana = substr($value, 2, 2);
            $year = $string_comienzo_anio. substr($value, 0, 2);
            $StartAndEnd=\AppHelper::instance()->getStartAndEndWeek($num_semana, $year);
            
            //preparamos el nombre
            $name = 'Sem-'.$num_semana.' '.$StartAndEnd['week_start'];
            array_push($columns,["data" => $value, "name" => $name]);        
         }
         } 
        $metodos = DB::select( 'SELECT FldValue, Descr FROM UFD1 WHERE TableID = ? AND FieldID = ? ORDER BY Descr', ['OITM',18]);
        $compradores = DB::select( 'SELECT FldValue, Descr FROM UFD1 WHERE TableID = ? AND FieldID = ? ORDER BY Descr', ['OITM',10]);
        $gruposPlaneacion = DB::select( 'SELECT FldValue, Descr FROM UFD1 WHERE TableID = ? AND FieldID = ? ORDER BY Descr', ['OITM',19]);
                 
        $user = Auth::user();
        $actividades = $user->getTareas();  
        $proveedores = DB::select('SELECT CardCode, CardName FROM OCRD WHERE CardType = ? ORDER BY CardName', ['S']);
        
        $tareas = json_decode(json_encode($actividades), true);
        foreach ($tareas as $tarea) {
        $privilegioTarea = array_search('DATOS MAESTROS ARTICULOS', $tarea);
        if ($privilegioTarea != false) {
             $privilegioTarea = $tarea['privilegio_tarea'];
             break;
        }
       }
       if (strpos($privilegioTarea, 'checked') !== false) {
        $privilegioTarea = '';            
       } else {
         $privilegioTarea = 'disabled';
       }
        $param = array(        
            'actividades' => $actividades,
            'ultimo' => count($actividades),
            'data' => $data,          
            'semanas' => $sem,
            'proveedores' => $proveedores,
            'columns' => $columns,
            'metodos' => $metodos,
            'compradores' => $compradores,
            'gruposPlaneacion' => $gruposPlaneacion,
            'privilegioTarea' => $privilegioTarea
        );
        return $param;
}

public function solicitudMateriales(){
     if (Auth::check()) {
        $user = Auth::user();
        $actividades = $user->getTareas();
        $almacenesDestino = DB::table('SIZ_AlmacenesTransferencias')
                            ->where('Dept', Auth::user()->dept)
                            ->where('SolicitudMateriales', 'D')->get();
    

        $param = array(        
            'actividades' => $actividades,
            'ultimo' => count($actividades),
            'almacenesDestino' => $almacenesDestino,  
        );
        return view('Mod04_Materiales.solicitudMateriales', $param);
    } else {
         return redirect()->route('auth/login');
    }
    
}
public function pickingArticulos(){
     if (Auth::check()) {
        $user = Auth::user();
        $actividades = $user->getTareas();  

        $param = array(        
            'actividades' => $actividades,
            'ultimo' => count($actividades),          
        );
        return view('Mod04_Materiales.ShowSolicitudes', $param);
    } else {
         return redirect()->route('auth/login');
    } 
}
public function TrasladosArticulos(){
     if (Auth::check()) {
        $user = Auth::user();
        $actividades = $user->getTareas();  

        $param = array(        
            'actividades' => $actividades,
            'ultimo' => count($actividades),          
        );
        return view('Mod04_Materiales.ShowTraslados', $param);
    } else {
         return redirect()->route('auth/login');
    } 
}
public function AutorizacionSolicitudes(){
     if (Auth::check()) {
        $user = Auth::user();
        $actividades = $user->getTareas();         
      
        $param = array(        
            'actividades' => $actividades,
            'ultimo' => count($actividades),          
        );
        return view('Mod04_Materiales.AutorizarSolicitudes', $param);
    } else {
         return redirect()->route('auth/login');
    }
    
}
public function DataSolicitudes(){
     $consulta = DB::table('SIZ_SolicitudesMP')
                    ->join('SIZ_MaterialesSolicitudes', 'SIZ_MaterialesSolicitudes.Id_Solicitud', '=', 'SIZ_SolicitudesMP.Id_Solicitud')
                    ->leftjoin('OHEM', 'OHEM.U_EmpGiro', '=', 'SIZ_SolicitudesMP.Usuario')
                    ->leftjoin('OUDP', 'OUDP.Code', '=', 'dept')
                    ->groupBy('SIZ_SolicitudesMP.Id_Solicitud', 'SIZ_SolicitudesMP.FechaCreacion', 'SIZ_SolicitudesMP.Usuario', 'SIZ_SolicitudesMP.Status', 'firstName', 'lastName', 'dept', 'Name')
                    ->select('SIZ_SolicitudesMP.Id_Solicitud', 'SIZ_SolicitudesMP.FechaCreacion', 
                    'SIZ_SolicitudesMP.Usuario', 'SIZ_SolicitudesMP.Status', 'OHEM.firstName',
                     'OHEM.lastName', 'OHEM.dept', 'OUDP.Name as depto')
                    ->whereIn('SIZ_SolicitudesMP.Status', ['Pendiente', 'Regresada', 'En Proceso'])
                    ->whereIn('SIZ_MaterialesSolicitudes.EstatusLinea', ['S', 'P', 'N']);
     //$consulta = collect($consulta);
            return Datatables::of($consulta)             
                 ->addColumn('folio', function ($item) {                     
                       return  '<a href="2 PICKING ARTICULOS/solicitud/'.$item->Id_Solicitud.'"><i class="fa fa-hand-o-right"></i> '.$item->Id_Solicitud.'</a>';           
                    }
                    )
                    ->addColumn('user_name', function ($item) {
                       return  $item->firstName.' '.$item->lastName;           
                    }
                    )
                    ->addColumn('area', function ($item) {                      
                       return  $item->depto;
                    }
                    )
                    ->addColumn('statusbadge', function ($item) {
                      if ($item->Status == 'Pendiente') {
                          return '<a href="2 PICKING ARTICULOS/solicitud/'.$item->Id_Solicitud.'"><span class="badge badge-warning" style="background:#FFC107">'.$item->Status.'</span></a>';
                      } else {
                            return '<a href="2 PICKING ARTICULOS/solicitud/'.$item->Id_Solicitud.'"><span class="badge badge-primary" style="background:#007BFF">'.$item->Status.'</span></a>';
                      }                                                                    
                    }
                    )
                ->make(true);
}
public function DataTraslados(){
     $consulta = DB::table('SIZ_SolicitudesMP')
                    ->join('SIZ_MaterialesSolicitudes', 'SIZ_MaterialesSolicitudes.Id_Solicitud', '=', 'SIZ_SolicitudesMP.Id_Solicitud')
                    ->leftjoin('OHEM', 'OHEM.U_EmpGiro', '=', 'SIZ_SolicitudesMP.Usuario')
                    ->leftjoin('OUDP', 'OUDP.Code', '=', 'dept')
                    ->groupBy('SIZ_SolicitudesMP.Id_Solicitud', 'SIZ_SolicitudesMP.FechaCreacion', 'SIZ_SolicitudesMP.Usuario', 'SIZ_SolicitudesMP.Status', 'firstName', 'lastName', 'dept', 'Name')
                    ->select('SIZ_SolicitudesMP.Id_Solicitud', 'SIZ_SolicitudesMP.FechaCreacion', 
                    'SIZ_SolicitudesMP.Usuario', 'SIZ_SolicitudesMP.Status', 'OHEM.firstName',
                     'OHEM.lastName', 'OHEM.dept', 'OUDP.Name as depto')
                    ->where('SIZ_MaterialesSolicitudes.Cant_Pendiente', '>', 0)
                    ->where('SIZ_SolicitudesMP.Status', 'Traslado');
     //$consulta = collect($consulta);
            return Datatables::of($consulta)             
                 ->addColumn('folio', function ($item) {                     
                       return  '<a href="TRASLADOS/solicitud/'.$item->Id_Solicitud.'"><i class="fa fa-hand-o-right"></i> '.$item->Id_Solicitud.'</a>';           
                    }
                    )
                    ->addColumn('user_name', function ($item) {
                       return  $item->firstName.' '.$item->lastName;           
                    }
                    )
                    ->addColumn('area', function ($item) {                      
                       return  $item->depto;
                    }
                    )
                   
                ->make(true);
}
public function DataSolicitudes_Auht(){
     $consulta = DB::table('SIZ_SolicitudesMP')
                    ->join('SIZ_MaterialesSolicitudes', 'SIZ_MaterialesSolicitudes.Id_Solicitud', '=', 'SIZ_SolicitudesMP.Id_Solicitud')
                    ->leftjoin('OHEM', 'OHEM.U_EmpGiro', '=', 'SIZ_SolicitudesMP.Usuario')
                    ->leftjoin('OUDP', 'OUDP.Code', '=', 'dept')
                    ->groupBy('SIZ_SolicitudesMP.Id_Solicitud', 'SIZ_SolicitudesMP.FechaCreacion', 'SIZ_SolicitudesMP.Usuario', 'SIZ_SolicitudesMP.Status', 'firstName', 'lastName', 'dept', 'Name')
                    ->select('SIZ_SolicitudesMP.Id_Solicitud', 'SIZ_SolicitudesMP.FechaCreacion', 
                    'SIZ_SolicitudesMP.Usuario', 'SIZ_SolicitudesMP.Status', 'OHEM.firstName',
                     'OHEM.lastName', 'OHEM.dept', 'OUDP.Name as depto')
                    ->where('SIZ_SolicitudesMP.Status', 'Autorizacion');
     //$consulta = collect($consulta);
            return Datatables::of($consulta)             
                 ->addColumn('folio', function ($item) {                     
                       return  '<a href="AUTORIZACION/solicitud/'.$item->Id_Solicitud.'"><i class="fa fa-hand-o-right"></i> '.$item->Id_Solicitud.'</a>';           
                    }
                    )
                    ->addColumn('user_name', function ($item) {
                       return  $item->firstName.' '.$item->lastName;           
                    }
                    )
                    ->addColumn('area', function ($item) {                      
                       return  $item->depto;
                    }
                    )                                     
                    
                ->make(true);
}
  public function ShowArticulosWH(Request $request)
    {
        $consulta= DB::select('
        SELECT OITM.ItemCode, ItemName, InvntryUom AS UM, ALMACENES.Existencia FROM OITM
        LEFT JOIN 
        (SELECT ItemCode, SUM(CASE WHEN WhsCode = \'APG-PA\' OR WhsCode = \'AMP-ST\'  THEN OnHand ELSE 0 END) AS Existencia
        FROM dbo.OITW
        GROUP BY ItemCode) AS ALMACENES ON OITM.ItemCode = ALMACENES.ItemCode
        WHERE PrchseItem = \'Y\' AND InvntItem = \'Y\' AND U_TipoMat = \'MP\'
        ');
               $columns = array(
                ["data" => "ItemCode", "name" => "Código"],
                ["data" => "ItemName", "name" => "Descripción"],
                ["data" => "UM", "name" => "UM"],            
                ["data" => "Existencia", "name" => "Existencia", "defaultContent" => "0.00"],            
            );          

            return response()->json(array('data' => $consulta, 'columns' => $columns));
    }
public function saveArt(Request $request){   
    
            DB::beginTransaction();
        $err = false;
        $id = 0;
        $arts = $request->get('arts');
        $usercomment = mb_strtoupper($request->get('comentario'));
                $dt = new \DateTime();
                $id = DB::table('SIZ_SolicitudesMP')->insertGetId(
                    ['FechaCreacion' => $dt, 'Usuario' => Auth::id(), 'Status' => 'Autorizacion', 
                    'ComentarioUsuario' => $usercomment]
                );
                
                foreach ($arts as $art) {
                    DB::table('SIZ_MaterialesSolicitudes')->insert(
                        ['Id_Solicitud' => $id, 'ItemCode' => $art['pKey'], 
                        'Cant_Requerida' => $art['cant'], 'Destino' => $art['destino'], 
                        'Cant_Autorizada' =>  $art['cant'], 'Cant_Pendiente' =>  $art['cant'], 
                        'Cant_PendienteA' =>  $art['cant'],
                        'EstatusLinea' => 'S', 'Cant_ASurtir_Origen_A' => 0, 'Cant_ASurtir_Origen_B' => 0]
                    );
                }
                if (!($id > 0) || is_null($arts) || is_null($id)) {
                    $err =true;
                }
        
        if ($err) {
            DB::rollBack();       
            return 'Error: No se guardo la solicitud, favor de notificar a Sistemas';
        }else{
                DB::commit();
                $N_Emp = User::where('position', 4)->where('dept', Auth::user()->dept)->first();
                if (!is_null($N_Emp) && count($N_Emp)>0) {
                    $correo = utf8_encode($N_Emp->email . '@zarkin.com');
                    if (strlen($correo) > 10) {
                        Mail::send('Emails.SolicitudMP', [
                            'arts' => $arts, 'id' => $id, 'comentario' => $usercomment
                        ], function ($msj) use ($correo, $id) {
                            $msj->subject('SIZ Solicitud de Material #'.$id); //ASUNTO DEL CORREO
                            $msj->to($correo); //Correo del destinatario
                        });
                    } 
                }
                $Num_Nominas = DB::select(DB::raw("SELECT No_Nomina FROM Siz_Email WHERE SolicitudesMP = '1' OR SolicitudesMP = '2' "));
                foreach ($Num_Nominas as $Num_Nomina) {
                    $user = User::find($Num_Nomina->No_Nomina);
                    $correo = utf8_encode($user['email'] . '@zarkin.com');
                    if (strlen($correo) > 10) {
                        Mail::send('Emails.SolicitudMP', [
                            'arts' => $arts, 'id' => $id, 'comentario' => $usercomment
                                        ], function ($msj) use ($correo, $id) {
                            $msj->subject('SIZ Solicitud de Material #'.$id); //ASUNTO DEL CORREO
                            $msj->to($correo); //Correo del destinatario
                        });
                    }
                }
                return 'Mensaje: Tu Solicitud ha sido enviada (#'.$id.')';
        }
        DB::rollBack();       
        return 'Error: No se guardo la solicitud, favor de notificar a Sistemas';
    
}
 
public function ShowDetalleSolicitud($id){
    if (Auth::check()) {
    $user = Auth::user();
    $actividades = $user->getTareas();  
      // $solicitudes = DB::table('SIZ_SolicitudesMP');
    $articulos = DB::select('select mat.Id, mat.ItemCode, OITM.InvntryUom as UM, OITM.ItemName, mat.Destino, 
                    mat.Cant_Requerida, mat.Cant_Autorizada, mat.Cant_Pendiente, mat.Cant_PendienteA, mat.Cant_ASurtir_Origen_A, mat.Cant_ASurtir_Origen_B,
                     ALMACENES.APGPA, ALMACENES.AMPST, (APGPA + AMPST) AS Disponible, mat.EstatusLinea  from SIZ_MaterialesSolicitudes mat
                    LEFT JOIN OITM on OITM.ItemCode = mat.ItemCode
                    LEFT JOIN 
                    (SELECT ItemCode, SUM(CASE WHEN WhsCode = \'APG-PA\'  THEN OnHand ELSE 0 END) AS APGPA,
					SUM(CASE WHEN WhsCode = \'AMP-ST\'  THEN OnHand ELSE 0 END) AS AMPST
                    FROM dbo.OITW
                    GROUP BY ItemCode) AS ALMACENES ON OITM.ItemCode = ALMACENES.ItemCode
                    WHERE Id_Solicitud = ? AND Cant_PendienteA > 0', [$id]);
                    $step = DB::table('SIZ_SolicitudesMP')->where('Id_Solicitud', $id)->first();
                    if ($step->Status != 'Autorizacion') {
                         self::asignaAlmacenesOrigen($articulos);                    

                        $articulos = DB::select('select mat.Id, mat.ItemCode, OITM.InvntryUom as UM, OITM.ItemName, mat.Destino, 
                                        mat.Cant_Requerida, mat.Cant_Autorizada, mat.Cant_Pendiente, mat.Cant_PendienteA, mat.Cant_ASurtir_Origen_A, mat.Cant_ASurtir_Origen_B,
                                        ALMACENES.APGPA, ALMACENES.AMPST, (APGPA + AMPST) AS Disponible, 
                                        mat.EstatusLinea from SIZ_MaterialesSolicitudes mat
                                        LEFT JOIN OITM on OITM.ItemCode = mat.ItemCode
                                        LEFT JOIN 
                                        (SELECT ItemCode, SUM(CASE WHEN WhsCode = \'APG-PA\'  THEN OnHand ELSE 0 END) AS APGPA,
                                        SUM(CASE WHEN WhsCode = \'AMP-ST\'  THEN OnHand ELSE 0 END) AS AMPST
                                        FROM dbo.OITW
                                        GROUP BY ItemCode) AS ALMACENES ON OITM.ItemCode = ALMACENES.ItemCode
                                        WHERE Id_Solicitud = ?', [$id]);
                    }
    
    //$step = DB::table('SIZ_SolicitudesMP')->where('Id_Solicitud', $id)->value('Status');
    if ($step->Status == 'Autorizacion') {
        $articulos_validos = array_where($articulos, function ($key,$item) {
            return ($item->EstatusLinea == 'S' || $item->EstatusLinea == 'N');
        });
       $articulos_novalidos = array_where($articulos, function ($key,$item) {
                            return $item->EstatusLinea == 'A';});   
    } else {    //para Picking
        
        $articulos_validos = array_where($articulos, function ($key,$item) {
            return $item->EstatusLinea == 'S' || $item->EstatusLinea == 'P';
        });
       $articulos_novalidos = array_where($articulos, function ($key,$item) {
                            return $item->EstatusLinea == 'N';});   
    }
        
    if (count($articulos_novalidos) > 0) {
        Session::flash('solicitud_err','Esta Solicitud tiene artículos que no se surtirán (fueron quitados o no hay material disponible)');
    }

    $param = array(        
        'actividades' => $actividades,
        'ultimo' => count($actividades),    
        'id' => $id,
        'articulos_validos' => $articulos_validos,
        'articulos_novalidos' => $articulos_novalidos,
        'comentario' => $step->ComentarioUsuario
    );
    if ($step->Status == 'Autorizacion') {
        return view('Mod04_Materiales.Autorizacion', $param);
    } else {
        return view('Mod04_Materiales.Picking', $param);
    }
          
    } else {
        return redirect()->route('auth/login');
    }
}
public function ShowDetalleTraslado($id){ 
    if (Auth::check()) {
    $user = Auth::user();
    $actividades = $user->getTareas();  
      // $solicitudes = DB::table('SIZ_SolicitudesMP');                

    $articulos = DB::select('select mat.Id, mat.ItemCode, OITM.InvntryUom as UM, OITM.ItemName, mat.Destino, 
                    mat.Cant_Requerida, mat.Cant_Autorizada, mat.Cant_Pendiente, mat.Cant_PendienteA, mat.Cant_ASurtir_Origen_A, mat.Cant_ASurtir_Origen_B,
                     ALMACENES.APGPA, ALMACENES.AMPST, (APGPA + AMPST) AS Disponible, 
                     CASE WHEN ((APGPA + AMPST)  < mat.Cant_Requerida) and mat.EstatusLinea = \'S\' THEN \'N\' ELSE mat.EstatusLinea END AS EstatusLinea
                      from SIZ_MaterialesSolicitudes mat
                    LEFT JOIN OITM on OITM.ItemCode = mat.ItemCode
                    LEFT JOIN 
                    (SELECT ItemCode, SUM(CASE WHEN WhsCode = \'APG-PA\'  THEN OnHand ELSE 0 END) AS APGPA,
					SUM(CASE WHEN WhsCode = \'AMP-ST\'  THEN OnHand ELSE 0 END) AS AMPST
                    FROM dbo.OITW
                    GROUP BY ItemCode) AS ALMACENES ON OITM.ItemCode = ALMACENES.ItemCode
                    WHERE Id_Solicitud = ? AND mat.Cant_Pendiente > 0', [$id]);

                    $articulos_validos = array_where($articulos, function ($key,$item) {
                        return ($item->EstatusLinea == 'S') && (($item->Cant_ASurtir_Origen_A + $item->Cant_ASurtir_Origen_B) > 0);
                    });
                   $articulos_novalidos = array_where($articulos, function ($key,$item) {
                                        return $item->EstatusLinea == 'N';});     
                    
                    $pdf_solicitud = DB::table('OWTR')->where('FolioNum', $id)->get();
    if (count($articulos_novalidos) > 0) {
        Session::flash('solicitud_err','Esta Solicitud tiene artículos que no se surtirán (fueron quitados o no hay material disponible)');
    }
    $param = array(        
        'actividades' => $actividades,
        'ultimo' => count($actividades),    
        'id' => $id,
        'articulos_validos' => $articulos_validos,
        'articulos_novalidos' => $articulos_novalidos,
        'pdf_solicitud' => $pdf_solicitud
    );
        Session::put('transfer1', 0);
        Session::put('transfer2', 0);
        return view('Mod04_Materiales.Traslado', $param);    
          
    } else {
        return redirect()->route('auth/login');
    }
}
public function asignaAlmacenesOrigen($articulos){
    foreach ($articulos as $art) {
        if (($art->Cant_ASurtir_Origen_A + $art->Cant_ASurtir_Origen_B) == 0) {
            $Cant = $art->Cant_Pendiente; 
                $P = $art->Cant_Pendiente; 
                $A = $art->APGPA; 
                $B = $art->AMPST;
                $ALMA = 0;      
                $ALMB = 0;      
                if(($A + $B) >= $Cant){
                    if ($A >= $B) {
                        if ($A >= $P) {
                            $ALMA = $P;
                            $ALMB = 0;
                        
                        } else {
                            $P = $P - $A;
                            $ALMA = $Cant - $P;
                            if ($P <= $B) {
                            $ALMB = $P;
                            
                            }
                        }                
                    } else {
                        if ($B >= $P) {
                            $ALMA = 0;
                            $ALMB = $P;
                        
                        } else { 
                            $P = $P - $B;
                            $ALMB = $Cant - $P;
                            if ($P <= $A) {
                                $ALMA = $P;

                            }
                        }
                        
                    }
                    self::updateAlmacenesArticulo([$ALMA, $ALMB, 'S', $art->Id]);               
                }else{
                    if ($art->EstatusLinea == 'S') {
                        self::updateAlmacenesArticulo([0, 0, 'N', $art->Id]);
                    } else {
                        self::updateAlmacenesArticulo([0, 0, $art->EstatusLinea, $art->Id]);
                    }
                }
        }        
    }
}
public function updateAlmacenesArticulo($parametros){
    DB::update('UPDATE SIZ_MaterialesSolicitudes SET Cant_ASurtir_Origen_A = ? , Cant_ASurtir_Origen_B = ?, EstatusLinea = ? WHERE Id = ?', $parametros);
}
public function removeArticuloSolicitud(){
 // dd(strpos(Input::get('reason'), 'Material') !== false);
// dd(Input::all());
    if (Auth::check()) {
        if (strpos(Input::get('reason'), 'Material') !== false) {
            DB::update('UPDATE SIZ_MaterialesSolicitudes SET EstatusLinea = ? , Razon_Picking = ?
            WHERE Id = ?', ['C', Input::get('reason'), Input::get('articulo')]);
        } else {
            DB::update('UPDATE SIZ_MaterialesSolicitudes SET EstatusLinea = ? , Razon_Picking = ?
            WHERE Id = ?', ['N', Input::get('reason'), Input::get('articulo')]);
        }
        return redirect()->back();
    } else {
        return redirect()->route('auth/login');
    }
}
public function removeArticuloNoAutorizado(){
    if (Auth::check()) {
        DB::update('UPDATE SIZ_MaterialesSolicitudes SET EstatusLinea = ? , Razon_NoAutorizado = ? WHERE Id = ?', ['A', Input::get('reason'), Input::get('articulo')]);
        return redirect()->back();
    } else {
        return redirect()->route('auth/login');
    }
}
public function editArticulo(){
    if (Auth::check()) {
        DB::update('UPDATE SIZ_MaterialesSolicitudes SET Cant_Autorizada = ? , Cant_Pendiente = ?, Cant_PendienteA = ?, Razon_AutorizaCantMenor = ? WHERE Id = ?', [Input::get('canta'), Input::get('canta'), Input::get('canta'), Input::get('reason'), Input::get('articulo')]);
        return redirect()->back();
    } else {
        return redirect()->route('auth/login');
    }
}
public function editArticuloPicking(){
  
    if (Auth::check()) {

    if (Input::get('pendiente') < (Input::get('canta')+Input::get('cantb'))) {
        Session::flash('error', 'La Cantidad a Surtir debe ser igual o menor a '.Input::get('pendiente'));
        return redirect()->back();
    }

    if (Input::get('canta') + Input::get('cantb') == Input::get('pendiente')) {
        DB::update('UPDATE SIZ_MaterialesSolicitudes SET  Cant_ASurtir_Origen_A = ?, Cant_ASurtir_Origen_B = ?, Razon_PickingCantMenor = ? WHERE Id = ?', [Input::get('canta'), Input::get('cantb'), '', Input::get('articulo')]);
    } elseif(Input::get('canta') + Input::get('cantb') < Input::get('pendiente')){
        DB::update('UPDATE SIZ_MaterialesSolicitudes SET  Cant_ASurtir_Origen_A = ?, Cant_ASurtir_Origen_B = ?, Razon_PickingCantMenor = ? WHERE Id = ?', [Input::get('canta'), Input::get('cantb'), Input::get('reason'), Input::get('articulo')]);
        
        if (strpos(Input::get('reason'), 'existencia') !== false) {
           
            $art = DB::table('SIZ_MaterialesSolicitudes')
            ->join('OITM', 'OITM.ItemCode', '=' , 'SIZ_MaterialesSolicitudes.ItemCode')
            ->select('SIZ_MaterialesSolicitudes.*', 'OITM.ItemName')        
            ->where('Id', Input::get('articulo'))->first();
        
            $Num_Nominas = DB::select(DB::raw("SELECT No_Nomina FROM Siz_Email WHERE SolicitudesErrExistencias = '1' "));
                    foreach ($Num_Nominas as $Num_Nomina) {
                        $user = User::find($Num_Nomina->No_Nomina);
                        $correo = utf8_encode($user['email'] . '@zarkin.com');
                        if (strlen($correo) > 10) {
                            Mail::send('Emails.Err_existencias', [
                                'art' => $art
                                            ], function ($msj) use ($correo, $art) {
                                $msj->subject('SIZ Error de Existencias ('.$art->ItemCode.')'); //ASUNTO DEL CORREO
                                $msj->to($correo); //Correo del destinatario
                            });
                        }
                    }
        }
        
    } else{
          Session::flash('error', 'No se pudo actualizar...');
    }
     return redirect()->back();
    } else {
        return redirect()->route('auth/login');
    }
}
public function returnArticuloSolicitud($id){
    if (Auth::check()) {   
        DB::update('UPDATE SIZ_MaterialesSolicitudes SET EstatusLinea = ? , Razon_Picking = ? , Razon_NoAutorizado = ? WHERE Id = ?', ['S', '', '', $id]);
        return redirect()->back();
    } else {
        return redirect()->route('auth/login');
    }
}
public function SolicitudPDF($id){
    if (Auth::check()) {   
    
        DB::update('UPDATE SIZ_SolicitudesMP SET Status = ? WHERE Id_Solicitud = ?', ['En Proceso', $id]);
        $articulos = DB::select('select mat.Id, mat.ItemCode, OITM.InvntryUom as UM, OITM.ItemName, mat.Destino,
          mat.Cant_Requerida, mat.Cant_Autorizada, mat.Cant_Pendiente, mat.Cant_PendienteA, (mat.Cant_ASurtir_Origen_A + mat.Cant_ASurtir_Origen_B) AS Cant_Surtir,
         ALMACENES.APGPA, ALMACENES.AMPST, (APGPA + AMPST) AS Disponible from SIZ_MaterialesSolicitudes mat
                    LEFT JOIN OITM on OITM.ItemCode = mat.ItemCode
                    LEFT JOIN 
                    (SELECT ItemCode, SUM(CASE WHEN WhsCode = \'APG-PA\'  THEN OnHand ELSE 0 END) AS APGPA,
					SUM(CASE WHEN WhsCode = \'AMP-ST\'  THEN OnHand ELSE 0 END) AS AMPST
                    FROM dbo.OITW
                    GROUP BY ItemCode) AS ALMACENES ON OITM.ItemCode = ALMACENES.ItemCode
                    WHERE Id_Solicitud = ? AND mat.EstatusLinea = \'S\'', [$id]);       
  
       //haz el PDF
        $pdf = \PDF::loadView('Mod04_Materiales.SolicitudPDF', compact('articulos', 'id'));
        $pdf->setPaper('Letter','landscape')->setOptions(['isPhpEnabled'=>true]);             
        return $pdf->stream('Siz_Picking_'.$id . ' - ' .date("d/m/Y") . '.Pdf');
    } else {
        return redirect()->route('auth/login');
    }
}
public function Solicitud_A_Traslados($id){
 if (Auth::check()) {
        $cantLineas = DB::table('SIZ_MaterialesSolicitudes')
        ->where('EstatusLinea', 'S')
        ->where('Id_Solicitud', $id)
        ->count();
        $cantcheckLineas = DB::table('SIZ_MaterialesSolicitudes')
        ->where('Id_Solicitud', $id)
        ->where('Cant_PendienteA', '>=', DB::raw('([Cant_ASurtir_Origen_A] + [Cant_ASurtir_Origen_B])'))
        ->where('EstatusLinea', 'S')
        ->count();
      
        if ($cantLineas == $cantcheckLineas ) {
            DB::update('UPDATE SIZ_SolicitudesMP SET Status = ? WHERE Id_Solicitud = ?', ['Traslado', $id]);
            Session::flash('mensaje','Solicitud #'.$id.' Enviada a Traslados');
            return redirect()->action('Mod04_MaterialesController@pickingArticulos');
        } else {
            Session::flash('error','Hay cantidades a surtir incorrectas');
            return redirect()->back();
        }
        
       
       
    } else {
        return redirect()->route('auth/login');
    }
}
public function Solicitud_A_Picking($id){
 if (Auth::check()) {   
    
        DB::update('UPDATE SIZ_SolicitudesMP SET Status = ? WHERE Id_Solicitud = ?', 
        ['Pendiente', $id]);
        Session::flash('mensaje','La Solicitud  #'.$id.' se ha enviado a Picking Almacén');
        //aquicorreo
        $nomSolicitante = DB::table('SIZ_SolicitudesMP')
                            ->where('Id_Solicitud', $id)
                            ->value('Usuario');
        $Num_Nominas = DB::table('Siz_Email')
                        ->whereIn('SolicitudesMP', [1,3])
                        ->lists('No_Nomina');
                     
        if (!in_array($nomSolicitante, $Num_Nominas)) {
            $Num_Nominas[] = $nomSolicitante;
        }
       $arts = DB::table('SIZ_MaterialesSolicitudes')
        ->join('OITM', 'OITM.ItemCode', '=' , 'SIZ_MaterialesSolicitudes.ItemCode')
        ->select('SIZ_MaterialesSolicitudes.*', 'OITM.ItemName')        
        ->where('Id_Solicitud', $id)->get(); 
        
        foreach ($Num_Nominas as $Num_Nomina) {
            $user = User::find($Num_Nomina);
            $correo = utf8_encode($user['email'] . '@zarkin.com');
            if (strlen($correo) > 10) {
                Mail::send('Emails.AutorizacionMP', [
                    'arts' => $arts, 'id' =>$id
                ], function ($msj) use ($correo, $id) {
                    $msj->subject('SIZ Autorización Material #'.$id); //ASUNTO DEL CORREO
                    $msj->to($correo); //Correo del destinatario
                });
            }
        }
        return redirect()->action('Mod04_MaterialesController@AutorizacionSolicitudes');
    } else {
        return redirect()->route('auth/login');
    }
}
public function Solicitud_A_PickingTraslados($id){
 if (Auth::check()) {   
    
        DB::update('UPDATE SIZ_SolicitudesMP SET Status = ? WHERE Id_Solicitud = ?', 
        ['Pendiente', $id]);
        Session::flash('mensaje','La Solicitud  #'.$id.' se ha enviado a Picking Almacén');
        return redirect()->action('Mod04_MaterialesController@TrasladosArticulos');
       
    } else {
        return redirect()->route('auth/login');
    }
}
public function DM_Articulo($ItemCode){
    if (Auth::check()) {
        $param = self::getParam_DM_Articulos($ItemCode);
        
        $param['privilegioTarea'] = 'disabled';
        $param['oculto'] = true;

        
        return view('Mod04_Materiales.DM_Articulos', $param);
    } else {
        return redirect()->route('auth/login');
    }
}

public function HacerTraslados($id){
   //dd(Session::get('transfer1'));
    if (Auth::check()) {
        $rates = DB::table('ORTT')->where('RateDate', date('d-m-Y'))->get();
        if (count($rates) >= 3) {
            //PERSONA QUE SOLICITA
            $solicitante = DB::table('SIZ_SolicitudesMP')       
                ->leftjoin('OHEM', 'OHEM.U_EmpGiro', '=', 'SIZ_SolicitudesMP.Usuario')        
                ->select('OHEM.firstName','OHEM.lastName')
                ->where('SIZ_SolicitudesMP.Id_Solicitud', $id)->first();
            $nombreCompleto = $solicitante->firstName.' '.$solicitante->lastName;
            //articulos validos para transferencia
            $articulos = DB::select('select mat.Id, mat.ItemCode, mat.Destino, mat.Cant_Pendiente, 
                mat.Cant_ASurtir_Origen_A as CA, mat.Cant_ASurtir_Origen_B AS CB, mat.Cant_PendienteA,
                ALMACENES.APGPA, ALMACENES.AMPST from SIZ_MaterialesSolicitudes mat                                
                LEFT JOIN(SELECT ItemCode, SUM(CASE WHEN WhsCode = \'APG-PA\'  THEN OnHand ELSE 0 END) AS APGPA,
                SUM(CASE WHEN WhsCode = \'AMP-ST\'  THEN OnHand ELSE 0 END) AS AMPST
                FROM dbo.OITW
                GROUP BY ItemCode) AS ALMACENES ON mat.ItemCode = ALMACENES.ItemCode
                WHERE Id_Solicitud = ? AND mat.EstatusLinea = \'S\'
                AND mat.Cant_ASurtir_Origen_A <= APGPA AND mat.Cant_ASurtir_Origen_B <= AMPST ', [$id]); 

            $primer_origen = array_where($articulos, function ($key,$item) {
                return $item->CA > 0;
            });

            $stat1 = 0;
            $stat2 = 0;
            $transfer1 = 0;
            $transfer2 = 0;
            $total1 = 0;
            $total2 = 0;
            $t1 = 0;
            $t2 = 0;
            $info1 = 0;
            $info2 = 0;
            if (count($primer_origen) > 0) {
                $data =  array(
                    'id_solicitud' => $id,
                    'pricelist' => '10',
                    'almacen_origen' => 'APG-PA',
                    'items' => $primer_origen,
                    'nombre_completo' => $nombreCompleto
                );
                if (Session::has('transfer1')) {   
                    if (Session::get('transfer1') > 0) {
                        $t1 = Session::get('transfer1');
                    } else {
                        $t1 = SAP::Transfer($data);
                    }
                } else {
                    $t1 = SAP::Transfer($data);
                }
                
                if (is_numeric($t1) && $t1 > 0 ) {
                    Session::put('transfer1', $t1);
                    Session::flash('mensaje', 'Transferencia '.$t1.' realizada.');
                    
                }else{
                    $stat1 = strlen($t1);
                    $transfer1 = array();
                }
               
                }else{
                    if (false) {
                       if (Session::has('transfer1') && (Session::get('transfer1') > 0)) {                        
                        $t1 = Session::get('transfer1');
                            if (is_numeric($t1) && $t1 > 0 ) {
                                Session::put('transfer1', $t1);
                                $transfer1 = DB::select('select LineNum +1 As lineNum, ItemCode, Dscription, unitMsr, WhsCode,
                                Quantity, Price, Currency, LineTotal  from WTR1 where DocEntry = ? ', [$t1]);                                
                                    $total1 = DB::select('select sum(LineTotal) as Total from WTR1 where DocEntry = ? ', [$t1]);
                                    $info1 = DB::select('select Printed, Comments  from OWTR where DocEntry = ? ', [$t1]);
                            }else{
                                $stat1 = strlen($t1);
                                $transfer1 = array();
                            }
                        }
                    }
                   
                }
                $articulos = DB::select('select mat.Id, mat.ItemCode, mat.Destino, mat.Cant_Pendiente, 
                mat.Cant_ASurtir_Origen_A as CA, mat.Cant_ASurtir_Origen_B AS CB, mat.Cant_PendienteA,
                ALMACENES.APGPA, ALMACENES.AMPST from SIZ_MaterialesSolicitudes mat                                
                LEFT JOIN(SELECT ItemCode, SUM(CASE WHEN WhsCode = \'APG-PA\'  THEN OnHand ELSE 0 END) AS APGPA,
                SUM(CASE WHEN WhsCode = \'AMP-ST\'  THEN OnHand ELSE 0 END) AS AMPST
                FROM dbo.OITW
                GROUP BY ItemCode) AS ALMACENES ON mat.ItemCode = ALMACENES.ItemCode
                WHERE Id_Solicitud = ? AND mat.EstatusLinea = \'S\'
                AND mat.Cant_ASurtir_Origen_A <= APGPA AND mat.Cant_ASurtir_Origen_B <= AMPST ', [$id]); 

           
            $segundo_origen = array_where($articulos, function ($key,$item) {
                return $item->CB > 0;
            });
            
                if (count($segundo_origen) > 0) {
                    $data =  array(
                        'id_solicitud' => $id,
                        'pricelist' => '10',
                        'almacen_origen' => 'AMP-ST',
                        'items' => $segundo_origen,
                        'nombre_completo' => $nombreCompleto
                    );
                        
                    if (Session::has('transfer2')) {                        
                        if (Session::get('transfer2') > 0) {
                            $t2 = Session::get('transfer2');
                        } else {
                            $t2 = SAP::Transfer($data);
                        }
                        } else {
                            $t2 = SAP::Transfer($data);
                           
                    }

                    if (is_numeric($t2) && $t2 > 0 ) {
                        
                        Session::put('transfer2', $t2);
                        Session::flash('mensaje2', 'Transferencia '.$t2.' realizada.');
                    }else{
                        $stat2 = strlen($t2);
                        $transfer2 = array();
                    }
            }else{
                if (false) {
                    if (Session::has('transfer2') && (Session::get('transfer2') > 0)) {                        
                        $t2 = Session::get('transfer2');
                        if (is_numeric($t2) && $t2 > 0 ) {
                           // Session::put('transfer2', $t2);
                            $transfer2 = DB::select('select LineNum +1 As lineNum, ItemCode, Dscription, unitMsr, WhsCode,
                            Quantity, Price, Currency, LineTotal  from WTR1 where DocEntry = ? ', [$t2]);                                
                                $total2 = DB::select('select sum(LineTotal) as Total from WTR1 where DocEntry = ? ', [$t2]);
                                $info2 = DB::select('select Printed, Comments  from OWTR where DocEntry = ? ', [$t2]);
                        }else{
                            $stat2 = strlen($t2);
                            $transfer2 = array();
                        }
                    }
                }
            }
            if ($stat1 > 0 && $stat2 > 0) {
               Session::flash('error', 'Transferencia APG-PA '.$t1);
               Session::flash('error', 'Transferencia AMP-ST '.$t2); 
                return redirect()->back();
            }

            $filas = DB::table('SIZ_MaterialesSolicitudes')
            ->where('Id_Solicitud', $id)
            ->whereIn('EstatusLinea', ['S', 'P'])
            ->count();            
            if ($filas > 0) {
                DB::table('SIZ_SolicitudesMP')
                ->where('Id_Solicitud', $id)
                ->update(['Status' => 'Regresada']);
            } elseif($filas == 0) {
                DB::table('SIZ_SolicitudesMP')
                ->where('Id_Solicitud', $id)
                ->update(['Status' => 'Cerrada', 'FechaFinalizada' => (new \DateTime('now'))->format('Y-m-d H:i:s')]);
            }
            return redirect()->action('Mod04_MaterialesController@ShowDetalleTraslado', [$id]);
           // return redirect()->route('home/TRASLADOS/solicitud', [$id]);
        } else {
            Session::flash('error', 'No estan capturados todos los "Tipos de Cambio" en SAP.');        
            return redirect()->back();
        }
    } else {
        return redirect()->route('auth/login');
    }
}
  public function getPdfTraslado($transfer){
    $transfer1 = DB::select('select LineNum +1 As lineNum, ItemCode, Dscription, unitMsr, WhsCode,
    Quantity, Price, Currency, LineTotal  from WTR1 where DocEntry = ? ', [$transfer]);                                
        $total1 = DB::select('select sum(LineTotal) as Total from WTR1 where DocEntry = ? ', [$transfer]);
        $info1 = DB::select('select FolioNum, Filler, Printed, Comments  from OWTR where DocEntry = ? ', [$transfer]);
        $comentario = DB::table('SIZ_SolicitudesMP')->where('Id_Solicitud', $info1[0]->FolioNum)->value('ComentarioUsuario');
        $pdf = \PDF::loadView('Mod04_Materiales.TrasladoPDF_SinPrecio', compact('info1', 'transfer1', 
         'total1',  'transfer', 'comentario'));
        $pdf->setPaper('Letter','landscape')->setOptions(['isPhpEnabled'=>true]);             
        return $pdf->stream('Siz_Traslado_'.$info1[0]->FolioNum . ' - ' .date("d/m/Y") . '.Pdf');
  }
    public function trasladoEntrega()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $actividades = $user->getTareas();
            $almacenesDestino = DB::table('SIZ_AlmacenesTransferencias')
                ->where('Dept', Auth::user()->dept)
                ->whereIn('TrasladoDeptos', ['D', 'OD'])->get();

            $param = array(
                'actividades' => $actividades,
                'ultimo' => count($actividades),
                'almacenOrigen' => Input::get('text_selTres'),
                'almacenesDestino' => $almacenesDestino,
            );
            return view('Mod04_Materiales.showListMateriales', $param);
        } else {
            return redirect()->route('auth/login');
        }
    }
    public function ShowArticulosWHTraslados(Request $request)
    {    
        //AND U_TipoMat = \'PT\' 
        $consulta = DB::select('
        SELECT  top 5 OITM.ItemCode, ItemName, InvntryUom AS UM, ALMACENES.Existencia FROM OITM
        LEFT JOIN 
        (SELECT ItemCode, SUM(CASE WHEN WhsCode = \''. $request->get('almacen'). '\'   THEN OnHand ELSE 0 END) AS Existencia
        FROM dbo.OITW 
        GROUP BY ItemCode) AS ALMACENES ON OITM.ItemCode = ALMACENES.ItemCode
        WHERE PrchseItem = \'Y\' AND InvntItem = \'Y\' 
        
        AND Existencia > 0
        ');
        $columns = array(
            ["data" => "ItemCode", "name" => "Código"],
            ["data" => "ItemName", "name" => "Descripción"],
            ["data" => "UM", "name" => "UM"],
            ["data" => "Existencia", "name" => "Existencia", "defaultContent" => "0.00"],
        );

        return response()->json(array('data' => $consulta, 'columns' => $columns));
    }
    public function saveTraslado(Request $request)
    {
        DB::beginTransaction();
        $err = false;
        $id = 0;
        $arts = $request->get('arts');
        $almacen_origen = $request->get('almacen');
        $usercomment = mb_strtoupper($request->get('comentario'));
        $dt = new \DateTime();
        $id = DB::table('SIZ_SolicitudesMP')->insertGetId(
            [
                'FechaCreacion' => $dt, 'Usuario' => Auth::id(), 'Status' => 'Pendiente',
                'ComentarioUsuario' => $usercomment, 'AlmacenOrigen' => $almacen_origen
            ]
        );

        foreach ($arts as $art) {
           
            DB::table('SIZ_MaterialesTraslados')->insert(
                [
                    'Id_Solicitud' => $id, 'ItemCode' => $art['pKey'],
                    'Cant_Requerida' => $art['cant'], 'Destino' => $art['destino'],
                    'Cant_Autorizada' =>  $art['cant'], 'Cant_Pendiente' =>  $art['cant'],
                    'Cant_PendienteA' =>  $art['cant'],
                    'EstatusLinea' => 'S', 'Cant_ASurtir_Origen_A' => $art['cant'], 'Cant_ASurtir_Origen_B' => 0
                ]
            );
        }
        if (!($id > 0) || is_null($arts) || is_null($id)) {
            $err = true;
        }

        if ($err) {
            DB::rollBack();
            return 'Error: No se guardo la solicitud, favor de notificar a Sistemas';
        } else {
            DB::commit();
            $Num_Nominas = DB::select(DB::raw("SELECT No_Nomina FROM Siz_Email WHERE Traslados = '1' "));
            foreach ($Num_Nominas as $Num_Nomina) {
                $user = User::find($Num_Nomina->No_Nomina);
                $correo = utf8_encode($user['email'] . '@zarkin.com');
                if (strlen($correo) > 10) {
                    Mail::send('Emails.TrasladosDeptos', [
                        'arts' => $arts, 'id' => $id, 'comentario' => $usercomment, 'origen' => $almacen_origen
                    ], function ($msj) use ($correo, $id) {
                        $msj->subject('SIZ Traslado #' . $id); //ASUNTO DEL CORREO
                        $msj->to($correo); //Correo del destinatario
                    });
                }
            }
            return 'Mensaje: Tu Traslado ha sido enviado (#' . $id . ')';
        }
        DB::rollBack();
        return 'Error: No se guardo la solicitud, favor de notificar a Sistemas';
    }
    public function TrasladosDeptos(){
        if (Auth::check()) {
            $user = Auth::user();
            $actividades = $user->getTareas();

            $param = array(
                'actividades' => $actividades,
                'ultimo' => count($actividades),
            );
            return view('Mod04_Materiales.ShowTrasladosDeptos', $param);
        } else {
            return redirect()->route('auth/login');
        } 
    }
    public function DataTrasladosDeptos(){
        $consulta = DB::table('SIZ_SolicitudesMP')
            ->join('SIZ_MaterialesTraslados', 'SIZ_MaterialesTraslados.Id_Solicitud', '=', 'SIZ_SolicitudesMP.Id_Solicitud')
            ->leftjoin('OHEM', 'OHEM.U_EmpGiro', '=', 'SIZ_SolicitudesMP.Usuario')
            ->leftjoin('OUDP', 'OUDP.Code', '=', 'dept')
            ->groupBy('SIZ_SolicitudesMP.Id_Solicitud', 'SIZ_SolicitudesMP.FechaCreacion', 'SIZ_SolicitudesMP.Usuario', 'SIZ_SolicitudesMP.Status', 'firstName', 'lastName', 'dept', 'Name', 'SIZ_SolicitudesMP.AlmacenOrigen')
            ->select(
                'SIZ_SolicitudesMP.Id_Solicitud',
                'SIZ_SolicitudesMP.FechaCreacion',
                'SIZ_SolicitudesMP.Usuario',
                'SIZ_SolicitudesMP.Status',
                'SIZ_SolicitudesMP.AlmacenOrigen',
                'OHEM.firstName',
                'OHEM.lastName',
                'OHEM.dept',
                'OUDP.Name as depto'
            )
            ->whereNotNull('SIZ_SolicitudesMP.AlmacenOrigen')
            ->where('SIZ_MaterialesTraslados.Cant_Pendiente', '>', 0)
            ->where('SIZ_SolicitudesMP.Status', 'Pendiente');
       
        return Datatables::of($consulta)
            ->addColumn(
                'folio',
                function ($item) {
                    return  '<a href="TRASLADO RECEPCION/solicitud/' . $item->Id_Solicitud . '"><i class="fa fa-hand-o-right"></i> ' . $item->Id_Solicitud . '</a>';
                }
            )
            ->addColumn(
                'user_name',
                function ($item) {
                    return  $item->firstName . ' ' . $item->lastName;
                }
            )
            ->addColumn(
                'area',
                function ($item) {
                    return  $item->depto;
                }
            )

            ->make(true);
    }
    public function ShowDetalleTrasladoDeptos($id)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $actividades = $user->getTareas();
            $solicitud = DB::table('SIZ_SolicitudesMP')->where('Id_Solicitud', $id)->first();
                     
            $articulos = DB::select('select mat.Id, mat.ItemCode, OITM.InvntryUom as UM, OITM.ItemName, mat.Destino, 
                    mat.Cant_Requerida, mat.Cant_Autorizada, mat.Cant_Pendiente, mat.Cant_PendienteA, mat.Cant_ASurtir_Origen_A, 
                    ALMACENES.AlmacenOrigen, CASE WHEN mat.Cant_ASurtir_Origen_A > AlmacenOrigen THEN \'N\'  ELSE mat.EstatusLinea END as EstatusLinea  
                    from SIZ_MaterialesTraslados mat
                    LEFT JOIN OITM on OITM.ItemCode = mat.ItemCode
                    LEFT JOIN 
                    (SELECT ItemCode, SUM(CASE WHEN WhsCode = \''.$solicitud->AlmacenOrigen.'\' THEN OnHand ELSE 0 END) AS AlmacenOrigen
                    FROM dbo.OITW
                    GROUP BY ItemCode) AS ALMACENES ON OITM.ItemCode = ALMACENES.ItemCode
                    WHERE Id_Solicitud = ? AND Cant_PendienteA > 0', [$id]);
              
                $articulos_validos = array_where($articulos, function ($key, $item) {
                    return $item->EstatusLinea == 'S' || $item->EstatusLinea == 'P';
                });
                $articulos_novalidos = array_where($articulos, function ($key, $item) {
                    return $item->EstatusLinea == 'N';
                });

            if (count($articulos_novalidos) > 0) {
                Session::flash('solicitud_err', 'Esta Solicitud tiene artículos que no se surtirán (fueron quitados o no hay material disponible)');
            }

            $param = array(
                'actividades' => $actividades,
                'ultimo' => count($actividades),
                'id' => $id,
                'almacen_origen' => $solicitud->AlmacenOrigen,
                'articulos_validos' => $articulos_validos,
                'articulos_novalidos' => $articulos_novalidos,
                'comentario' => $solicitud->ComentarioUsuario
            );
            
                return view('Mod04_Materiales.TrasladosDeptos', $param);
            
        } else {
            return redirect()->route('auth/login');
        }
    }
    public function removeArticuloTrasladoDepto()
    {
        // dd(strpos(Input::get('reason'), 'Material') !== false);
        // dd(Input::all());
        if (Auth::check()) {
            if (strpos(Input::get('reason'), 'Material') !== false) {
                DB::update('UPDATE SIZ_MaterialesTraslados SET EstatusLinea = ? , Razon = ?
            WHERE Id = ?', ['C', Input::get('reason'), Input::get('articulo')]);
            } else {
                DB::update('UPDATE SIZ_MaterialesTraslados SET EstatusLinea = ? , Razon= ?
            WHERE Id = ?', ['N', Input::get('reason'), Input::get('articulo')]);
            }
            return redirect()->back();
        } else {
            return redirect()->route('auth/login');
        }
    }
}