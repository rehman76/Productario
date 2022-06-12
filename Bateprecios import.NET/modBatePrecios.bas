Attribute VB_Name = "modBatePrecios"
'Declaramos la función que utilizaremos para descargar el archivo
Private Declare Function URLDownloadToFile Lib "urlmon" Alias "URLDownloadToFileA" (ByVal pCaller As Long, ByVal szURL As String, ByVal szFileName As String, ByVal dwReserved As Long, ByVal lpfnCB As Long) As Long


Dim cn As ADODB.Connection
Dim rs As ADODB.Recordset

Function insertArticulos(SKU, EAN, Categoria, Titulo, Descripcion, Estado, Proveedor, ListaPrecios, FechaVigencia, Cotizacion, Valor, IVA, ImpuestosInternos, Stock, ByRef mensaje)
    Set cn = New ADODB.Connection
    Set rs = New ADODB.Recordset
    Do
        cn.open funConexion()
        DoEvents
    Loop While Err <> 0
    sql = "SELECT COUNT(SKU) AS Cantidad FROM Articulos WHERE SKU = '" & Trim(SKU) & "'"
    Do
        rs.open sql, cn, 3, 3
        DoEvents
    Loop While Err <> 0
    mensaje = "Registro Actualizado: " & SKU & " - " & Trim(Descripcion)
    If rs("Cantidad") = 0 Then
        sql = "INSERT INTO Articulos (SKU, EAN, Categoria, Titulo, Descripcion, Estado) VALUES ('" & SKU & "', '" & EAN & "', " & Categoria & ", '" & Trim(Titulo) & "', '" & Trim(Descripcion) & "', '" & Estado & "')"
        Do
            cn.Execute sql
            DoEvents
        Loop While Err <> 0
        mensaje = "Registro Agregado: " & SKU & " - " & Titulo
    Else
        sql = "UPDATE Articulos SET Estado = '" & Estado & "' WHERE SKU = '" & SKU & "'"
        Do
            cn.Execute sql
            DoEvents
        Loop While Err <> 0
    End If
    sql = "INSERT INTO Articulos_Valor (SKU, Proveedor, ListaPrecios, FechaVigencia, Cotizacion, Valor, IVA, ImpuestosInternos, Stock) VALUES ('" & SKU & "', " & Proveedor & ", " & ListaPrecios & ", '" & Format(FechaVigencia, "yyyy/dd/mm hh:MM:ss") & "', " & Cotizacion & ", " & Valor & ", " & IVA & ", " & ImpuestosInternos & ", " & Stock & ")"
    Do
        cn.Execute sql
        DoEvents
    Loop While Err <> 0

'    sql = "INSERT INTO Articulos_Log (SKU, Fecha, Descripcion) VALUES ('" & SKU & "', '" & Format(Now(), "yyyy/mm/dd hh:MM:ss") & "', '" & Trim(Descripcion) & "')"
'    Do
'        cn.Execute sql
'        DoEvents
'    Loop While Err <> 0
    
    rs.Close
    Set rs = Nothing
    Set cn = Nothing
End Function

Function CotizacionBNA()
    Dim Reply As Long
    Dim nroA As Integer
    Dim Linea As String
    Dim html As String
    
    Reply = URLDownloadToFile(0, "https://www.bna.com.ar/Personas", "C:\1\html.xml", 0, 0)
    If Reply = 0 Then
        nroA = FreeFile
        Open "C:\1\html.xml" For Input As nroA
        html = ""
        Do While Not EOF(nroA)
            Line Input #nroA, Linea
            html = html & Trim(Linea)
        Loop
        Close nroA
        a = InStr(html, "<td class=" & Chr(34) & "tit" & Chr(34) & ">Dolar U.S.A</td>")
        b = InStr(a + 1, html, "<td>")
        c = InStr(b + 1, html, "<td>")
        d = InStr(c + 1, html, "</td>")
        e = Mid(html, c + 4, d - c - 4)
        CotizacionBNA = Replace(e, ",", ".")
    Else
        CotizacionBNA = 1
    End If
End Function
