VERSION 5.00
Object = "{48E59290-9880-11CF-9754-00AA00C00908}#1.0#0"; "MSINET.OCX"
Object = "{440E9D62-5EE7-4C7D-8414-0870054AC206}#1.0#0"; "GurhanButtonControl.ocx"
Begin VB.Form frmElit 
   BorderStyle     =   1  'Fixed Single
   Caption         =   "Interface Elit - Bateprecios"
   ClientHeight    =   4440
   ClientLeft      =   45
   ClientTop       =   330
   ClientWidth     =   6495
   LinkTopic       =   "Form1"
   MaxButton       =   0   'False
   MinButton       =   0   'False
   ScaleHeight     =   4440
   ScaleWidth      =   6495
   StartUpPosition =   1  'CenterOwner
   Begin InetCtlsObjects.Inet Inet1 
      Left            =   480
      Top             =   4920
      _ExtentX        =   1005
      _ExtentY        =   1005
      _Version        =   393216
   End
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
Attribute VB_Name = "frmElit"
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
    Dim archivo As String
    Dim url As String
    Dim r As Integer
    Dim SKU As String
    Dim xx As String
    Dim fl As Long
    Dim Registro, Campo

    archivo = GetDirTemp() & "\catalogo.csv"
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
    Inet1.Execute , "CD elit"
    Do While Inet1.StillExecuting
       DoEvents
    Loop
    Inet1.Execute , "GET catalogo.csv " & archivo
    Do While Inet1.StillExecuting
       DoEvents
    Loop
    Inet1.Execute , "CLOSE"
    
    fl = FileLen(archivo)
    
    
    Set cn = New ADODB.Connection
    Set rs = New ADODB.Recordset
    cn.Open funConexion()
    
    sql = "UPDATE Articulos SET Estado = '*' WHERE SKU LIKE 'ELT%'"
    cn.Execute sql
    
    cn.Close
    Set rs = Nothing
    Set cn = Nothing
    
    c = 0
    Open archivo For Input As #1
    Do While Not EOF(1)
        c = c + 1
        Line Input #1, Registro
        If c > 1 Then
            Campo = Split(Registro, ";")
            DoEvents
            SKU = "ELT-" & Trim(Campo(0))
            EAN = Trim(Campo(12))
            Categoria = 0
            Titulo = Trim(Campo(2))
            Descripcion = Trim(Campo(14))
            Estado = "A"
            Proveedor = 1
            ListaPrecios = 1
            FechaVigencia = Now
            Cotizacion = Replace(Campo(10), ",", ".")
            Valor = Replace(Campo(6), ",", ".")
            IVA = Replace(Campo(8), ",", ".")
            ImpuestosInternos = Replace(Campo(7), ",", ".")
            Stock = Replace(Campo(11), ",", ".")
            ok = insertArticulos(SKU, EAN, Categoria, Titulo, Descripcion, Estado, Proveedor, ListaPrecios, FechaVigencia, Cotizacion, Valor, IVA, ImpuestosInternos, Stock, mensaje)
            lstLog.AddItem Trim(mensaje) & "..."
            lstLog.ListIndex = lstLog.ListCount - 1
        End If
    Loop
    Close #1
    
    End
   
End Sub

