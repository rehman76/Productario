VERSION 5.00
Object = "{48E59290-9880-11CF-9754-00AA00C00908}#1.0#0"; "MSINET.OCX"
Object = "{440E9D62-5EE7-4C7D-8414-0870054AC206}#1.0#0"; "GurhanButtonControl.ocx"
Begin VB.Form frmGrupoNucleo 
   BorderStyle     =   1  'Fixed Single
   Caption         =   "Interface Grupo Nucleo - Bateprecios"
   ClientHeight    =   4590
   ClientLeft      =   45
   ClientTop       =   330
   ClientWidth     =   6630
   LinkTopic       =   "Form1"
   MaxButton       =   0   'False
   MinButton       =   0   'False
   ScaleHeight     =   4590
   ScaleWidth      =   6630
   StartUpPosition =   1  'CenterOwner
   Begin InetCtlsObjects.Inet Inet1 
      Left            =   360
      Top             =   5040
      _ExtentX        =   1005
      _ExtentY        =   1005
      _Version        =   393216
   End
   Begin VB.Frame fraLog 
      Caption         =   "Log"
      ForeColor       =   &H000000FF&
      Height          =   3975
      Left            =   120
      TabIndex        =   0
      Top             =   120
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
      Top             =   4200
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
Attribute VB_Name = "frmGrupoNucleo"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Dim cn As ADODB.Connection
Dim rs As ADODB.Recordset

Private Sub butSalir_Click()
    Unload Me
End Sub

Private Sub Form_Activate()
    Dim archivo As String
    Dim url As String
    Dim r As Integer
    Dim SKU As String
    Dim xx As String
    Dim fl As Long

    archivo = GetDirTemp() & "\gn.json"
    If Dir(archivo) <> "" Then
        Kill archivo
    End If
    
    lstLog.Clear
    
    Inet1.AccessType = icUseDefault
    Inet1.url = "ftp://ftp.bateprecios.com"
    Inet1.UserName = "bate-importer-vb@bateprecios.com"
    Inet1.Password = "xp2S?6-j]0?*$ROCuW"
    Inet1.RequestTimeout = 400
    Inet1.Execute , "CD proveedores"
    Do While Inet1.StillExecuting
       DoEvents
    Loop
    Inet1.Execute , "CD gruponucleo"
    Do While Inet1.StillExecuting
       DoEvents
    Loop
    Inet1.Execute , "GET precios_stock_GN.JSON " & archivo
    Do While Inet1.StillExecuting
       DoEvents
    Loop
    Inet1.Execute , "CLOSE"
    
    fl = FileLen(archivo)
    
    Open archivo For Input As #1
    xx = input$(fl, 1)
    Close #1
    
    Set cn = New ADODB.Connection
    Set rs = New ADODB.Recordset
    cn.open funConexion()
    
    sql = "UPDATE Articulos SET Estado = '*' WHERE SKU LIKE 'GPN%'"
    cn.Execute sql
    
    cn.Close
    Set rs = Nothing
    Set cn = Nothing
    
    Set p = JSON.parse(xx)
    For Y = 1 To p.Count()
        DoEvents
        SKU = "GPN-" & Trim(p.Item(Y).Item("codigo"))
        EAN = ""
        Categoria = 0
        Titulo = ArreglarSeparadores(Trim(p.Item(Y).Item("titulo")))
        Descripcion = Trim(ArreglarSeparadores(Trim(p.Item(Y).Item("desc_1")))) & " " & Trim(ArreglarSeparadores(Trim(p.Item(Y).Item("desc_2"))))
        Estado = "A"
        Proveedor = 2
        ListaPrecios = 1
        FechaVigencia = Now
        Cotizacion = 1
        Valor = Replace(Trim(p.Item(Y).Item("neto_pesos")), ",", ".")
        IVA = Replace(Trim(p.Item(Y).Item("porcentaje_imp")), ",", ".")
        ImpuestosInternos = 0
        Stock = Trim(p.Item(Y).Item("stock_caba"))
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
        Select Case Trim(auxLetra)
        Case Chr(34) 'Comillas
            If auxComillas = 0 Then
                auxComillas = 1
            Else
                auxComillas = 0
            End If
        Case ";"
            auxLetra = ";"
            If auxComillas = 1 Then
'                auxLetra = "."
            End If
        Case "'"
            auxLetra = "|"
            If auxComillas = 1 Then
'                auxLetra = "|"
            End If
        Case ","
            auxLetra = ";"
            If auxComillas = 0 Then
'                auxLetra = ";"
            End If
        Case ""
            auxLetra = Chr(254)
        End Select
        auxTexto = auxTexto & Trim(auxLetra)
    Next
    auxTexto = Replace(auxTexto, Chr(254), " ")
    ArreglarSeparadores = auxTexto
End Function

