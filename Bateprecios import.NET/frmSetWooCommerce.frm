VERSION 5.00
Object = "{48E59290-9880-11CF-9754-00AA00C00908}#1.0#0"; "MSINET.OCX"
Begin VB.Form frmSetWooCommerce 
   BorderStyle     =   1  'Fixed Single
   Caption         =   "Set WooCommerce"
   ClientHeight    =   765
   ClientLeft      =   45
   ClientTop       =   330
   ClientWidth     =   4845
   LinkTopic       =   "Form1"
   MaxButton       =   0   'False
   MinButton       =   0   'False
   ScaleHeight     =   765
   ScaleWidth      =   4845
   StartUpPosition =   3  'Windows Default
   Begin InetCtlsObjects.Inet Inet1 
      Left            =   240
      Top             =   2400
      _ExtentX        =   1005
      _ExtentY        =   1005
      _Version        =   393216
   End
End
Attribute VB_Name = "frmSetWooCommerce"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Dim cn As ADODB.Connection
Dim rs As ADODB.Recordset
Dim CSVData As String

Private Sub Form_Activate()
    GenerarArchivo ""
    GenerarArchivo "ELT"
    GenerarArchivo "GPN"
    GenerarArchivo "INC"
    GenerarArchivo "STY"
    End
End Sub

Sub GenerarArchivo(Proveedor)
    Set cn = New ADODB.Connection
    Set rs = New ADODB.Recordset
    cn.Open funConexion()
    sql = "SELECT A.SKU, A.EAN, A.Categoria, A.Titulo, REPLACE(REPLACE(A.Descripcion,CHAR(13),''),CHAR(10),'') AS Descripcion, A.Estado, REPLACE(AV.Cotizacion,',','.') AS Cotizacion, AV.FechaVigencia, REPLACE(AV.ImpuestosInternos,',','.') AS ImpuestosInternos, REPLACE(AV.IVA,',','.') AS IVA, AV.ListaPrecios, AV.Proveedor, REPLACE(AV.Valor,',','.') AS Valor, AV.SKU, IIF(A.Estado = '*', 0, AV.Stock) AS Stock FROM Articulos AS A INNER JOIN Articulos_Valor AS AV ON AV.SKU = A.SKU AND AV.FechaVigencia = (SELECT MAX(FechaVigencia) FROM Articulos_Valor WHERE SKU = AV.SKU) WHERE AV.ListaPrecios = 1 AND AV.SKU LIKE '" & Proveedor & "%'"
    rs.Open sql, cn, 3, 3
    If Proveedor = "" Then Proveedor = "General"
    archivo = GetDirTemp() & Proveedor & ".csv"
    If Dir(archivo) <> "" Then
        Kill archivo
    End If
    Open archivo For Output As #1
    Print #1, "SKU;EAN;Categoria;Titulo;Descripcion;Estado;Cotizacion;FechaVigencia;ImpuestosInternos;IVA;ListaPrecios;Proveedor;Valor;SKU;Stock"
    Do While Not rs.EOF
        For Y = 0 To 13
            campo = rs(Y)
            campo = Replace(campo, ";", "|")
            Print #1, campo & ";";
        Next
        Print #1, rs(14)
        rs.MoveNext
    Loop
    Close #1
    
    
'    CSVData = RecordsetToCSV(rs, True)
    rs.Close
    cn.Close
    Set rs = Nothing
    Set cn = Nothing
    
'    End
'
'    If Proveedor = "" Then Proveedor = "General"
'    archivo = GetDirTemp() & Proveedor & ".csv"
'    If Dir(archivo) <> "" Then
'        Kill archivo
'    End If
'
'    Open archivo For Binary Access Write As #1
'        Put #1, , CSVData
'    Close #1
    
    Inet1.AccessType = icUseDefault
    Inet1.URL = "ftp://ftp.bateprecios.com"
    Inet1.UserName = "bate-importer-vb@bateprecios.com"
'    Inet1.Password = ";R,(QDx8+i=slb]{[u"
    Inet1.Password = "xp2S?6-j]0?*$ROCuW"
    Inet1.RequestTimeout = 400
    Inet1.Execute , "CD vendors.bateprecios.com"
    Do While Inet1.StillExecuting
       DoEvents
    Loop
    Inet1.Execute , "CD vendors"
    Do While Inet1.StillExecuting
       DoEvents
    Loop
    Inet1.Execute , "CD FabianSoft"
    Do While Inet1.StillExecuting
       DoEvents
    Loop
    
    Inet1.Execute , "PUT " & archivo & " " & Proveedor & ".csv"
    Do While Inet1.StillExecuting
       DoEvents
    Loop
    
    Inet1.Execute , "CLOSE"
End Sub
