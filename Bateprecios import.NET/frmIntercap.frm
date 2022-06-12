VERSION 5.00
Object = "{440E9D62-5EE7-4C7D-8414-0870054AC206}#1.0#0"; "GurhanButtonControl.ocx"
Begin VB.Form frmIntercap 
   BorderStyle     =   1  'Fixed Single
   Caption         =   "Interface Intercap - Bateprecios"
   ClientHeight    =   4425
   ClientLeft      =   45
   ClientTop       =   330
   ClientWidth     =   6630
   LinkTopic       =   "Form1"
   MaxButton       =   0   'False
   MinButton       =   0   'False
   ScaleHeight     =   4425
   ScaleWidth      =   6630
   StartUpPosition =   1  'CenterOwner
   Begin VB.Frame fraLog 
      Caption         =   "Log"
      ForeColor       =   &H000000FF&
      Height          =   3975
      Left            =   120
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
      Left            =   240
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
Attribute VB_Name = "frmIntercap"
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
    auxUrl = "http://www.intercap.com.ar/API/rs/bateprecios/catalogo"
    xmlhttp.Open "GET", auxUrl, False, "bateprecios", "XrB3hUHLn6R7mbC"
    xmlhttp.send

    Set cn = New ADODB.Connection
    Set rs = New ADODB.Recordset
    cn.Open funConexion()
    
    sql = "UPDATE Articulos SET Estado = '*' WHERE SKU LIKE 'INC-%'"
    Do
        cn.Execute sql
        DoEvents
    Loop While Err <> 0
    
    Set p = JSON.parse(xmlhttp.responseText)
    DoEvents
    For Y = 1 To p.Item("catalogoBatePrecios").Count()
        DoEvents
        SKU = "INC-" & Trim(p.Item("catalogoBatePrecios").Item(Y).Item("id"))
        EAN = ""
        Categoria = 0
        Titulo = ArreglarSeparadores(Trim(p.Item("catalogoBatePrecios").Item(Y).Item("titulo")))
        Descripcion = ArreglarSeparadores(Trim(p.Item("catalogoBatePrecios").Item(Y).Item("descripcion")))
        Estado = "A"
        Proveedor = 3
        ListaPrecios = 1
        FechaVigencia = Now
        Cotizacion = CotizacionBNA
        Valor = Trim(p.Item("catalogoBatePrecios").Item(Y).Item("precio"))
        IVA = Replace(Trim(p.Item("catalogoBatePrecios").Item(Y).Item("iva")), ",", ".")
        ImpuestosInternos = Trim(p.Item("catalogoBatePrecios").Item(Y).Item("otrosTributos"))
        Stock = Trim(p.Item("catalogoBatePrecios").Item(Y).Item("stock"))
        ok = insertArticulos(SKU, EAN, Categoria, Titulo, Descripcion, Estado, Proveedor, ListaPrecios, FechaVigencia, Cotizacion, Valor, IVA, ImpuestosInternos, Stock, mensaje)
        lstLog.AddItem Trim(mensaje) & "..."
        lstLog.ListIndex = lstLog.ListCount - 1
    Next
    Set p = Nothing
    End
End Sub

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
