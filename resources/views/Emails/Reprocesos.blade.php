<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Correo</title>
</head>

<h3 style="color:#14778E">Se esta llevando a cabo el siguiente Reproceso</h3>

<table border="1" cellpadding="0" cellspacing="0" width="90%" style="text-align:center;">

    <thead>
        <tr>
            <th>Usuario</th>
            <th>No.Orden</th>
            <th>Cantidad</th>
            <th bgcolor="#b3ffcc">Estacion de Origen</th>   
            <th bgcolor="#ff8080">Estacion de Destino</th>
            <th>Descripcion de la falla</th>
            <th>Recibido en Estación Destino</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{$Nom_User}}</td>
            <td>{{$orden}}</td>
            <td>{{$cant_r}}</td>
            <td bgcolor="#b3ffcc">{{$Est_act}}</td>
            <td bgcolor="#ff8080">{{$Est_ant}}</td>
            <td>{{$nota}}</td>
            <td>No</td>
        </tr>
    </tbody>
</table>
<h4 style="color:red">El usuario que hizo este movimiento debe entregar el producto a la estacion de Destino y verificar que se acepte</h4>
</html>