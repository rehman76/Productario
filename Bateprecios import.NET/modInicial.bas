Attribute VB_Name = "modInicial"
'Api GetLongPathName para convertir el path
''''''''''''''''''''''''''''''''''''''''''''
Private Declare Function GetLongPathName Lib _
    "kernel32.dll" _
    Alias "GetLongPathNameA" ( _
    ByVal lpszShortPath As String, _
    ByVal lpszLongPath As String, _
    ByVal cchBuffer As Long) As Long

Public Function funConexion()
'    funConexion = "Provider=SQLNCLI11;Integrated Security=SSPI;Persist Security Info=False;User ID="""";Initial Catalog=Bateprecios;Data Source=localhost\sqlexpress;Initial File Name="""";Server SPN="""""
'    funConexion = "Driver={MySQL ODBC 3.51 Driver};Server=85.10.205.173;Database=bate_stock;User=fabianf;Password=demostenes;PORT=3306;"
    funConexion = "DSN=Bate"
End Function

Public Function RecordsetToCSV(rsData As ADODB.Recordset, Optional ShowColumnNames As Boolean = True, Optional NULLStr As String = "") As String
    Dim K As Long, RetStr As String
    
    If ShowColumnNames Then
        For K = 0 To rsData.Fields.Count - 1
            RetStr = RetStr & ",""" & rsData.Fields(K).Name & """"
        Next K
        
        RetStr = Mid(RetStr, 2) & vbNewLine
    End If
    
    RetStr = RetStr & """" & rsData.GetString(adClipString, -1, """,""", """" & vbNewLine & """", NULLStr)
    RetStr = Left(RetStr, Len(RetStr) - 3)
    
    RecordsetToCSV = RetStr
End Function

Function GetDirTemp() As String
    If Environ$("temp") <> vbNullString Then
       Dim Buffer As String
       Buffer = String(255, 0) ' buffer de caracteres para el retorno
       ' llamada a GetLongPathName para convertir
       Call GetLongPathName(Environ$("temp"), Buffer, 255)
       ' Retorno
       GetDirTemp = Replace(Buffer, Chr(0), vbNullString)
    End If
End Function

