VERSION 5.00
Object = "{440E9D62-5EE7-4C7D-8414-0870054AC206}#1.0#0"; "GurhanButtonControl.ocx"
Begin VB.Form frmElit_API 
   BorderStyle     =   1  'Fixed Single
   Caption         =   "Interface Elit API - Bateprecios"
   ClientHeight    =   4500
   ClientLeft      =   45
   ClientTop       =   330
   ClientWidth     =   6495
   LinkTopic       =   "Form1"
   MaxButton       =   0   'False
   MinButton       =   0   'False
   ScaleHeight     =   4500
   ScaleWidth      =   6495
   StartUpPosition =   1  'CenterOwner
   Begin VB.Frame fraLog 
      Caption         =   "Log"
      ForeColor       =   &H000000FF&
      Height          =   3975
      Left            =   0
      TabIndex        =   0
      Top             =   0
      Width           =   6375
      Begin VB.ListBox lstLog 
         Height          =   3570
         Left            =   120
         TabIndex        =   1
         Top             =   240
         Width           =   6135
      End
   End
   Begin GurhanButtonControl.GurhanButton butSalir 
      Height          =   255
      Left            =   120
      TabIndex        =   2
      Top             =   4080
      Width           =   6135
      _ExtentX        =   10821
      _ExtentY        =   450
      Caption         =   "Salir"
      PictureWidth    =   16
      PictureHeight   =   16
      PictureSize     =   0
      Enabled         =   -1  'True
      BeginProperty Font {0BE35203-8F91-11CE-9DE3-00AA004BB851} 
         Name            =   "MS Sans Serif"
         Size            =   8.25
         Charset         =   0
         Weight          =   400
         Underline       =   0   'False
         Italic          =   0   'False
         Strikethrough   =   0   'False
      EndProperty
   End
End
Attribute VB_Name = "frmElit_API"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Dim cn As ADODB.Connection
Dim rs As ADODB.Recordset
'Option Explicit

Private Sub butSalir_Click()
    End
End Sub

Private Sub Form_Activate()
    Dim xmlhttp As MSXML2.xmlhttp
    Dim result0 As String
    Dim auxUrl As String
    Dim auxBody As String
    Dim auxOffset As Integer
    Dim auxTotal As Integer
    Dim auxLimit As Integer
    Dim r As Integer
    Dim SKU As String
    Dim p As Object
    
    inicio = Now
    
    Set xmlhttp = CreateObject("MSXML2.ServerXMLHTTP")
    
    lstLog.Clear
    auxUrl = "http://api.elit.com.ar/productos"
    auxBody = "{""user_id"": ""18377"",""token"": ""d3dxcjkwOTg=""}"
    xmlhttp.Open "POST", auxUrl, False
    xmlhttp.send auxBody
    Set p = JSON.parse(xmlhttp.responseText)
    auxTotal = p.Item("paginador").Item("total")
    auxLimit = p.Item("paginador").Item("limit")
    Set p = Nothing
    
    Set cn = New ADODB.Connection
    Set rs = New ADODB.Recordset
    cn.Open funConexion()
    
    sql = "UPDATE Articulos SET Estado = '*' WHERE SKU LIKE 'ELT%'"
    Do
        cn.Execute sql
        DoEvents
    Loop While Err <> 0
    
    For x = 0 To auxTotal Step auxLimit
        DoEvents
        auxUrl = "http://api.elit.com.ar/productos?offset=" & Trim(str(x))
        xmlhttp.Open "POST", auxUrl, False
        xmlhttp.send auxBody
        If Trim(xmlhttp.responseText) <> "Too Many Attempts." Then
            Set p = JSON.parse(xmlhttp.responseText)
            DoEvents
            For Y = 1 To p.Item("resultado").Count()
                DoEvents
                SKU = "ELT-" & Trim(p.Item("resultado").Item(Y).Item("cod_alfa"))
                EAN = Trim(p.Item("resultado").Item(Y).Item("ean"))
                Categoria = Trim(p.Item("resultado").Item(Y).Item("rubro"))
                Categoria = 0
                Titulo = ArreglarSeparadores(Trim(p.Item("resultado").Item(Y).Item("detalle")))
                Descripcion = ArreglarSeparadores(Trim(p.Item("resultado").Item(Y).Item("detalle")))
                Estado = "A"
                Proveedor = 1
                ListaPrecios = 1
                FechaVigencia = Now
                Cotizacion = Replace(Trim(p.Item("resultado").Item(Y).Item("cotizacion")), ",", ".")
                Valor = Trim(p.Item("resultado").Item(Y).Item("precio"))
                IVA = Replace(Trim(p.Item("resultado").Item(Y).Item("iva")), ",", ".")
                ImpuestosInternos = Trim(p.Item("resultado").Item(Y).Item("i_internos"))
                Stock = Trim(p.Item("resultado").Item(Y).Item("stock"))
                ok = insertArticulos(SKU, EAN, Categoria, Titulo, Descripcion, Estado, Proveedor, ListaPrecios, FechaVigencia, Cotizacion, Valor, IVA, ImpuestosInternos, Stock, mensaje)
                lstLog.AddItem Trim(mensaje) & "..."
                lstLog.ListIndex = lstLog.ListCount - 1
            Next
            Set p = Nothing
        Else
            Debug.Print x
        End If
    Next
    final = Now
    End
