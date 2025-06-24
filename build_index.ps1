@"
<html>
<body>
"@ > index.html

Get-ChildItem -Filter *.html | ForEach-Object {
    $fileName = $_.Name
    $displayName = $fileName -replace '^http___www.prirodneriesenia.sk_', ''
    "<a href='$fileName'>$displayName</a><br>" >> index.html
}

@"
</body>
</html>
"@ >> index.html
