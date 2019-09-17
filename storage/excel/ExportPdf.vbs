Dim target, result

target = WScript.Arguments(0)
result = WScript.Arguments(1)

Set excel = CreateObject("Excel.Application")
Set book = excel.Application.Workbooks.Open(target)
book.WorkSheets(1).ExportAsFixedFormat xlTypePDF, result, xlqualityminimum
book.Close
Set excel = Nothing