End Sub

Public Function WebRequest(url As String) As String
    Dim usr As String
    Dim pass As String
    
    Dim http As MSXML2.xmlhttp
    Set http = CreateObject("MSXML2.ServerXMLHTTP")

    usr = "fabian"
    pass = "^kAiw#[nrjkEi@j]xv"

    http.Open "GET", url, False, usr, pass
    http.send

    WebRequest = http.responseText
    Set http = Nothing
End Function

Public Function ArreglarSeparadores(ByRef texto)
    Dim auxContador As Integer
    Dim auxTexto As String
    Dim auxLetra As String
    Dim auxComillas As Integer
    auxTexto = ""
    auxComillas = 0
    For auxContador = 1 To Len(Trim(texto))
        auxLetra = Mid(Trim(texto), auxContador, 1)
        Select Case auxLetra
        Case "'"
            auxLetra = Chr(254)
        Case " "
            auxLetra = Chr(254)
        End Select
        auxTexto = auxTexto & Trim(auxLetra)
    Next
    auxTexto = Replace(auxTexto, Chr(254), " ")
    ArreglarSeparadores = auxTexto
End Function



Private Sub Command1_Click()
    Dim xmlhttp As MSXML2.xmlhttp
    Set xmlhttp = CreateObject("MSXML2.ServerXMLHTTP")
   
    auxUrl = "http://api.elit.com.ar/productos?offset=100"
    auxusuario = "18377"
    auxpass = "d3dxcjkwOTg="
    auxBody = "{""user_id"": ""18377"",""token"": ""d3dxcjkwOTg=""}"

   
'    xmlhttp.setRequestHeader "user_id", "18377"
'    xmlhttp.setRequestHeader "token", auxPass


    xmlhttp.Open "POST", auxUrl, False
'    xmlhttp.setRequestHeader "user_id", "18377"
'    xmlhttp.setRequestHeader "token", "d3dxcjkwOTg="
    xmlhttp.send auxBody
'    xmlhttp.Open "GET", auxURL, False
'    xmlhttp.Send
    Open "c:\1\abc.txt" For Output As #1
    Print #1, xmlhttp.responseText
    Close #1
    Set p = JSON.parse(xmlhttp.responseText)
    MsgBox p.Item("codigo")
    MsgBox p.Item("resultado").Count
    MsgBox p.Item("resultado").Item(1).Item("cod_alfa")
    End
End Sub

Private Sub Command2_Click()
Dim objRequest As Object
    Dim strUrl As String
    Dim strResponse As String
    Dim body As String
    Dim strResponseHeaders As String
    Dim allResponseHeader As String

    Set objRequest = CreateObject("WinHttp.WinHttpRequest.5.1")
    strUrl = "http://api.elit.com.ar/productos?offset=1"
    body = "{""user_id"": ""18377"",""token"": ""d3dxcjkwOTg=""}"
    'with basic'
    With objRequest
        .Open "GET", strUrl, False, "18377", "d3dxcjkwOTg="
        .setRequestHeader "Content-Type", "application/json"
        .send body
        strResponseHeaders = .statusText
        strResponse = .responseText
        allResponseHeader = .getAllResponseHeaders
    End With
    Debug.Print body
    Debug.Print allResponseHeader
    Debug.Print strResponse
End Sub

