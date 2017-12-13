<!DOCTYPE html>
<html>
<head>
    <title>Upload File</title>
</head>
<body>
<form method="post" action="{{ route('upload_file') }}" enctype="multipart/form-data">
    <input name="_token" type="hidden" value="{{ csrf_token() }}">

    <input type="text" name="name">
    <input type="text" name="author">
    <input type="text" name="likes">
    <input type="text" name="percent">
    <input type="text" name="folder_id">
    <input type="file" multiple name="file[]">
    <button type="submit">Загрузить</button>
</form>
</body>
</html>