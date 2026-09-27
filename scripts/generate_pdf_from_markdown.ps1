param(
    [Parameter(Mandatory = $true)]
    [string]$MarkdownPath,

    [Parameter(Mandatory = $true)]
    [string]$DocxPath,

    [Parameter(Mandatory = $true)]
    [string]$PdfPath
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function ConvertTo-XmlEscapedText([string]$Text)
{
    if ($null -eq $Text) {
        return ''
    }

    return [System.Security.SecurityElement]::Escape($Text)
}

function New-ParagraphXml(
    [string]$Text,
    [string]$StyleId = 'Normal',
    [bool]$Bold = $false,
    [string]$FontName = $null,
    [int]$FontSizeHalfPoints = 0
)
{
    $escapedText = ConvertTo-XmlEscapedText $Text
    $paragraphProperties = @()

    if ($StyleId) {
        $paragraphProperties += "<w:pStyle w:val=`"$StyleId`"/>"
    }

    $runProperties = @()
    if ($Bold) {
        $runProperties += '<w:b/>'
    }

    if ($FontName) {
        $escapedFont = ConvertTo-XmlEscapedText $FontName
        $runProperties += "<w:rFonts w:ascii=`"$escapedFont`" w:hAnsi=`"$escapedFont`" w:cs=`"$escapedFont`"/>"
    }

    if ($FontSizeHalfPoints -gt 0) {
        $runProperties += "<w:sz w:val=`"$FontSizeHalfPoints`"/>"
        $runProperties += "<w:szCs w:val=`"$FontSizeHalfPoints`"/>"
    }

    $paragraphPropertiesXml = if ($paragraphProperties.Count -gt 0) {
        "<w:pPr>$($paragraphProperties -join '')</w:pPr>"
    }
    else {
        ''
    }

    $runPropertiesXml = if ($runProperties.Count -gt 0) {
        "<w:rPr>$($runProperties -join '')</w:rPr>"
    }
    else {
        ''
    }

    return "<w:p>$paragraphPropertiesXml<w:r>$runPropertiesXml<w:t xml:space=`"preserve`">$escapedText</w:t></w:r></w:p>"
}

function New-TableXml([object[]]$Rows)
{
    if ($Rows.Count -eq 0) {
        return ''
    }

    $columnCount = 0
    foreach ($row in $Rows) {
        if ($row.Count -gt $columnCount) {
            $columnCount = $row.Count
        }
    }

    $gridColumns = for ($columnIndex = 0; $columnIndex -lt $columnCount; $columnIndex++) {
        '<w:gridCol w:w="2400"/>'
    }

    $tableRows = foreach ($rowIndex in 0..($Rows.Count - 1)) {
        $cells = for ($columnIndex = 0; $columnIndex -lt $columnCount; $columnIndex++) {
            $cellValue = if ($columnIndex -lt $Rows[$rowIndex].Count) {
                [string]$Rows[$rowIndex][$columnIndex]
            }
            else {
                ''
            }

            $paragraphXml = New-ParagraphXml -Text $cellValue -StyleId 'Normal' -Bold:($rowIndex -eq 0)
            "<w:tc><w:tcPr><w:tcW w:w=`"2400`" w:type=`"dxa`"/></w:tcPr>$paragraphXml</w:tc>"
        }

        "<w:tr>$($cells -join '')</w:tr>"
    }

    return @"
<w:tbl>
  <w:tblPr>
    <w:tblStyle w:val="TableGrid"/>
    <w:tblW w:w="0" w:type="auto"/>
    <w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1"/>
  </w:tblPr>
  <w:tblGrid>$($gridColumns -join '')</w:tblGrid>
  $($tableRows -join '')
</w:tbl>
"@
}

function Test-TableLine([string]$Line)
{
    $trimmed = $Line.Trim()
    return $trimmed.StartsWith('|') -and $trimmed.EndsWith('|')
}

function Convert-MarkdownToWordXml([string]$MarkdownContent, [string]$SectionPropertiesXml)
{
    $content = [regex]::Replace($MarkdownContent, '^(?s)---\r?\n.*?\r?\n---\r?\n', '')
    $lines = $content -split "`r?`n"
    $bodyNodes = New-Object System.Collections.Generic.List[string]
    $firstHeadingWritten = $false
    $lineIndex = 0

    while ($lineIndex -lt $lines.Count) {
        $line = $lines[$lineIndex]
        $trimmed = $line.Trim()

        if ([string]::IsNullOrWhiteSpace($trimmed)) {
            $lineIndex++
            continue
        }

        if ($trimmed -match '^```') {
            $lineIndex++
            while ($lineIndex -lt $lines.Count -and $lines[$lineIndex].Trim() -notmatch '^```') {
                [void]$bodyNodes.Add((New-ParagraphXml -Text $lines[$lineIndex] -StyleId 'Normal' -FontName 'Consolas' -FontSizeHalfPoints 18))
                $lineIndex++
            }

            if ($lineIndex -lt $lines.Count) {
                $lineIndex++
            }

            continue
        }

        if ($trimmed -match '^(#{1,4})\s+(.*)$') {
            $level = $Matches[1].Length
            $text = $Matches[2].Trim()
            $style = switch ($level) {
                1 {
                    if (-not $firstHeadingWritten) {
                        'Title'
                    }
                    else {
                        'Heading1'
                    }
                }
                2 { 'Heading2' }
                3 { 'Heading3' }
                default { 'Heading4' }
            }

            [void]$bodyNodes.Add((New-ParagraphXml -Text $text -StyleId $style))

            if (-not $firstHeadingWritten -and $level -eq 1) {
                $firstHeadingWritten = $true
            }

            $lineIndex++
            continue
        }

        if (Test-TableLine $line) {
            $tableLines = New-Object System.Collections.Generic.List[string]
            while ($lineIndex -lt $lines.Count -and (Test-TableLine $lines[$lineIndex])) {
                [void]$tableLines.Add($lines[$lineIndex].Trim())
                $lineIndex++
            }

            $parsedRows = New-Object System.Collections.Generic.List[object]
            for ($tableIndex = 0; $tableIndex -lt $tableLines.Count; $tableIndex++) {
                if ($tableIndex -eq 1 -and $tableLines[$tableIndex] -match '^\|(?:\s*:?-+:?\s*\|)+$') {
                    continue
                }

                $cells = $tableLines[$tableIndex].Trim('|').Split('|') | ForEach-Object { $_.Trim() }
                [void]$parsedRows.Add(@($cells))
            }

            [void]$bodyNodes.Add((New-TableXml -Rows $parsedRows.ToArray()))
            continue
        }

        if ($trimmed -match '^\-\s+(.*)$') {
            [void]$bodyNodes.Add((New-ParagraphXml -Text $Matches[1].Trim() -StyleId 'ListBullet'))
            $lineIndex++
            continue
        }

        if ($trimmed -match '^\d+\.\s+(.*)$') {
            [void]$bodyNodes.Add((New-ParagraphXml -Text $Matches[1].Trim() -StyleId 'ListNumber'))
            $lineIndex++
            continue
        }

        $paragraphLines = New-Object System.Collections.Generic.List[string]
        while ($lineIndex -lt $lines.Count) {
            $candidate = $lines[$lineIndex]
            $candidateTrimmed = $candidate.Trim()

            if ([string]::IsNullOrWhiteSpace($candidateTrimmed)) {
                break
            }

            if ($candidateTrimmed -match '^```') {
                break
            }

            if ($candidateTrimmed -match '^(#{1,4})\s+') {
                break
            }

            if ($candidateTrimmed -match '^\-\s+') {
                break
            }

            if ($candidateTrimmed -match '^\d+\.\s+') {
                break
            }

            if (Test-TableLine $candidate) {
                break
            }

            [void]$paragraphLines.Add($candidateTrimmed)
            $lineIndex++
        }

        if ($paragraphLines.Count -gt 0) {
            [void]$bodyNodes.Add((New-ParagraphXml -Text ($paragraphLines.ToArray() -join ' ') -StyleId 'Normal'))
        }
        else {
            $lineIndex++
        }
    }

    $bodyNodes.Add($SectionPropertiesXml) | Out-Null

    return @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml" xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup" xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk" xmlns:wne="http://schemas.microsoft.com/office/2006/wordml" xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape" mc:Ignorable="w14 wp14">
  <w:body>
    $($bodyNodes -join "`n    ")
  </w:body>
</w:document>
"@
}

$markdownAbsolutePath = (Resolve-Path $MarkdownPath).Path
$docxAbsolutePath = (Resolve-Path $DocxPath).Path
$pdfAbsolutePath = $ExecutionContext.SessionState.Path.GetUnresolvedProviderPathFromPSPath($PdfPath)
$workingRoot = Join-Path ([System.IO.Path]::GetTempPath()) ([System.Guid]::NewGuid().ToString())
$extractPath = Join-Path $workingRoot 'docx'
$zipPath = Join-Path $workingRoot 'updated.zip'
$tempDocxPath = Join-Path $workingRoot 'output.docx'

try {
    New-Item -ItemType Directory -Path $extractPath -Force | Out-Null
    Copy-Item $docxAbsolutePath $zipPath
    Expand-Archive -Path $zipPath -DestinationPath $extractPath -Force

    $existingDocumentXmlPath = Join-Path $extractPath 'word\document.xml'
    $existingDocumentXml = Get-Content -Path $existingDocumentXmlPath -Raw -Encoding UTF8
    $sectionPropertiesMatch = [regex]::Match($existingDocumentXml, '(?s)<w:sectPr\b.*?</w:sectPr>')
    $sectionPropertiesXml = if ($sectionPropertiesMatch.Success) {
        $sectionPropertiesMatch.Value
    }
    else {
        '<w:sectPr><w:pgSz w:w="12240" w:h="15840"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="708" w:footer="708" w:gutter="0"/></w:sectPr>'
    }

    $markdownContent = Get-Content -Path $markdownAbsolutePath -Raw -Encoding UTF8
    $newDocumentXml = Convert-MarkdownToWordXml -MarkdownContent $markdownContent -SectionPropertiesXml $sectionPropertiesXml
    Set-Content -Path $existingDocumentXmlPath -Value $newDocumentXml -Encoding UTF8

    $coreXmlPath = Join-Path $extractPath 'docProps\core.xml'
    if (Test-Path $coreXmlPath) {
        $timestamp = [DateTime]::UtcNow.ToString('s') + 'Z'
        $coreXml = Get-Content -Path $coreXmlPath -Raw -Encoding UTF8
        $coreXml = [regex]::Replace($coreXml, '<dcterms:modified[^>]*>.*?</dcterms:modified>', "<dcterms:modified xsi:type=`"dcterms:W3CDTF`">$timestamp</dcterms:modified>")
        $coreXml = [regex]::Replace($coreXml, '<dcterms:created[^>]*>.*?</dcterms:created>', "<dcterms:created xsi:type=`"dcterms:W3CDTF`">$timestamp</dcterms:created>")
        Set-Content -Path $coreXmlPath -Value $coreXml -Encoding UTF8
    }

    if (Test-Path $zipPath) {
        Remove-Item $zipPath -Force
    }

    Compress-Archive -Path (Join-Path $extractPath '*') -DestinationPath $zipPath -Force
    Copy-Item $zipPath $tempDocxPath -Force

    $word = New-Object -ComObject Word.Application
    $word.Visible = $false
    try {
        $doc = $word.Documents.Open($tempDocxPath)
        try {
            $doc.SaveAs([ref]$pdfAbsolutePath, [ref]17)  # 17 = wdFormatPDF
        }
        finally {
            $doc.Close($false)
        }
    }
    finally {
        $word.Quit()
        [System.Runtime.InteropServices.Marshal]::ReleaseComObject($word) | Out-Null
        [System.GC]::Collect()
        [System.GC]::WaitForPendingFinalizers()
    }
}
finally {
    if (Test-Path $workingRoot) {
        Remove-Item $workingRoot -Recurse -Force
    }
}
