VERSION 5.00
Object = "{48E59290-9880-11CF-9754-00AA00C00908}#1.0#0"; "MSINET.OCX"
Object = "{440E9D62-5EE7-4C7D-8414-0870054AC206}#1.0#0"; "GurhanButtonControl.ocx"
Begin VB.Form frmStylus 
   BorderStyle     =   1  'Fixed Single
   Caption         =   "Interface Stylus - Bateprecios"
   ClientHeight    =   4440
   ClientLeft      =   45
   ClientTop       =   330
   ClientWidth     =   6630
   LinkTopic       =   "Form1"
   MaxButton       =   0   'False
   MinButton       =   0   'False
   ScaleHeight     =   4440
   ScaleWidth      =   6630
   StartUpPosition =   1  'CenterOwner
   Begin InetCtlsObjects.Inet Inet1 
      Left            =   240
      Top             =   4800
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
Attribute VB_Name = "frmStylus"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Dim cn As ADODB.Connection
Dim cn2 As ADODB.Connection
Dim rs As ADODB.Recordset
Dim rs2 As ADODB.Recordset

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
    Dim Linea() As String
    Dim dato() As String
    Dim Valor As String
    Dim bytes As String
    Dim bytes2 As String
    Dim Letra As String

    archivo = "c:\1\stylus.txt"
    If Dir(archivo) <> "" Then
        Kill archivo
    End If
    archivo = "c:\1\stylus1.txt"
    If Dir(archivo) <> "" Then
        Kill archivo
    End If
    archivo = "c:\1\stylus.txt"
    
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
    Inet1.Execute , "CD stylus"
    Do While Inet1.StillExecuting
       DoEvents
    Loop
    Inet1.Execute , "GET ARTWEB1.TXT " & archivo
    Do While Inet1.StillExecuting
       DoEvents
    Loop
    Inet1.Execute , "CLOSE"
    
    Open "c:\1\stylus.txt" For Binary As #1
    Open "c:\1\stylus1.txt" For Binary As #2
    
    bytes = Space(LOF(1))
    Get #1, , bytes
    bytes2 = ""
    auxComillas = 0
    For x = 1 To Len(bytes)
        Letra = Mid(bytes, x, 1)
        If Letra = Chr(10) Then
            bytes2 = bytes2 & Chr(13) & Chr(10)
        Else
            bytes2 = bytes2 & Letra
        End If
    Next
    Put #2, , bytes2
    Close #2
    Close #1
    
    Set cn = New ADODB.Connection
    Set rs = New ADODB.Recordset
    cn.Open funConexion()
    
    sql = "UPDATE Articulos SET Estado = '*' WHERE SKU LIKE 'STY%'"
    cn.Execute sql
    
    cn.Close
    Set rs = Nothing
    Set cn = Nothing
    
    Open "c:\1\stylus1.txt" For Input As #1
    Cotizacion = CotizacionBNA
    Do While Not EOF(1)
        f = f + 1
        Line Input #1, datos
        datos = ArreglarSeparadores(datos)
        dato = Split(datos, ";")
        If f > 1 Then
            DoEvents
            contador = contador + 1
            SKU = "STY-" & Trim(dato(0))
            EAN = ""
            Categoria = 0
            Titulo = ArreglarSeparadores(Trim(dato(5)))
            Descripcion = Trim(ArreglarSeparadores(dato(6)))
            Estado = "A"
            Proveedor = 4
            ListaPrecios = 1
            FechaVigencia = Now
            Valor = dato(9)
            If IsNull(Valor) Then
                Valor = "0"
            End If
            If Valor = "" Then
                Valor = "0"
            End If
            If Left(Valor, 1) = "." Then Valor = "0" & Trim(Valor)
            Valor = Replace(Valor, ",", ".")
            Select Case dato(15)
            Case "M"
                IVA = "10.5"
            Case "G"
                IVA = "21"
            Case Else
                IVA = "0"
            End Select
            Select Case dato(18)
            Case "N"
                ImpuestosInternos = "0"
            Case Else
                ImpuestosInternos = "1"
            End Select
            Stock = dato(13)
            If IsNull(Stock) Then
                Stock = "0"
            End If
            
            ok = insertArticulos(SKU, EAN, Categoria, Titulo, Descripcion, Estado, Proveedor, ListaPrecios, FechaVigencia, Cotizacion, Valor, IVA, ImpuestosInternos, Stock, mensaje)
            lstLog.AddItem Trim(mensaje) & "..."
            
            lstLog.ListIndex = lstLog.ListCount - 1
        End If
    Loop
    Close #1
    
    End
    
End Sub

Public Function ArreglarSeparadores(ByRef texto)
    Dim auxContador As Integer
    Dim auxTexto As String
    Dim auxLetra As String
    Dim auxLetraAnterior As String
    Dim auxLetraSiguiente As String
    Dim auxComillas As Integer
    auxTexto = ""
    auxComillas = 0
    If IsNull(texto) Then
        texto = " "
    End If
    For auxContador = 1 To Len(Trim(texto))
        auxLetra = Mid(Trim(texto), auxContador, 1)
        If auxContador = 1 Then
            auxLetraAnterior = ";"
        Else
            auxLetraAnterior = Mid(Trim(texto), auxContador - 1, 1)
        End If
        auxLetraSiguiente = Mid(Trim(texto), auxContador + 1, 1)
        Select Case Trim(auxLetra)
        Case Chr(34) 'Comillas
            If auxComillas = 0 Then
                If auxLetraAnterior = ";" Then
                    auxComillas = 1
                End If
            Else
                If auxLetraSiguiente = ";" Then
                    auxComillas = 0
                End If
            End If
        Case ";"
            If auxComillas = 1 Then
                auxLetra = "|"
            End If
        Case "'"
            If auxComillas = 1 Then
                auxLetra = "|"
            End If
        Case ","
            If auxComillas = 0 Then
                auxLetra = "|"
            End If
        Case ""
            auxLetra = Chr(254)
        End Select
        auxTexto = auxTexto & Trim(auxLetra)
    Next
    auxTexto = Replace(auxTexto, Chr(254), " ")
    ArreglarSeparadores = auxTexto
End Function


